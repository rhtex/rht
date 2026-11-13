<?php

namespace App\Services\Zoho\Modules;

use App\Models\AddressModel;
use App\Models\CustomerModel;
use App\Models\ProductModel;
use App\Models\VendorModel;
use App\Models\Zoho\ZohoSettingModel;
use App\Models\Zoho\ZohoSyncCursorModel;
use App\Models\Zoho\ZohoSyncMappingModel;
use App\Services\Zoho\ZohoApiClient;
use App\Services\Zoho\ZohoConflictService;
use App\Services\Zoho\ZohoHashService;
use App\Services\Zoho\ZohoTaxMapperService;

abstract class AbstractModuleSyncService
{
    protected ZohoApiClient $client;
    protected ZohoSettingModel $settingModel;
    protected ZohoSyncMappingModel $mappingModel;
    protected ZohoSyncCursorModel $cursorModel;
    protected ZohoTaxMapperService $taxMapper;
    protected ZohoConflictService $conflictService;
    protected $progressCallback = null;

    public function setProgressCallback(?callable $cb): self
    {
        $this->progressCallback = $cb;
        return $this;
    }

    protected function emitProgress(int $pct, string $msg, array $extra = []): void
    {
        if ($this->progressCallback && is_callable($this->progressCallback)) {
            call_user_func($this->progressCallback, $pct, $msg, $extra);
        }
    }

    public function __construct(?ZohoApiClient $client = null)
    {
        $this->client          = $client ?? new ZohoApiClient();
        $this->settingModel    = new ZohoSettingModel();
        $this->mappingModel    = new ZohoSyncMappingModel();
        $this->cursorModel     = new ZohoSyncCursorModel();
        $this->taxMapper       = new ZohoTaxMapperService($this->client);
        $this->conflictService = new ZohoConflictService();
    }

    abstract public function getModuleName(): string;

    abstract public function pushToZoho(int $localId, string $operation = 'UPDATE'): array;

    abstract public function pullFromZoho(bool $incremental = false, bool $checkConflicts = true): array;

    abstract public function applyZohoRecordToLocal(array $zohoRecord, bool $forceOverwrite = false): array;

