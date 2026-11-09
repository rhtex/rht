<?php

namespace App\Services\Zoho;

use App\Models\BankAccountModel;
use App\Models\ExpenseCategoryModel;
use App\Models\SettingModel;
use App\Models\StateModel;
use App\Models\TaxModel;
use App\Models\Zoho\ZohoSettingModel;
use App\Models\Zoho\ZohoSyncMappingModel;

class ZohoTaxMapperService
{
    protected ZohoApiClient $client;
    protected ZohoSettingModel $zohoSettingModel;
    protected ZohoSyncMappingModel $mappingModel;
    protected ?array $statesCache = null;
    protected ?string $companyStateCode = null;

    /**
     * Standard Indian GST State Code (2-digit) -> 2-letter ISO code fallback map.
     */
    protected array $gstNumberToStateCode = [
        '01' => 'JK', '02' => 'HP', '03' => 'PB', '04' => 'CH', '05' => 'UK',
        '06' => 'HR', '07' => 'DL', '08' => 'RJ', '09' => 'UP', '10' => 'BR',
        '11' => 'SK', '12' => 'AR', '13' => 'NL', '14' => 'MN', '15' => 'MZ',
        '16' => 'TR', '17' => 'ML', '18' => 'AS', '19' => 'WB', '20' => 'JH',
        '21' => 'OD', '22' => 'CG', '23' => 'MP', '24' => 'GJ', '26' => 'DN',
        '27' => 'MH', '29' => 'KA', '30' => 'GA', '31' => 'LD', '32' => 'KL',
        '33' => 'TN', '34' => 'PY', '35' => 'AN', '36' => 'TS', '37' => 'AP',
        '38' => 'LA',
    ];

    public function __construct(?ZohoApiClient $client = null)
    {
        $this->client           = $client ?? new ZohoApiClient();
        $this->zohoSettingModel = new ZohoSettingModel();
        $this->mappingModel     = new ZohoSyncMappingModel();
    }

    public function mapLocalGstTypeToZoho(?string $gstType, ?string $gstin = null, bool $isVendor = false): string
    {
        $gstin   = strtoupper(trim((string) $gstin));
        $gstType = trim((string) $gstType);

        if ($gstType === 'Composition') {
            return 'business_composition_gst';
        }
        if ($gstType === 'Regular' || (!empty($gstin) && strlen($gstin) === 15)) {
            return 'business_gst';
        }
        if ($gstType === 'Unregistered') {
            return 'business_none';
        }
        if ($gstType === 'Consumer') {
            return $isVendor ? 'business_none' : 'consumer';
        }

        return !empty($gstin) ? 'business_gst' : ($isVendor ? 'business_none' : 'consumer');
    }

    public function mapZohoGstTreatmentToLocal(?string $gstTreatment, bool $isVendor = false): string
    {
        $gstTreatment = strtolower(trim((string) $gstTreatment));
        return match ($gstTreatment) {
            'business_gst'             => 'Regular',
            'business_composition_gst' => 'Composition',
            'business_none'            => 'Unregistered',
            'consumer'                 => $isVendor ? 'Unregistered' : 'Consumer',
            default                    => $isVendor ? 'Unregistered' : 'Consumer',
        };
    }

    public function getCompanyStateCode(): string
    {
        if ($this->companyStateCode !== null) {
            return $this->companyStateCode;
        }

        $settingModel = new SettingModel();
        $companyStateId = (int) $settingModel->getSetting('company_state', 1);
        $states = $this->getStates();

        foreach ($states as $st) {
            if ((int) $st['id'] === $companyStateId && !empty($st['state_code'])) {
                $this->companyStateCode = strtoupper(trim($st['state_code']));
                return $this->companyStateCode;
            }
        }

        $companyGstin = strtoupper(trim((string) $settingModel->getSetting('company_gstin', '')));
        if (strlen($companyGstin) >= 2) {
            $prefix = substr($companyGstin, 0, 2);
            if (isset($this->gstNumberToStateCode[$prefix])) {
                $this->companyStateCode = $this->gstNumberToStateCode[$prefix];
                return $this->companyStateCode;
            }
        }

        $this->companyStateCode = 'TN';
        return $this->companyStateCode;
    }

