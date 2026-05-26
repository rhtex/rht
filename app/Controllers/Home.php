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
        $returnModel = new \App\Models\ProductItemModel();
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

        // 4. Profit & Margins
        $data['totalProfit'] = $data['totalSales'] - $data['totalPurchases'];
        $data['profitMargin'] = ($data['totalSales'] > 0) ? ($data['totalProfit'] / $data['totalSales']) * 100 : 0;

        // 5. Agent Commissions
        $data['pendingCommissions'] = $invoiceModel->selectSum('agent_commission_amount')
            ->where('agent_commission_status', 'Unpaid')
            ->get()->getRow()->agent_commission_amount ?? 0;

        // 6. Monthly Trend Data (Last 6 Months)
        $months = [];
        $salesTrend = [];
        $purchaseTrend = [];
        $profitTrend = [];
        $returnTrend = [];

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

            // Returns per month
            $returnCount = $returnModel->where("DATE_FORMAT(updated_at, '%Y-%m')", $monthDay)
                ->where('status', 'rejected')
                ->countAllResults();
            $returnTrend[] = $returnCount;
        }

        $data['chartLabels'] = $months;
        $data['salesTrend'] = $salesTrend;
        $data['purchaseTrend'] = $purchaseTrend;
        $data['profitTrend'] = $profitTrend;
        $data['returnTrend'] = $returnTrend;

        // 7. Category Performance (Pie Chart)
        $data['categorySales'] = $db->table('invoice_items')
            ->select('product_categories.category_name as label, SUM(invoice_items.amount) as value')
            ->join('products', 'products.id = invoice_items.product_id')
            ->join('product_categories', 'product_categories.id = products.category_id')
            ->groupBy('products.category_id')
            ->orderBy('value', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        // 8. Top Performance Metrics
        // Top 5 Selling Products
        $data['topProducts'] = $db->table('invoice_items')
            ->select('products.product_name, SUM(invoice_items.quantity) as total_qty, SUM(invoice_items.amount) as total_revenue')
            ->join('products', 'products.id = invoice_items.product_id')
            ->groupBy('invoice_items.product_id')
            ->orderBy('total_revenue', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        // Top 5 Customers
        $data['topCustomers'] = $db->table('invoices')
            ->select('customers.name, COUNT(invoices.id) as total_orders, SUM(invoices.total_amount) as total_spent')
            ->join('customers', 'customers.id = invoices.customer_id')
            ->whereNotIn('invoices.status', ['Void', 'Draft'])
            ->groupBy('invoices.customer_id')
            ->orderBy('total_spent', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        // 7. Recent Lists
        $data['recentInvoices'] = $invoiceModel->getInvoicesWithCustomer();
        $data['recentInvoices'] = array_slice($data['recentInvoices'], 0, 5);

        $data['recentBills'] = $billModel->getBillsWithVendor();
        $data['recentBills'] = array_slice($data['recentBills'], 0, 5);


        return view('dashboard/index', $data);
    }

    public function lang($locale)
    {
        $session = session();
        $session->remove('lang');
        $session->set('lang', $locale);
        return redirect()->back();
    }
}
