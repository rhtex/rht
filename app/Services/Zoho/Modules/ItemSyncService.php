<?php

namespace App\Services\Zoho\Modules;

use App\Models\ProductCategoryModel;
use App\Models\ProductModel;
use App\Models\TaxModel;
use App\Services\Zoho\ZohoHashService;
use App\Services\Zoho\ZohoSyncContext;

class ItemSyncService extends AbstractModuleSyncService
{
    protected ProductModel $productModel;
    protected TaxModel $taxModel;

    public function __construct($client = null)
    {
        parent::__construct($client);
        $this->productModel = new ProductModel();
        $this->taxModel     = new TaxModel();
    }

    public function getModuleName(): string
    {
        return 'items';
    }

    public function pushToZoho(int $localId, string $operation = 'UPDATE'): array
    {
        $mapping = $this->mappingModel->findByLocal('items', $localId);
        $product = $this->productModel->find($localId);

        if ($operation === 'DELETE' || $operation === 'MARK_INACTIVE' || !$product) {
            if (!$mapping || empty($mapping['zoho_record_id'])) {
                return ['success' => true, 'message' => 'Item was not synced to Zoho; nothing to delete.'];
            }
            $zohoId = $mapping['zoho_record_id'];
            if ($operation === 'DELETE') {
                $delRes = $this->client->delete("/items/{$zohoId}", [], [
                    'module'          => 'items',
                    'local_record_id' => $localId,
                    'zoho_record_id'  => $zohoId,
                    'operation'       => 'DELETE',
                ]);
                if (!empty($delRes['success'])) {
                    $this->mappingModel->markStatus('items', $localId, 'DELETED_IN_ZOHO');
                    return ['success' => true, 'message' => 'Item deleted in Zoho Books.'];
                }
            }

            $inactRes = $this->client->post("/items/{$zohoId}/inactive", [], [], [
                'module'          => 'items',
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
                'operation'       => 'MARK_INACTIVE',
            ]);
            if (!empty($inactRes['success'])) {
                $this->mappingModel->markStatus('items', $localId, 'ARCHIVED');
                return ['success' => true, 'message' => 'Item marked inactive in Zoho Books.'];
            }
            $this->mappingModel->markStatus('items', $localId, 'FAILED', $inactRes['message'] ?? 'Delete/Inactive failed');
            return $inactRes;
        }

        $taxPercentage = 0.0;
        if (!empty($product['tax_id'])) {
            $taxRow = $this->taxModel->find((int) $product['tax_id']);
            $taxPercentage = (float) ($taxRow['percentage'] ?? 0);
        }

        $normalizedLocal = $this->normalizeLocalProduct($product, $taxPercentage);

        if ($mapping && !empty($mapping['zoho_record_id']) && !empty($mapping['local_version_hash'])) {
            if (ZohoHashService::hash($normalizedLocal) === $mapping['local_version_hash'] && $mapping['sync_status'] === 'SYNCED') {
                return [
                    'success'        => true,
                    'skipped'        => true,
                    'zoho_record_id' => $mapping['zoho_record_id'],
                    'message'        => 'Product unchanged since last sync.',
                ];
            }
        }

        $zohoId = $mapping['zoho_record_id'] ?? null;
        if (empty($zohoId)) {
            $zohoId = $this->findExistingZohoItemId($product['product_name'], $product['barcode'] ?? null);
        }

        $payload = $this->buildZohoPayload($product, $taxPercentage);

        if (!empty($zohoId)) {
            $res = $this->client->put("/items/{$zohoId}", $payload, [], [
                'module'          => 'items',
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
                'operation'       => 'UPDATE',
            ]);
        } else {
            $res = $this->client->post('/items', $payload, [], [
                'module'          => 'items',
                'local_record_id' => $localId,
                'operation'       => 'CREATE',
            ]);
        }

        if (empty($res['success'])) {
            $this->mappingModel->markStatus('items', $localId, 'FAILED', $res['message'] ?? 'Sync failed');
            return $res;
        }

        $zohoItem  = $res['data']['item'] ?? [];
        $newZohoId = (string) ($zohoItem['item_id'] ?? $zohoId);

        $localStatus = strtolower((string) ($product['status'] ?? 'active'));
        $zohoStatus  = strtolower((string) ($zohoItem['status'] ?? 'active'));
        if ($localStatus !== $zohoStatus && !empty($newZohoId)) {
            $statusEndpoint = $localStatus === 'inactive' ? "/items/{$newZohoId}/inactive" : "/items/{$newZohoId}/active";
            $this->client->post($statusEndpoint, [], [], [
                'module'          => 'items',
                'local_record_id' => $localId,
                'zoho_record_id'  => $newZohoId,
                'operation'       => 'SYNC_STATUS',
            ]);
        }

        $normalizedZoho = !empty($zohoItem) ? $this->normalizeZohoItem($zohoItem) : $normalizedLocal;

        $this->saveMapping(
            $localId,
            $newZohoId,
            $product['barcode'] ?: $product['product_name'],
            $normalizedLocal,
            $normalizedZoho,
            'TO_ZOHO',
            $zohoItem['last_modified_time'] ?? null
        );

        return [
            'success'        => true,
            'zoho_record_id' => $newZohoId,
            'message'        => 'Product synchronized to Zoho Books.',
        ];
    }

