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

        // 3. Financial Totals (Current Year)
        $currentYear = date('Y');
        $data['totalSales'] = $invoiceModel->selectSum('total_amount')
            ->where('status !=', 'Void')
            ->where('status !=', 'Draft')
            ->where('YEAR(invoice_date)', $currentYear)
            ->get()->getRow()->total_amount ?? 0;

        $data['totalPurchases'] = $billModel->selectSum('total_amount')
            ->where('status !=', 'Void')
            ->where('status !=', 'Draft')
            ->where('YEAR(bill_date)', $currentYear)
            ->get()->getRow()->total_amount ?? 0;

        // 4. Accounts Receivable & Payable
        $data['accountsReceivable'] = $invoiceModel->selectSum('total_amount')
            ->whereIn('status', ['Unpaid', 'Partially Paid', 'Overdue'])
            ->get()->getRow()->total_amount ?? 0;

        $data['accountsPayable'] = $billModel->selectSum('total_amount')
            ->whereIn('status', ['Unpaid', 'Partially Paid', 'Overdue'])
            ->get()->getRow()->total_amount ?? 0;

        $data['totalProfit'] = $data['totalSales'] - $data['totalPurchases'];
        $data['profitMargin'] = ($data['totalSales'] > 0) ? ($data['totalProfit'] / $data['totalSales']) * 100 : 0;

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

        // 6. Monthly Trend Data (Last 6 Months)
        $months = [];
        $salesTrend = [];
        $purchaseTrend = [];
        $profitTrend = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthDay = date('Y-m', strtotime("-$i months"));
            $months[] = date('M Y', strtotime("-$i months"));

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

            // Profit per month
            $profitTrend[] = (float) ($sTotal - $pTotal);
        }

        $data['chartLabels'] = $months;
        $data['salesTrend'] = $salesTrend;
        $data['purchaseTrend'] = $purchaseTrend;
        $data['profitTrend'] = $profitTrend;

        // 7. Category Performance
        $data['categorySales'] = $db->table('invoice_items')
            ->select('product_categories.category_name as label, SUM(invoice_items.amount) as value')
            ->join('products', 'products.id = invoice_items.product_id')
            ->join('product_categories', 'product_categories.id = products.category_id')
            ->groupBy('products.category_id')
            ->orderBy('value', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        // 8. Top Products
        $data['topProducts'] = $db->table('invoice_items')
            ->select('products.product_name, SUM(invoice_items.quantity) as total_qty, SUM(invoice_items.amount) as total_revenue')
            ->join('products', 'products.id = invoice_items.product_id')
            ->groupBy('invoice_items.product_id')
            ->orderBy('total_revenue', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        // 9. Top Customers
        $data['topCustomers'] = $db->table('invoices')
            ->select('customers.name, COUNT(invoices.id) as total_orders, SUM(invoices.total_amount) as total_spent')
            ->join('customers', 'customers.id = invoices.customer_id')
            ->whereNotIn('invoices.status', ['Void', 'Draft'])
            ->groupBy('invoices.customer_id')
            ->orderBy('total_spent', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

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
        $filter = $this->request->getGet('filter') ?? '6m';

        $months = [];
        $salesTrend = [];
        $purchaseTrend = [];
        $profitTrend = [];

        if ($filter == '6m' || $filter == '12m') {
            $numMonths = ($filter == '12m') ? 11 : 5;
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

                $profitTrend[] = (float) ($sTotal - $pTotal);
            }
        } elseif ($filter == 'fy') {
            $currentMonth = (int)date('m');
            $currentYear = (int)date('Y');
            
            if ($currentMonth < 4) {
                $startYear = $currentYear - 2;
            } else {
                $startYear = $currentYear - 1;
            }
            
            $start = mktime(0,0,0, 4, 1, $startYear);
            
            for ($i = 0; $i < 12; $i++) {
                $loopTime = strtotime("+$i months", $start);
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

                $profitTrend[] = (float) ($sTotal - $pTotal);
            }
        } elseif ($filter == 'compare') {
            $currentMonthTime = time();
            $lastYearMonthTime = strtotime("-1 year");
            
            $times = [$lastYearMonthTime, $currentMonthTime];
            foreach ($times as $t) {
                $monthDay = date('Y-m', $t);
                $months[] = date('M Y', $t);
                
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

                $profitTrend[] = (float) ($sTotal - $pTotal);
            }
        }

        return $this->response->setJSON([
            'labels' => $months,
            'sales' => $salesTrend,
            'purchases' => $purchaseTrend,
            'profit' => $profitTrend
        ]);
    }
}