    /**
     * Pull a single record from Zoho Books by its human document number or record ID.
     * Supports invoices (invoice_number), bills (bill_number), quotations (estimate_number),
     * sales orders (salesorder_number), purchase orders (purchaseorder_number),
     * credit notes (creditnote_number), vendor credits (vendor_credit_number),
     * customers/vendors (contact_name / GSTIN), items (name / sku), payments (payment_number).
     */
    public function pullByNumberFromZoho(string $numberOrId): array
    {
        $identifier = trim($numberOrId);
        if ($identifier === '') {
            return ['success' => false, 'message' => 'Please provide a valid document or record number.'];
        }

        $module = $this->getModuleName();
        $endpoints = [
            'invoices'          => ['list' => '/invoices',          'detail' => '/invoices/%s',          'num_key' => 'invoice_number',       'id_key' => 'invoice_id',       'rec_key' => 'invoice',       'list_key' => 'invoices'],
            'bills'             => ['list' => '/bills',             'detail' => '/bills/%s',             'num_key' => 'bill_number',          'id_key' => 'bill_id',          'rec_key' => 'bill',          'list_key' => 'bills'],
            'quotations'        => ['list' => '/estimates',         'detail' => '/estimates/%s',         'num_key' => 'estimate_number',      'id_key' => 'estimate_id',      'rec_key' => 'estimate',      'list_key' => 'estimates'],
            'sales_orders'      => ['list' => '/salesorders',       'detail' => '/salesorders/%s',       'num_key' => 'salesorder_number',    'id_key' => 'salesorder_id',    'rec_key' => 'salesorder',    'list_key' => 'salesorders'],
            'purchase_orders'   => ['list' => '/purchaseorders',    'detail' => '/purchaseorders/%s',    'num_key' => 'purchaseorder_number', 'id_key' => 'purchaseorder_id', 'rec_key' => 'purchaseorder', 'list_key' => 'purchaseorders'],
            'credit_notes'      => ['list' => '/creditnotes',       'detail' => '/creditnotes/%s',       'num_key' => 'creditnote_number',    'id_key' => 'creditnote_id',    'rec_key' => 'creditnote',    'list_key' => 'creditnotes'],
            'vendor_credits'    => ['list' => '/vendorcredits',     'detail' => '/vendorcredits/%s',     'num_key' => 'vendor_credit_number', 'id_key' => 'vendor_credit_id', 'rec_key' => 'vendor_credit', 'list_key' => 'vendor_credits'],
            'customer_payments' => ['list' => '/customerpayments',  'detail' => '/customerpayments/%s',  'num_key' => 'payment_number',       'id_key' => 'payment_id',       'rec_key' => 'payment',       'list_key' => 'customerpayments'],
            'vendor_payments'   => ['list' => '/vendorpayments',    'detail' => '/vendorpayments/%s',    'num_key' => 'payment_number',       'id_key' => 'payment_id',       'rec_key' => 'payment',       'list_key' => 'vendorpayments'],
            'expenses'          => ['list' => '/expenses',          'detail' => '/expenses/%s',          'num_key' => 'expense_id',           'id_key' => 'expense_id',       'rec_key' => 'expense',       'list_key' => 'expenses'],
            'items'             => ['list' => '/items',             'detail' => '/items/%s',             'num_key' => 'name',                 'id_key' => 'item_id',          'rec_key' => 'item',          'list_key' => 'items'],
            'customers'         => ['list' => '/contacts',          'detail' => '/contacts/%s',          'num_key' => 'contact_name',         'id_key' => 'contact_id',       'rec_key' => 'contact',       'list_key' => 'contacts'],
            'vendors'           => ['list' => '/contacts',          'detail' => '/contacts/%s',          'num_key' => 'contact_name',         'id_key' => 'contact_id',       'rec_key' => 'contact',       'list_key' => 'contacts'],
        ];

        $cfg = $endpoints[$module] ?? null;
        if (!$cfg) {
            return ['success' => false, 'message' => "Single pull by number is not supported for module: {$module}"];
        }

        $zohoId = null;
        $matchedRecord = null;

        // 1. Direct fetch if input looks like a numeric Zoho record ID
        if (ctype_digit($identifier) && strlen($identifier) >= 15) {
            $zohoId = $identifier;
        } else {
            // 2. Query list endpoint by document number
            $queryParams = [$cfg['num_key'] => $identifier];
            if ($module === 'customers') {
                $queryParams['contact_type'] = 'customer';
            } elseif ($module === 'vendors') {
                $queryParams['contact_type'] = 'vendor';
            }

            $lookupRes = $this->client->get($cfg['list'], $queryParams, [
                'module'    => $module,
                'operation' => 'LOOKUP_BY_NUMBER_SINGLE_PULL',
            ]);

            $listRecords = $lookupRes['data'][$cfg['list_key']] ?? [];
            if (!empty($listRecords[0][$cfg['id_key']])) {
                $zohoId = (string) $listRecords[0][$cfg['id_key']];
                $matchedRecord = $listRecords[0];
            } else {
                // Secondary check: for items or contacts, try secondary lookup keys (SKU or GSTIN or case-insensitive search)
                if ($module === 'items') {
                    $skuLookup = $this->client->get('/items', ['sku' => $identifier], ['module' => 'items', 'operation' => 'LOOKUP_BY_SKU_SINGLE']);
                    if (!empty($skuLookup['data']['items'][0]['item_id'])) {
                        $zohoId = (string) $skuLookup['data']['items'][0]['item_id'];
                    }
                } elseif ($module === 'customers' || $module === 'vendors') {
                    $contactType = $module === 'customers' ? 'customer' : 'vendor';
                    $gstLookup = $this->client->get('/contacts', ['contact_type' => $contactType, 'gst_no' => strtoupper($identifier)], ['module' => $module, 'operation' => 'LOOKUP_BY_GST_SINGLE']);
                    if (!empty($gstLookup['data']['contacts'][0]['contact_id'])) {
                        $zohoId = (string) $gstLookup['data']['contacts'][0]['contact_id'];
                    }
                }
            }
        }

        if (empty($zohoId)) {
            return [
                'success' => false,
                'message' => "No record found in Zoho Books for {$module} with number/identifier '{$identifier}'. Please verify the number in Zoho Books."
            ];
        }

        // 3. Fetch detailed record
        $detailEndpoint = sprintf($cfg['detail'], $zohoId);
        $detailRes = $this->client->get($detailEndpoint, [], [
            'module'         => $module,
            'zoho_record_id' => $zohoId,
            'direction'      => 'FROM_ZOHO',
            'operation'      => 'GET_SINGLE_RECORD_DETAIL',
        ]);

        $fullRecord = !empty($detailRes['success']) ? ($detailRes['data'][$cfg['rec_key']] ?? $matchedRecord) : $matchedRecord;
        if (empty($fullRecord)) {
            return [
                'success' => false,
                'message' => "Found Zoho ID {$zohoId} but failed to retrieve detailed record data from Zoho Books: " . ($detailRes['message'] ?? 'API error')
            ];
        }

        // 4. Apply to local database with force overwrite
        $applyRes = $this->applyZohoRecordToLocal($fullRecord, true);
        if (!empty($applyRes['success'])) {
            $applyRes['message'] = "Successfully pulled {$module} #{$identifier} (Zoho ID: {$zohoId}) into local database.";
            $applyRes['zoho_id'] = $zohoId;
        }

        return $applyRes;
    }