    public function pullFromZoho(bool $incremental = false, bool $checkConflicts = true): array
    {
        $query = [];
        if ($incremental) {
            $cursor = $this->cursorModel->getCursor('items');
            if (!empty($cursor['last_zoho_modified_time'])) {
                $query['last_modified_time'] = $cursor['last_zoho_modified_time'];
            }
        }

        $listRes = $this->client->getAllPages('/items', 'items', $query, 50, [
            'module'    => 'items',
            'direction' => 'FROM_ZOHO',
            'operation' => 'LIST_ITEMS',
        ]);

        if (empty($listRes['success'])) {
            return $listRes;
        }

        $stats = [
            'total_processed' => 0,
            'created_count'   => 0,
            'updated_count'   => 0,
            'skipped_count'   => 0,
            'conflict_count'  => 0,
            'failed_count'    => 0,
        ];

        $latestModified = null;

        foreach ($listRes['records'] as $zohoItem) {
            $stats['total_processed']++;
            $applyRes = $this->applyZohoRecordToLocal($zohoItem, !$checkConflicts);
            $act      = $applyRes['action'] ?? 'SKIPPED';
            if ($act === 'CREATED') {
                $stats['created_count']++;
            } elseif ($act === 'UPDATED') {
                $stats['updated_count']++;
            } elseif ($act === 'CONFLICT') {
                $stats['conflict_count']++;
            } elseif ($act === 'FAILED') {
                $stats['failed_count']++;
            } else {
                $stats['skipped_count']++;
            }

            if (!empty($zohoItem['last_modified_time'])) {
                $latestModified = $zohoItem['last_modified_time'];
            }
        }

        $this->cursorModel->updateCursor('items', [
            'last_zoho_modified_time' => $latestModified ?: date('Y-m-d\TH:i:sO'),
            'last_full_sync_at'       => date('Y-m-d H:i:s'),
        ]);

        return array_merge(['success' => true], $stats);
    }