    public function resolveStateCode(?int $stateId = null, ?string $gstin = null, ?string $stateName = null): string
    {
        $states = $this->getStates();

        if (!empty($stateId)) {
            foreach ($states as $st) {
                if ((int) $st['id'] === (int) $stateId && !empty($st['state_code'])) {
                    return strtoupper(trim($st['state_code']));
                }
            }
        }

        $gstin = strtoupper(trim((string) $gstin));
        if (strlen($gstin) >= 2) {
            $prefix = substr($gstin, 0, 2);
            if (isset($this->gstNumberToStateCode[$prefix])) {
                return $this->gstNumberToStateCode[$prefix];
            }
        }

        if (!empty($stateName)) {
            $norm = strtolower(trim($stateName));
            foreach ($states as $st) {
                if (strtolower(trim($st['name'])) === $norm || strtolower(trim((string) $st['state_code'])) === $norm) {
                    return strtoupper(trim($st['state_code']));
                }
            }
        }

        return $this->getCompanyStateCode();
    }

    public function resolveLocalStateId(?string $stateCode = null, ?string $stateName = null, ?string $gstin = null): int
    {
        $states = $this->getStates();

        if (!empty($stateCode)) {
            $code = strtoupper(trim($stateCode));
            foreach ($states as $st) {
                if (strtoupper(trim((string) $st['state_code'])) === $code) {
                    return (int) $st['id'];
                }
            }
        }

        if (!empty($stateName)) {
            $name = strtolower(trim($stateName));
            foreach ($states as $st) {
                if (strtolower(trim($st['name'])) === $name) {
                    return (int) $st['id'];
                }
            }
        }

        $gstin = strtoupper(trim((string) $gstin));
        if (strlen($gstin) >= 2) {
            $prefix = substr($gstin, 0, 2);
            foreach ($states as $st) {
                if (trim((string) ($st['gst_state_code'] ?? '')) === $prefix) {
                    return (int) $st['id'];
                }
            }
        }

        $settingModel = new SettingModel();
        return (int) $settingModel->getSetting('company_state', 1);
    }