    protected function saveMapping(
        int $localId,
        string $zohoId,
        ?string $recordNumber,
        array $normalizedLocal,
        array $normalizedZoho,
        string $direction = 'TO_ZOHO',
        ?string $zohoLastModified = null,
        array $metadata = []
    ): void {
        $now = date('Y-m-d H:i:s');
        $this->mappingModel->upsertMapping($this->getModuleName(), $localId, $zohoId, [
            'zoho_record_number'      => $recordNumber,
            'last_local_sync_at'      => $direction === 'TO_ZOHO' ? $now : null,
            'last_zoho_sync_at'       => $direction === 'FROM_ZOHO' ? $now : null,
            'last_successful_sync_at' => $now,
            'sync_direction'          => $direction,
            'sync_status'             => 'SYNCED',
            'local_version_hash'      => ZohoHashService::hash($normalizedLocal),
            'zoho_version_hash'       => ZohoHashService::hash($normalizedZoho),
            'zoho_last_modified_time' => $zohoLastModified,
            'metadata_json'           => $metadata,
            'last_error'              => null,
        ]);
    }

    protected function ensureZohoCustomerId(int $localCustomerId): ?string
    {
        $mapped = $this->mappingModel->findByLocal('customers', $localCustomerId);
        if ($mapped && !empty($mapped['zoho_record_id'])) {
            return (string) $mapped['zoho_record_id'];
        }

        $custSync = new CustomerSyncService($this->client);
        $res      = $custSync->pushToZoho($localCustomerId, 'CREATE');
        return !empty($res['zoho_record_id']) ? (string) $res['zoho_record_id'] : null;
    }

    protected function ensureLocalCustomerId(string $zohoCustomerId, ?string $customerName = null): ?int
    {
        $zohoCustomerId = trim($zohoCustomerId);
        if ($zohoCustomerId === '') {
            return null;
        }

        $mapped = $this->mappingModel->findByZoho('customers', $zohoCustomerId);
        if ($mapped && !empty($mapped['local_record_id'])) {
            return (int) $mapped['local_record_id'];
        }

        $detail = $this->client->get("/contacts/{$zohoCustomerId}", [], [
            'module'         => 'customers',
            'zoho_record_id' => $zohoCustomerId,
            'direction'      => 'FROM_ZOHO',
            'operation'      => 'RESOLVE_CUSTOMER',
        ]);

        if (!empty($detail['success']) && !empty($detail['data']['contact'])) {
            $custSync = new CustomerSyncService($this->client);
            $applied  = $custSync->applyZohoRecordToLocal($detail['data']['contact'], true);
            if (!empty($applied['local_record_id'])) {
                return (int) $applied['local_record_id'];
            }
        }

        if (!empty($customerName)) {
            $custModel = new CustomerModel();
            $found     = $custModel->where('name', trim($customerName))->first();
            if ($found) {
                return (int) $found['id'];
            }
        }

        return null;
    }

    protected function ensureZohoVendorId(int $localVendorId): ?string
    {
        $mapped = $this->mappingModel->findByLocal('vendors', $localVendorId);
        if ($mapped && !empty($mapped['zoho_record_id'])) {
            return (string) $mapped['zoho_record_id'];
        }

        $vendorSync = new VendorSyncService($this->client);
        $res        = $vendorSync->pushToZoho($localVendorId, 'CREATE');
        return !empty($res['zoho_record_id']) ? (string) $res['zoho_record_id'] : null;
    }