    public function applyZohoRecordToLocal(array $zohoItem, bool $forceOverwrite = false): array
    {
        $zohoId = (string) ($zohoItem['item_id'] ?? '');
        if ($zohoId === '') {
            return ['success' => false, 'action' => 'FAILED', 'message' => 'Missing Zoho item_id'];
        }

        $mapping      = $this->mappingModel->findByZoho('items', $zohoId);
        $localProduct = null;

        if ($mapping) {
            $localProduct = $this->productModel->find((int) $mapping['local_record_id']);
        }

        if (!$localProduct) {
            $sku = trim((string) ($zohoItem['sku'] ?? ''));
            if ($sku !== '') {
                $localProduct = $this->productModel->where('barcode', $sku)->first();
            }
            if (!$localProduct && !empty($zohoItem['name'])) {
                $localProduct = $this->productModel->where('product_name', trim($zohoItem['name']))->first();
            }
        }

        $normalizedZoho = $this->normalizeZohoItem($zohoItem);

        if ($localProduct && !$forceOverwrite && $mapping) {
            $localId = (int) $localProduct['id'];
            $taxPct  = 0.0;
            if (!empty($localProduct['tax_id'])) {
                $tRow   = $this->taxModel->find((int) $localProduct['tax_id']);
                $taxPct = (float) ($tRow['percentage'] ?? 0);
            }
            $normalizedLocal = $this->normalizeLocalProduct($localProduct, $taxPct);

            $eval = $this->conflictService->evaluate(
                'items',
                $localId,
                $zohoId,
                $localProduct['product_name'] ?? $zohoId,
                $normalizedLocal,
                $normalizedZoho,
                $localProduct['updated_at'] ?? null,
                $zohoItem['last_modified_time'] ?? null,
                $mapping
            );

            if ($eval['action'] === 'NO_CHANGE') {
                return ['success' => true, 'action' => 'SKIPPED', 'local_record_id' => $localId];
            }
            if ($eval['action'] === 'CONFLICT') {
                return ['success' => false, 'action' => 'CONFLICT', 'local_record_id' => $localId, 'conflict_id' => $eval['conflict_id']];
            }
            if ($eval['action'] === 'APPLY_LOCAL') {
                $this->pushToZoho($localId, 'UPDATE');
                return ['success' => true, 'action' => 'UPDATED', 'local_record_id' => $localId];
            }
        }

        return ZohoSyncContext::runAsZoho(function () use ($zohoItem, $zohoId, $localProduct, $normalizedZoho) {
            $zohoTaxPct = (float) ($zohoItem['tax_percentage'] ?? 0);
            if ($zohoTaxPct <= 0 && !empty($zohoItem['tax_preferences'][0]['tax_percentage'])) {
                $zohoTaxPct = (float) $zohoItem['tax_preferences'][0]['tax_percentage'];
            }
            $localTaxId = $this->taxMapper->resolveLocalTaxId($zohoTaxPct);

            $sku = trim((string) ($zohoItem['sku'] ?? ''));
            if ($sku === '' && !$localProduct) {
                $sku = 'ZITEM-' . substr($zohoId, -6);
            } elseif ($sku === '' && $localProduct) {
                $sku = $localProduct['barcode'];
            }

            $catId = $localProduct['category_id'] ?? null;
            if (empty($catId)) {
                $catModel = new ProductCategoryModel();
                $firstCat = $catModel->first();
                $catId    = $firstCat['id'] ?? 1;
            }

            $productData = [
                'product_name'  => trim((string) ($zohoItem['name'] ?? 'Unnamed Item')),
                'barcode'       => $sku,
                'category_id'   => $catId,
                'hsn_code'      => trim((string) ($zohoItem['hsn_or_sac'] ?? ($localProduct['hsn_code'] ?? ''))) ?: null,
                'description'   => $zohoItem['description'] ?? ($localProduct['description'] ?? null),
                'unit'          => trim((string) ($zohoItem['unit'] ?? ($localProduct['unit'] ?? 'pcs'))),
                'selling_price' => (float) ($zohoItem['rate'] ?? ($localProduct['selling_price'] ?? 0)),
                'tax_id'        => $localTaxId ?? ($localProduct['tax_id'] ?? null),
                'status'        => strtolower((string) ($zohoItem['status'] ?? 'active')) === 'inactive' ? 'inactive' : 'active',
            ];

            if ($localProduct) {
                $localId = (int) $localProduct['id'];
                $this->productModel->skipValidation(true)->update($localId, $productData);
                $action = 'UPDATED';
            } else {
                $productData['total_stock'] = (int) ($zohoItem['available_stock'] ?? $zohoItem['stock_on_hand'] ?? 0);
                $localId = (int) $this->productModel->skipValidation(true)->insert($productData, true);
                $action = 'CREATED';
            }

            $updatedProduct  = $this->productModel->find($localId);
            $normalizedLocal = $this->normalizeLocalProduct($updatedProduct, $zohoTaxPct);

            $this->saveMapping(
                $localId,
                $zohoId,
                $updatedProduct['barcode'] ?: $updatedProduct['product_name'],
                $normalizedLocal,
                $normalizedZoho,
                'FROM_ZOHO',
                $zohoItem['last_modified_time'] ?? null
            );

            return [
                'success'         => true,
                'action'          => $action,
                'local_record_id' => $localId,
                'zoho_record_id'  => $zohoId,
            ];
        });
    }