    /**
     * Synchronize and cache Zoho Taxes, Tax Groups, and Chart of Accounts metadata.
     */
    public function ensureMetadataCache(bool $forceRefresh = false): array
    {
        $config = $this->zohoSettingModel->getConfig(false);
        $cache  = $config['finance_account_map'] ?? [];

        if (!$forceRefresh && !empty($cache['cached_at']) && (time() - (int) $cache['cached_at']) < 3600) {
            return $cache;
        }

        // 1. Fetch Taxes from Zoho
        $taxesRes = $this->client->get('/settings/taxes', ['per_page' => 200], [
            'module'    => 'taxes',
            'operation' => 'FETCH_TAXES',
        ]);
        $zohoTaxes = $taxesRes['data']['taxes'] ?? [];

        // 2. Fetch Chart of Accounts from Zoho
        $coaRes = $this->client->get('/chartofaccounts', ['per_page' => 200], [
            'module'    => 'accounts',
            'operation' => 'FETCH_CHART_OF_ACCOUNTS',
        ]);
        $zohoAccounts = $coaRes['data']['chartofaccounts'] ?? [];

        $intraTaxMap = []; // percentage -> tax_group_id / tax_id
        $interTaxMap = []; // percentage -> igst tax_id
        $anyTaxMap   = []; // percentage -> tax_id
        $taxIdToRate = []; // zoho_tax_id -> percentage

        foreach ($zohoTaxes as $t) {
            $taxId    = (string) ($t['tax_id'] ?? '');
            $rate     = number_format((float) ($t['tax_percentage'] ?? 0), 2, '.', '');
            $specType = strtolower((string) ($t['tax_specific_type'] ?? ''));
            $taxType  = strtolower((string) ($t['tax_type'] ?? ''));
            $taxName  = strtolower((string) ($t['tax_name'] ?? ''));

            if ($taxId === '') {
                continue;
            }

            $taxIdToRate[$taxId] = (float) $rate;
            $anyTaxMap[$rate]    = $taxId;

            if ($specType === 'igst' || strpos($taxName, 'igst') !== false) {
                $interTaxMap[$rate] = $taxId;
            } elseif ($taxType === 'tax_group' || strpos($taxName, 'gst') !== false) {
                $intraTaxMap[$rate] = $taxId;
            }
        }

        // Sync Zoho tax percentages into local `taxes` table if missing
        $localTaxModel = new TaxModel();
        $localTaxes    = $localTaxModel->findAll();
        $localRates    = [];
        foreach ($localTaxes as $lt) {
            $localRates[number_format((float) $lt['percentage'], 2, '.', '')] = (int) $lt['id'];
        }

        // Map default accounts from Zoho Chart of Accounts
        $defaultSalesAccountId    = '';
        $defaultPurchaseAccountId = '';
        $defaultExpenseAccountId  = '';
        $defaultCashAccountId     = '';
        $defaultBankAccountId     = '';
        $accountsByName           = [];

        foreach ($zohoAccounts as $acc) {
            $accId   = (string) ($acc['account_id'] ?? '');
            $accName = strtolower(trim((string) ($acc['account_name'] ?? '')));
            $accType = strtolower(trim((string) ($acc['account_type'] ?? '')));

            if ($accId === '') {
                continue;
            }
            $accountsByName[$accName] = $accId;

            if ($accType === 'income' && ($defaultSalesAccountId === '' || $accName === 'sales')) {
                $defaultSalesAccountId = $accId;
            }
            if (in_array($accType, ['cost_of_goods_sold', 'expense'], true) && ($defaultPurchaseAccountId === '' || strpos($accName, 'cost of goods') !== false || strpos($accName, 'purchase') !== false)) {
                $defaultPurchaseAccountId = $accId;
            }
            if ($accType === 'expense' && ($defaultExpenseAccountId === '' || strpos($accName, 'other expense') !== false || strpos($accName, 'office') !== false)) {
                $defaultExpenseAccountId = $accId;
            }
            if ($accType === 'cash' && ($defaultCashAccountId === '' || strpos($accName, 'petty cash') !== false || strpos($accName, 'undeposited') !== false)) {
                $defaultCashAccountId = $accId;
            }
            if ($accType === 'bank' && $defaultBankAccountId === '') {
                $defaultBankAccountId = $accId;
            }
        }

        $cache = [
            'cached_at'                   => time(),
            'intra_tax_map'               => $intraTaxMap,
            'inter_tax_map'               => $interTaxMap,
            'any_tax_map'                 => $anyTaxMap,
            'tax_id_to_rate'              => $taxIdToRate,
            'default_sales_account_id'    => $defaultSalesAccountId,
            'default_purchase_account_id' => $defaultPurchaseAccountId ?: $defaultExpenseAccountId,
            'default_expense_account_id'  => $defaultExpenseAccountId ?: $defaultPurchaseAccountId,
            'default_cash_account_id'     => $defaultCashAccountId ?: $defaultBankAccountId,
            'default_bank_account_id'     => $defaultBankAccountId ?: $defaultCashAccountId,
            'accounts_by_name'            => $accountsByName,
        ];

        $this->zohoSettingModel->saveConfig([
            'finance_default_account_map_json' => $cache,
        ]);

        return $cache;
    }

    /**
     * Resolve Zoho tax_id / tax_group_id for a given percentage and intra/inter-state flag.
     */
    public function resolveZohoTaxId(float $taxPercentage, bool $isInterState): ?string
    {
        if ($taxPercentage <= 0) {
            return null;
        }

        $cache = $this->ensureMetadataCache();
        $key   = number_format($taxPercentage, 2, '.', '');

        if ($isInterState && !empty($cache['inter_tax_map'][$key])) {
            return (string) $cache['inter_tax_map'][$key];
        }
        if (!$isInterState && !empty($cache['intra_tax_map'][$key])) {
            return (string) $cache['intra_tax_map'][$key];
        }
        if (!empty($cache['any_tax_map'][$key])) {
            return (string) $cache['any_tax_map'][$key];
        }

        return null;
    }

