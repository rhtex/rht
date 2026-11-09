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
