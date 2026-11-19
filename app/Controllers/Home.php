<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Home extends BaseController
{
    public function index()
    {
        $customerModel = new \App\Models\CustomerModel();
        $productModel = new \App\Models\ProductModel();
        $invoiceModel = new \App\Models\InvoiceModel();
        $billModel = new \App\Models\BillModel();
        $userModel = new \App\Models\UserModel();
        $employeeModel = new \App\Models\EmployeeModel();
        $quotationModel = new \App\Models\QuotationModel();
        $salesOrderModel = new \App\Models\SalesOrderModel();
        $returnModel = new \App\Models\SalesReturnModel();
        $weaverModel = new \App\Models\WeaverModel();
        $productionYarnModel = new \App\Models\ProductionYarnModel();
        $yarnPurchaseModel = new \App\Models\YarnPurchaseModel();
        $yarnWeavingDcModel = new \App\Models\YarnWeavingDcModel();
        $db = \Config\Database::connect();

        // 1. Basic Counts & Summary
        $data['totalCustomers'] = $customerModel->countAllResults();
        $data['totalProducts'] = $productModel->countAllResults();
        $data['userCount'] = $userModel->countAllResults();
        $data['employeeCount'] = $employeeModel->where('status', 'active')->countAllResults();

        // 2. Stock Management
        $data['itemsInStock'] = $productModel->selectSum('total_stock')->get()->getRow()->total_stock ?? 0;

        // 3. Financial Totals (Current Financial Year: Apr to Mar)
        $currentMonth = (int) date('n');
        $currentYear  = (int) date('Y');
        $fyStartYear  = ($currentMonth < 4) ? $currentYear - 1 : $currentYear;
        $fyEndYear    = $fyStartYear + 1;
        $fyStartDate  = "{$fyStartYear}-04-01";
        $fyEndDate    = "{$fyEndYear}-03-31";

        $data['fyLabel'] = "FY {$fyStartYear}-" . substr((string) $fyEndYear, 2);

        $data['totalSales'] = (float) ($invoiceModel->selectSum('total_amount')
            ->where('status !=', 'Void')
            ->where('status !=', 'Draft')
            ->where('invoice_date >=', $fyStartDate)
            ->where('invoice_date <=', $fyEndDate)
            ->get()->getRow()->total_amount ?? 0);

        $data['totalPurchases'] = (float) ($billModel->selectSum('total_amount')
            ->where('status !=', 'Void')
            ->where('status !=', 'Draft')
            ->where('bill_date >=', $fyStartDate)
            ->where('bill_date <=', $fyEndDate)
            ->get()->getRow()->total_amount ?? 0);

        // 4. Accounts Receivable & Payable (Real Outstanding Balances without date restriction)
        $today = date('Y-m-d');
        $invoiceBalances = $invoiceModel->select("
            COALESCE(SUM(balance), 0) as total,
            COALESCE(SUM(CASE WHEN due_date >= '$today' THEN balance ELSE 0 END), 0) as current,
            COALESCE(SUM(CASE WHEN due_date < '$today' THEN balance ELSE 0 END), 0) as overdue
        ")->where('status !=', 'Void')->where('balance >', 0)->first();

        $billBalances = $billModel->select("
            COALESCE(SUM(balance), 0) as total,
            COALESCE(SUM(CASE WHEN due_date >= '$today' THEN balance ELSE 0 END), 0) as current,
            COALESCE(SUM(CASE WHEN due_date < '$today' THEN balance ELSE 0 END), 0) as overdue
        ")->where('status !=', 'Void')->where('balance >', 0)->first();

        $data['accountsReceivable']        = (float) ($invoiceBalances['total'] ?? 0);
        $data['receivableCurrent']         = (float) ($invoiceBalances['current'] ?? 0);
        $data['receivableOverdue']         = (float) ($invoiceBalances['overdue'] ?? 0);

        $data['accountsPayable']           = (float) ($billBalances['total'] ?? 0);
        $data['payableCurrent']            = (float) ($billBalances['current'] ?? 0);
        $data['payableOverdue']            = (float) ($billBalances['overdue'] ?? 0);

        // 5. Sales Funnel & Operational Metrics
        $data['pendingQuotations'] = $quotationModel->whereIn('status', ['Draft', 'Sent'])->countAllResults();
        $data['activeSalesOrders'] = $salesOrderModel->whereIn('status', ['Pending', 'Processing'])->countAllResults();
        
        $data['pendingReturns'] = 0;
        try {
            $data['pendingReturns'] = $returnModel->where('status', 'Pending')->countAllResults();
        } catch (\Exception $e) {}

        // 5b. Production Metrics
        $data['totalWeavers'] = 0;
        $data['totalYarnStock'] = 0;
        $data['yarnPurchasedYTD'] = 0;
        $data['pendingWeavingJobs'] = 0;
        try {
            $data['totalWeavers'] = $weaverModel->where('status', 'active')->countAllResults();
            $data['totalYarnStock'] = $productionYarnModel->selectSum('stock_kg')->get()->getRow()->stock_kg ?? 0;
            $data['yarnPurchasedYTD'] = $yarnPurchaseModel->selectSum('total_weight_kg')
                ->where('YEAR(purchase_date)', $currentYear)
                ->get()->getRow()->total_weight_kg ?? 0;
            $data['pendingWeavingJobs'] = $yarnWeavingDcModel->whereIn('status', ['Open', 'Partially Received'])->countAllResults();
        } catch (\Exception $e) {}

        // 6. Financial Year Trend Data (Apr to Mar - 12 Months)
        $currentMonth = (int) date('n');
        $currentYear = (int) date('Y');
        $fyStartYear = ($currentMonth < 4) ? $currentYear - 1 : $currentYear;
        $fyEndYear = $fyStartYear + 1;

        $months = [];
        $salesTrend = [];
        $purchaseTrend = [];

        for ($i = 0; $i < 12; $i++) {
            $loopTime = mktime(0, 0, 0, 4 + $i, 1, $fyStartYear);
            $monthDay = date('Y-m', $loopTime);
            $months[] = date('M Y', $loopTime);

            // Sales per month
            $sTotal = $invoiceModel->selectSum('total_amount')
                ->where("DATE_FORMAT(invoice_date, '%Y-%m')", $monthDay)
                ->whereNotIn('status', ['Void', 'Draft'])
                ->get()->getRow()->total_amount ?? 0;
            $salesTrend[] = (float) $sTotal;

            // Purchases per month
            $pTotal = $billModel->selectSum('total_amount')
                ->where("DATE_FORMAT(bill_date, '%Y-%m')", $monthDay)
                ->whereNotIn('status', ['Void', 'Draft'])
                ->get()->getRow()->total_amount ?? 0;
            $purchaseTrend[] = (float) $pTotal;
        }

        $data['chartLabels'] = $months;
        $data['salesTrend'] = $salesTrend;
        $data['purchaseTrend'] = $purchaseTrend;

        // 7. Category Performance
        $data['categorySales'] = $db->table('invoice_items')
            ->select('product_categories.category_name as label, SUM(invoice_items.amount) as value')
            ->join('products', 'products.id = invoice_items.product_id')
            ->join('product_categories', 'product_categories.id = products.category_id')
            ->groupBy('products.category_id')
            ->orderBy('value', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        // 8. Top Products (Default: Current Financial Year)
        $data['topProducts'] = $this->fetchTopProducts($fyStartDate, $fyEndDate);

        // 9. Top Customers (Default: Current Financial Year)
        $data['topCustomers'] = $this->fetchTopCustomers($fyStartDate, $fyEndDate);

        // 10. Recent Lists
        $data['recentInvoices'] = $db->table('invoices')
            ->select('invoices.*, customers.name as customer_name')
            ->join('customers', 'customers.id = invoices.customer_id', 'left')
            ->orderBy('invoices.created_at', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        $data['recentBills'] = $db->table('bills')
            ->select('bills.*, vendors.name as vendor_name')
            ->join('vendors', 'vendors.id = bills.vendor_id', 'left')
            ->orderBy('bills.created_at', 'DESC')
            ->limit(5)
            ->get()->getResultArray();
            
        $data['recentQuotations'] = $db->table('quotations')
            ->select('quotations.*, customers.name as customer_name')
            ->join('customers', 'customers.id = quotations.customer_id', 'left')
            ->orderBy('quotations.created_at', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        return view('dashboard/index', $data);
    }

    public function lang($locale)
    {
        $session = session();
        $session->remove('lang');
        $session->set('lang', $locale);
        return redirect()->back();
    }

    public function getChartData()
    {
        $invoiceModel = new \App\Models\InvoiceModel();
        $billModel = new \App\Models\BillModel();
        $filter = $this->request->getGet('filter') ?? 'current_fy';

        $months = [];
        $salesTrend = [];
        $purchaseTrend = [];
        $datasets = [];

        $currentMonth = (int) date('n');
        $currentYear = (int) date('Y');
        $fyStartYear = ($currentMonth < 4) ? $currentYear - 1 : $currentYear;

        if ($filter === 'compare_last_year') {
            // Compare 12 months of Current FY (Apr-Mar) with Last FY (Apr-Mar)
            $lastFyStartYear = $fyStartYear - 1;
            $monthNames = ['Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar'];

            $curSales = [];
            $curPurchases = [];
            $lastSales = [];
            $lastPurchases = [];

            for ($i = 0; $i < 12; $i++) {
                $curMonthDay = date('Y-m', mktime(0, 0, 0, 4 + $i, 1, $fyStartYear));
                $lastMonthDay = date('Y-m', mktime(0, 0, 0, 4 + $i, 1, $lastFyStartYear));

                $s1 = $invoiceModel->selectSum('total_amount')
                    ->where("DATE_FORMAT(invoice_date, '%Y-%m')", $curMonthDay)
                    ->whereNotIn('status', ['Void', 'Draft'])
                    ->get()->getRow()->total_amount ?? 0;
                $p1 = $billModel->selectSum('total_amount')
                    ->where("DATE_FORMAT(bill_date, '%Y-%m')", $curMonthDay)
                    ->whereNotIn('status', ['Void', 'Draft'])
                    ->get()->getRow()->total_amount ?? 0;

                $s2 = $invoiceModel->selectSum('total_amount')
                    ->where("DATE_FORMAT(invoice_date, '%Y-%m')", $lastMonthDay)
                    ->whereNotIn('status', ['Void', 'Draft'])
                    ->get()->getRow()->total_amount ?? 0;
                $p2 = $billModel->selectSum('total_amount')
                    ->where("DATE_FORMAT(bill_date, '%Y-%m')", $lastMonthDay)
                    ->whereNotIn('status', ['Void', 'Draft'])
                    ->get()->getRow()->total_amount ?? 0;

                $curSales[] = (float) $s1;
                $curPurchases[] = (float) $p1;
                $lastSales[] = (float) $s2;
                $lastPurchases[] = (float) $p2;
            }

            return $this->response->setJSON([
                'labels' => $monthNames,
                'is_comparison' => true,
                'datasets' => [
                    [
                        'label' => "Revenue (FY {$fyStartYear}-" . substr($fyStartYear + 1, 2) . ")",
                        'data' => $curSales,
                        'backgroundColor' => '#15803d',
                        'borderRadius' => 4,
                        'barPercentage' => 0.85,
                        'categoryPercentage' => 0.75
                    ],
                    [
                        'label' => "Expense (FY {$fyStartYear}-" . substr($fyStartYear + 1, 2) . ")",
                        'data' => $curPurchases,
                        'backgroundColor' => '#c2410c',
                        'borderRadius' => 4,
                        'barPercentage' => 0.85,
                        'categoryPercentage' => 0.75
                    ],
                    [
                        'label' => "Revenue (FY {$lastFyStartYear}-" . substr($lastFyStartYear + 1, 2) . ")",
                        'data' => $lastSales,
                        'backgroundColor' => '#86efac',
                        'borderRadius' => 4,
                        'barPercentage' => 0.85,
                        'categoryPercentage' => 0.75
                    ],
                    [
                        'label' => "Expense (FY {$lastFyStartYear}-" . substr($lastFyStartYear + 1, 2) . ")",
                        'data' => $lastPurchases,
                        'backgroundColor' => '#fdba74',
                        'borderRadius' => 4,
                        'barPercentage' => 0.85,
                        'categoryPercentage' => 0.75
                    ]
                ]
            ]);
        } elseif ($filter === 'last_fy') {
            $targetStartYear = $fyStartYear - 1;
            for ($i = 0; $i < 12; $i++) {
                $loopTime = mktime(0, 0, 0, 4 + $i, 1, $targetStartYear);
                $monthDay = date('Y-m', $loopTime);
                $months[] = date('M Y', $loopTime);

                $sTotal = $invoiceModel->selectSum('total_amount')
                    ->where("DATE_FORMAT(invoice_date, '%Y-%m')", $monthDay)
                    ->whereNotIn('status', ['Void', 'Draft'])
                    ->get()->getRow()->total_amount ?? 0;
                $salesTrend[] = (float) $sTotal;

                $pTotal = $billModel->selectSum('total_amount')
                    ->where("DATE_FORMAT(bill_date, '%Y-%m')", $monthDay)
                    ->whereNotIn('status', ['Void', 'Draft'])
                    ->get()->getRow()->total_amount ?? 0;
                $purchaseTrend[] = (float) $pTotal;
            }
        } elseif ($filter === '6m' || $filter === '12m') {
            $numMonths = ($filter === '12m') ? 11 : 5;
            for ($i = $numMonths; $i >= 0; $i--) {
                $monthDay = date('Y-m', strtotime("-$i months"));
                $months[] = date('M Y', strtotime("-$i months"));

                $sTotal = $invoiceModel->selectSum('total_amount')
                    ->where("DATE_FORMAT(invoice_date, '%Y-%m')", $monthDay)
                    ->whereNotIn('status', ['Void', 'Draft'])
                    ->get()->getRow()->total_amount ?? 0;
                $salesTrend[] = (float) $sTotal;

                $pTotal = $billModel->selectSum('total_amount')
                    ->where("DATE_FORMAT(bill_date, '%Y-%m')", $monthDay)
                    ->whereNotIn('status', ['Void', 'Draft'])
                    ->get()->getRow()->total_amount ?? 0;
                $purchaseTrend[] = (float) $pTotal;
            }
        } else {
            // Default: 'current_fy' (12 months: Apr to Mar)
            for ($i = 0; $i < 12; $i++) {
                $loopTime = mktime(0, 0, 0, 4 + $i, 1, $fyStartYear);
                $monthDay = date('Y-m', $loopTime);
                $months[] = date('M Y', $loopTime);

                $sTotal = $invoiceModel->selectSum('total_amount')
                    ->where("DATE_FORMAT(invoice_date, '%Y-%m')", $monthDay)
                    ->whereNotIn('status', ['Void', 'Draft'])
                    ->get()->getRow()->total_amount ?? 0;
                $salesTrend[] = (float) $sTotal;

                $pTotal = $billModel->selectSum('total_amount')
                    ->where("DATE_FORMAT(bill_date, '%Y-%m')", $monthDay)
                    ->whereNotIn('status', ['Void', 'Draft'])
                    ->get()->getRow()->total_amount ?? 0;
                $purchaseTrend[] = (float) $pTotal;
            }
        }

        return $this->response->setJSON([
            'labels' => $months,
            'is_comparison' => false,
            'sales' => $salesTrend,
            'purchases' => $purchaseTrend
        ]);
    }

    public function getTopData()
    {
        $filter = $this->request->getGet('filter') ?? 'current_fy';

        $currentMonth = (int) date('n');
        $currentYear  = (int) date('Y');
        $fyStartYear  = ($currentMonth < 4) ? $currentYear - 1 : $currentYear;

        if ($filter === 'compare_both') {
            $curStart  = "{$fyStartYear}-04-01";
            $curEnd    = ($fyStartYear + 1) . "-03-31";
            $lastStart = ($fyStartYear - 1) . "-04-01";
            $lastEnd   = "{$fyStartYear}-03-31";

            $curProducts   = $this->fetchTopProducts($curStart, $curEnd);
            $lastProducts  = $this->fetchTopProducts($lastStart, $lastEnd);
            $curCustomers  = $this->fetchTopCustomers($curStart, $curEnd);
            $lastCustomers = $this->fetchTopCustomers($lastStart, $lastEnd);

            return $this->response->setJSON([
                'is_comparison' => true,
                'current_fy_label' => "FY {$fyStartYear}-" . substr((string) ($fyStartYear + 1), 2),
                'last_fy_label'    => "FY " . ($fyStartYear - 1) . "-" . substr((string) $fyStartYear, 2),
                'current_products' => $curProducts,
                'last_products'    => $lastProducts,
                'current_customers' => $curCustomers,
                'last_customers'   => $lastCustomers
            ]);
        }

        if ($filter === 'last_fy') {
            $startDate = ($fyStartYear - 1) . "-04-01";
            $endDate   = "{$fyStartYear}-03-31";
        } elseif ($filter === '12m') {
            $startDate = date('Y-m-d', strtotime('-12 months'));
            $endDate   = date('Y-m-d');
        } else {
            // Default: current_fy
            $startDate = "{$fyStartYear}-04-01";
            $endDate   = ($fyStartYear + 1) . "-03-31";
        }

        $products  = $this->fetchTopProducts($startDate, $endDate);
        $customers = $this->fetchTopCustomers($startDate, $endDate);

        return $this->response->setJSON([
            'is_comparison' => false,
            'products'      => $products,
            'customers'     => $customers
        ]);
    }

    protected function fetchTopProducts(string $startDate, string $endDate, int $limit = 5): array
    {
        $db = \Config\Database::connect();
        return $db->table('invoice_items')
            ->select('products.product_name, SUM(invoice_items.quantity) as total_qty, SUM(invoice_items.amount) as total_revenue')
            ->join('invoices', 'invoices.id = invoice_items.invoice_id')
            ->join('products', 'products.id = invoice_items.product_id')
            ->whereNotIn('invoices.status', ['Void', 'Draft'])
            ->where('invoices.invoice_date >=', $startDate)
            ->where('invoices.invoice_date <=', $endDate)
            ->groupBy('invoice_items.product_id')
            ->orderBy('total_revenue', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }

    protected function fetchTopCustomers(string $startDate, string $endDate, int $limit = 5): array
    {
        $db = \Config\Database::connect();
        return $db->table('invoices')
            ->select('customers.name, COUNT(invoices.id) as total_orders, SUM(invoices.total_amount) as total_spent')
            ->join('customers', 'customers.id = invoices.customer_id')
            ->whereNotIn('invoices.status', ['Void', 'Draft'])
            ->where('invoices.invoice_date >=', $startDate)
            ->where('invoices.invoice_date <=', $endDate)
            ->groupBy('invoices.customer_id')
            ->orderBy('total_spent', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }
}