    protected function ensureLocalVendorId(string $zohoVendorId, ?string $vendorName = null): ?int
    {
        $zohoVendorId = trim($zohoVendorId);
        if ($zohoVendorId === '') {
            return null;
        }

        $mapped = $this->mappingModel->findByZoho('vendors', $zohoVendorId);
        if ($mapped && !empty($mapped['local_record_id'])) {
            return (int) $mapped['local_record_id'];
        }

        $detail = $this->client->get("/contacts/{$zohoVendorId}", [], [
            'module'         => 'vendors',
            'zoho_record_id' => $zohoVendorId,
            'direction'      => 'FROM_ZOHO',
            'operation'      => 'RESOLVE_VENDOR',
        ]);

        if (!empty($detail['success']) && !empty($detail['data']['contact'])) {
            $vendorSync = new VendorSyncService($this->client);
            $applied    = $vendorSync->applyZohoRecordToLocal($detail['data']['contact'], true);
            if (!empty($applied['local_record_id'])) {
                return (int) $applied['local_record_id'];
            }
        }

        if (!empty($vendorName)) {
            $vendorModel = new VendorModel();
            $found       = $vendorModel->where('name', trim($vendorName))->first();
            if ($found) {
                return (int) $found['id'];
            }
        }

        return null;
    }

    protected function ensureZohoItemId(?int $localProductId): ?string
    {
        if (empty($localProductId)) {
            return null;
        }
        $mapped = $this->mappingModel->findByLocal('items', $localProductId);
        if ($mapped && !empty($mapped['zoho_record_id'])) {
            return (string) $mapped['zoho_record_id'];
        }

        $itemSync = new ItemSyncService($this->client);
        $res      = $itemSync->pushToZoho($localProductId, 'CREATE');
        return !empty($res['zoho_record_id']) ? (string) $res['zoho_record_id'] : null;
    }

    protected function ensureLocalProductId(?string $zohoItemId, ?string $itemName = null, ?string $sku = null, float $rate = 0.0, ?string $hsn = null): ?int
    {
        $zohoItemId = trim((string) $zohoItemId);
        if ($zohoItemId !== '') {
            $mapped = $this->mappingModel->findByZoho('items', $zohoItemId);
            if ($mapped && !empty($mapped['local_record_id'])) {
                return (int) $mapped['local_record_id'];
            }

            $detail = $this->client->get("/items/{$zohoItemId}", [], [
                'module'         => 'items',
                'zoho_record_id' => $zohoItemId,
                'direction'      => 'FROM_ZOHO',
                'operation'      => 'RESOLVE_ITEM',
            ]);
            if (!empty($detail['success']) && !empty($detail['data']['item'])) {
                $itemSync = new ItemSyncService($this->client);
                $applied  = $itemSync->applyZohoRecordToLocal($detail['data']['item'], true);
                if (!empty($applied['local_record_id'])) {
                    return (int) $applied['local_record_id'];
                }
            }
        }

        $productModel = new ProductModel();
        if (!empty($sku)) {
            $bySku = $productModel->where('barcode', trim($sku))->first();
            if ($bySku) {
                return (int) $bySku['id'];
            }
        }
        if (!empty($itemName)) {
            $byName = $productModel->where('product_name', trim($itemName))->first();
            if ($byName) {
                return (int) $byName['id'];
            }
        }

        return null;
    }

    protected function resolveCustomerPlaceOfSupply(int $localCustomerId, bool $isInterState): string
    {
        $companyStateCode = $this->taxMapper->getCompanyStateCode();
        if (!$isInterState) {
            return $companyStateCode;
        }

        $custModel = new CustomerModel();
        $addrModel = new AddressModel();
        $cust      = $custModel->find($localCustomerId);
        $billing   = $addrModel->getBillingAddress('customer', $localCustomerId);

        $custState = $this->taxMapper->resolveStateCode(
            $billing['state_id'] ?? null,
            $cust['gstin'] ?? null,
            $billing['state_name'] ?? null
        );

        // If customer state happens to equal company state but document is marked inter-state, use a distinct valid state or customer state
        return $custState ?: $companyStateCode;
    }

    protected function resolveVendorSourceOfSupply(int $localVendorId, bool $isInterState): string
    {
        $companyStateCode = $this->taxMapper->getCompanyStateCode();
        if (!$isInterState) {
            return $companyStateCode;
        }

        $vendorModel = new VendorModel();
        $addrModel   = new AddressModel();
        $vendor      = $vendorModel->find($localVendorId);
        $billing     = $addrModel->getBillingAddress('vendor', $localVendorId);

        $vendorState = $this->taxMapper->resolveStateCode(
            $billing['state_id'] ?? null,
            $vendor['gstin'] ?? null,
            $billing['state_name'] ?? null
        );

        return $vendorState ?: $companyStateCode;
    }
}