    protected function buildZohoPayload(array $product, float $taxPercentage): array
    {
        $hsn = trim((string) ($product['hsn_code'] ?? ''));
        $payload = [
            'name'         => trim((string) $product['product_name']),
            'sku'          => trim((string) ($product['barcode'] ?? '')),
            'product_type' => (strpos($hsn, '99') === 0) ? 'service' : 'goods',
            'rate'         => round((float) ($product['selling_price'] ?? 0), 2),
            'unit'         => trim((string) ($product['unit'] ?? 'pcs')) ?: 'pcs',
            'description'  => (string) ($product['description'] ?? ''),
        ];

        if ($hsn !== '') {
            $payload['hsn_or_sac'] = $hsn;
        }

        if ($taxPercentage > 0) {
            $intraTaxId = $this->taxMapper->resolveZohoTaxId($taxPercentage, false);
            $interTaxId = $this->taxMapper->resolveZohoTaxId($taxPercentage, true);
            $prefs = [];
            if ($intraTaxId) {
                $prefs[] = ['tax_specification' => 'intra', 'tax_id' => $intraTaxId];
            }
            if ($interTaxId) {
                $prefs[] = ['tax_specification' => 'inter', 'tax_id' => $interTaxId];
            }
            if (!empty($prefs)) {
                $payload['tax_preferences'] = $prefs;
            }
        }

        return $payload;
    }

    protected function findExistingZohoItemId(string $name, ?string $sku): ?string
    {
        if (!empty($sku)) {
            $res = $this->client->get('/items', ['sku' => trim($sku)], [
                'module'    => 'items',
                'operation' => 'LOOKUP_BY_SKU',
            ]);
            if (!empty($res['data']['items'][0]['item_id'])) {
                return (string) $res['data']['items'][0]['item_id'];
            }
        }

        $res = $this->client->get('/items', ['name' => trim($name)], [
            'module'    => 'items',
            'operation' => 'LOOKUP_BY_NAME',
        ]);
        if (!empty($res['data']['items'][0]['item_id'])) {
            return (string) $res['data']['items'][0]['item_id'];
        }

        return null;
    }

    public function normalizeLocalProduct(array $product, float $taxPercentage): array
    {
        return [
            'product_name'   => trim((string) ($product['product_name'] ?? '')),
            'sku'            => trim((string) ($product['barcode'] ?? '')),
            'hsn_code'       => trim((string) ($product['hsn_code'] ?? '')),
            'unit'           => strtolower(trim((string) ($product['unit'] ?? 'pcs'))),
            'selling_price'  => round((float) ($product['selling_price'] ?? 0), 2),
            'tax_percentage' => round($taxPercentage, 2),
            'status'         => strtolower(trim((string) ($product['status'] ?? 'active'))),
        ];
    }

    public function normalizeZohoItem(array $zoho): array
    {
        $taxPct = (float) ($zoho['tax_percentage'] ?? 0);
        if ($taxPct <= 0 && !empty($zoho['tax_preferences'][0]['tax_percentage'])) {
            $taxPct = (float) $zoho['tax_preferences'][0]['tax_percentage'];
        }
        return [
            'product_name'   => trim((string) ($zoho['name'] ?? '')),
            'sku'            => trim((string) ($zoho['sku'] ?? '')),
            'hsn_code'       => trim((string) ($zoho['hsn_or_sac'] ?? '')),
            'unit'           => strtolower(trim((string) ($zoho['unit'] ?? 'pcs'))),
            'selling_price'  => round((float) ($zoho['rate'] ?? 0), 2),
            'tax_percentage' => round($taxPct, 2),
            'status'         => strtolower(trim((string) ($zoho['status'] ?? 'active'))) === 'inactive' ? 'inactive' : 'active',
        ];
    }
}