    /**
     * Resolve local tax_id from a percentage.
     */
    public function resolveLocalTaxId(float $taxPercentage): ?int
    {
        $taxModel = new TaxModel();
        $taxes    = $taxModel->findAll();
        foreach ($taxes as $t) {
            if (abs((float) $t['percentage'] - $taxPercentage) < 0.05) {
                return (int) $t['id'];
            }
        }
        return null;
    }

    /**
     * Extract total tax percentage and CGST/SGST/IGST rates from a Zoho line item.
     */
    public function extractLineTaxRates(array $zohoLineItem, bool $isInterState): array
    {
        $taxPct = (float) ($zohoLineItem['tax_percentage'] ?? 0);

        if ($taxPct <= 0 && !empty($zohoLineItem['line_item_taxes']) && is_array($zohoLineItem['line_item_taxes'])) {
            $sum = 0.0;
            foreach ($zohoLineItem['line_item_taxes'] as $lt) {
                $sum += (float) ($lt['tax_percentage'] ?? 0);
            }
            $taxPct = $sum;
        }

        if ($taxPct <= 0 && !empty($zohoLineItem['tax_id'])) {
            $cache  = $this->ensureMetadataCache();
            $taxPct = (float) ($cache['tax_id_to_rate'][(string) $zohoLineItem['tax_id']] ?? 0);
        }

        if ($isInterState) {
            return [
                'tax_percentage' => round($taxPct, 2),
                'cgst_rate'      => 0.00,
                'sgst_rate'      => 0.00,
                'igst_rate'      => round($taxPct, 2),
            ];
        }

        $half = round($taxPct / 2, 2);
        return [
            'tax_percentage' => round($taxPct, 2),
            'cgst_rate'      => $half,
            'sgst_rate'      => $half,
            'igst_rate'      => 0.00,
        ];
    }

    /**
     * Resolve Zoho paid_through_account_id / account_id for a local bank_account_id or payment_mode.
     */
    public function resolvePaidThroughAccountId(?int $bankAccountId = null, ?string $paymentMode = null): ?string
    {
        if (!empty($bankAccountId)) {
            $mapped = $this->mappingModel->findByLocal('bank_accounts', $bankAccountId);
            if ($mapped && !empty($mapped['zoho_record_id'])) {
                return $mapped['zoho_record_id'];
            }

            $bankModel = new BankAccountModel();
            $bank      = $bankModel->find($bankAccountId);
            if ($bank) {
                $cache = $this->ensureMetadataCache();
                $name  = strtolower(trim((string) ($bank['bank_name'] ?? '')));
                if (!empty($cache['accounts_by_name'][$name])) {
                    return (string) $cache['accounts_by_name'][$name];
                }
            }
        }

        $cache = $this->ensureMetadataCache();
        if (strtolower(trim((string) $paymentMode)) === 'cash') {
            return $cache['default_cash_account_id'] ?: null;
        }

        return $cache['default_bank_account_id'] ?: ($cache['default_cash_account_id'] ?: null);
    }

    /**
     * Resolve Zoho Expense account_id from local expense category.
     */
    public function resolveExpenseAccountId(?int $categoryId = null, ?string $categoryName = null): ?string
    {
        if (!empty($categoryId)) {
            $mapped = $this->mappingModel->findByLocal('expense_categories', $categoryId);
            if ($mapped && !empty($mapped['zoho_record_id'])) {
                return $mapped['zoho_record_id'];
            }
            if (empty($categoryName)) {
                $catModel = new ExpenseCategoryModel();
                $cat      = $catModel->find($categoryId);
                $categoryName = $cat['category_name'] ?? '';
            }
        }

        $cache = $this->ensureMetadataCache();
        if (!empty($categoryName)) {
            $norm = strtolower(trim($categoryName));
            if (!empty($cache['accounts_by_name'][$norm])) {
                return (string) $cache['accounts_by_name'][$norm];
            }
        }

        return $cache['default_expense_account_id'] ?: null;
    }

    protected function getStates(): array
    {
        if ($this->statesCache === null) {
            $stateModel = new StateModel();
            $this->statesCache = $stateModel->findAll();
        }
        return $this->statesCache;
    }
}

