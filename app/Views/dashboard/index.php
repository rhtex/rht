<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Executive Dashboard<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row align-items-center mb-0">
    <div class="col-12 col-sm-6">
        <h3 class="fw-bolder text-uppercase ls-1 mb-0" style="color: #1e293b; letter-spacing: -0.5px;">Analytics <span class="text-primary">Studio</span></h3>
        <p class="text-muted small fw-bold mb-0">Comprehensive Business Overview</p>
    </div>
    <div class="col-12 col-sm-6 d-flex flex-wrap gap-2 justify-content-sm-end mt-2 mt-sm-0">
        <div class="btn-group shadow-sm rounded-3">
            <?php if (can('invoice.create')): ?>
            <a href="<?= site_url('invoices/create') ?>" class="btn btn-outline-primary btn-sm fw-bold"><i class="fas fa-plus me-1"></i> Invoice</a>
            <?php endif; ?>
            <?php if (can('quotation.create')): ?>
            <a href="<?= site_url('quotations/create') ?>" class="btn btn-outline-info btn-sm fw-bold"><i class="fas fa-plus me-1"></i> Quote</a>
            <?php endif; ?>
            <?php if (can('bill.create')): ?>
            <a href="<?= site_url('bills/create') ?>" class="btn btn-outline-warning btn-sm fw-bold"><i class="fas fa-plus me-1"></i> Bill</a>
            <?php endif; ?>
        </div>
        <div class="btn-group shadow-sm rounded-3 d-none d-sm-inline-flex">
            <button class="btn btn-white btn-sm fw-bold border-0 px-3"><i class="fas fa-sync-alt me-1 text-primary"></i> Live</button>
            <button class="btn btn-primary btn-sm fw-bold border-0 px-3"><?= date('M d, Y') ?></button>
        </div>
    </div>
</div>

<style>
    :root {
        --glass-bg: rgba(255, 255, 255, 0.95);
    }

    .dashboard-container { padding: 8px; background: #f8fafc; border-radius: 12px; }
    .compact-row { margin-left: -6px; margin-right: -6px; }
    .compact-row > [class*="col-"] { padding-left: 6px; padding-right: 6px; }

    .kpi-card-v3 {
        background: #ffffff;
        border: 1px solid rgba(0,0,0,0.03);
        border-radius: 16px;
        box-shadow: 0 4px 15px -5px rgba(0,0,0,0.06);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s ease;
    }
    .kpi-card-v3:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px -10px rgba(0,0,0,0.12);
    }
    .kpi-icon-wrap {
        width: 40px; height: 40px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem;
    }

    .metric-box-v2 {
        background: linear-gradient(145deg, #ffffff, #f8fafc);
        border-radius: 16px; 
        border: 1px solid #f1f5f9;
        padding: 12px; 
        text-align: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        transition: all 0.3s;
    }
    .metric-box-v2:hover {
        background: #ffffff;
        box-shadow: 0 8px 20px -5px rgba(0,0,0,0.08);
        transform: translateY(-2px);
    }
    .metric-box-v2 .val { font-size: 1.25rem; font-weight: 800; letter-spacing: -0.5px; }
    .metric-box-v2 .lbl { font-size: 0.65rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-top: 4px; letter-spacing: 0.5px; }

    .chart-card {
        background: white; border-radius: 16px; border: 1px solid #f1f5f9;
        box-shadow: 0 4px 15px -5px rgba(0,0,0,0.05); margin-bottom: 12px;
    }
    .chart-card .card-header {
        background: #1e293b; border-bottom: none; padding: 10px 14px;
        font-weight: 700; color: #ffffff; font-size: 0.9rem;
        border-top-left-radius: 16px; border-top-right-radius: 16px;
    }

    .list-card { height: 100%; border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: 0 4px 15px -5px rgba(0,0,0,0.05); }
    .list-card .card-header {
        background: #1e293b !important; border-bottom: none !important; padding: 10px 14px !important;
        color: #ffffff !important; font-size: 0.9rem;
        border-top-left-radius: 16px; border-top-right-radius: 16px;
    }
    .custom-table { font-size: 0.75rem; }
    .custom-table th { color: #94a3b8; font-weight: 700; text-transform: uppercase; font-size: 0.65rem; border-bottom: 2px solid #f1f5f9 !important; letter-spacing: 0.5px; }
    .custom-table td { font-weight: 600; color: #334155; padding: 8px 8px; vertical-align: middle; border-bottom: 1px solid #f8fafc; }
    .badge-soft { font-weight: 700; font-size: 0.65rem; padding: 4px 8px; border-radius: 6px; }
    .chart-body-wrap { height: 320px; }
    .list-card-body-min { min-height: 310px; }
    @media (max-width: 991.98px) {
        .dashboard-container { padding: 2px; border-radius: 8px; }
        .chart-body-wrap { height: 230px !important; }
        .list-card-body-min { min-height: auto !important; }
        .metric-box-v2 { padding: 10px 8px; border-radius: 12px; }
        .metric-box-v2 .lbl { font-size: 0.72rem !important; }
        .custom-table { font-size: 0.85rem !important; }
        .custom-table td { padding: 8px 6px; }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="dashboard-container">

    <!-- Top Row: Financial KPIs -->
    <div class="row compact-row mb-3">
        <div class="col-xl-3 col-sm-6 mb-2 d-flex">
            <div class="card kpi-card-v3 flex-fill">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <div class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 0.5px;">Total Sales</div>
                                <div class="text-secondary fw-semibold" style="font-size: 0.7rem;">Gross Sales</div>
                            </div>
                            <div class="kpi-icon-wrap bg-primary bg-opacity-10 text-primary" style="width: 32px; height: 32px; font-size: 0.9rem;">
                                <i class="fas fa-wallet"></i>
                            </div>
                        </div>
                        <h3 class="fw-black mb-1 text-dark" style="font-weight: 800; font-size: 1.25rem;">₹<?= format_inr($totalSales) ?></h3>
                    </div>
                    <div class="text-muted small fw-semibold" style="font-size: 0.72rem;">
                        <span class="text-success"><i class="fas fa-chart-line me-1"></i>All Time</span> Total Revenue
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-2 d-flex">
            <div class="card kpi-card-v3 flex-fill">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <div class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 0.5px;">Total Purchases</div>
                                <div class="text-secondary fw-semibold" style="font-size: 0.7rem;">Total Purchases</div>
                            </div>
                            <div class="kpi-icon-wrap bg-info bg-opacity-10 text-info" style="width: 32px; height: 32px; font-size: 0.9rem;">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                        <h3 class="fw-black mb-1 text-dark" style="font-weight: 800; font-size: 1.25rem;">₹<?= format_inr($totalPurchases) ?></h3>
                    </div>
                    <div class="text-muted small fw-semibold" style="font-size: 0.72rem;">
                        <span class="text-primary"><i class="fas fa-chart-line me-1"></i>All Time</span> Total Purchases
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-2 d-flex">
            <div class="card kpi-card-v3 flex-fill">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <div class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 0.5px;">Total Receivables</div>
                                <div class="text-secondary fw-semibold" style="font-size: 0.7rem;">Total Unpaid Invoices</div>
                            </div>
                            <div class="kpi-icon-wrap bg-warning bg-opacity-10 text-warning" style="width: 32px; height: 32px; font-size: 0.9rem;">
                                <i class="fas fa-hand-holding-usd"></i>
                            </div>
                        </div>
                        <h3 class="fw-black mb-1 text-dark" style="font-weight: 800; font-size: 1.25rem;">₹<?= format_inr($accountsReceivable) ?></h3>
                    </div>
                    <div class="text-muted small fw-semibold d-flex flex-wrap gap-2" style="font-size: 0.72rem;">
                        <span>Current : <strong class="text-success">₹<?= format_inr($receivableCurrent) ?></strong></span>
                        <span class="text-muted">|</span>
                        <span>Overdue : <strong class="text-danger">₹<?= format_inr($receivableOverdue) ?></strong></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-2 d-flex">
            <div class="card kpi-card-v3 flex-fill">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <div class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 0.5px;">Total Payables</div>
                                <div class="text-secondary fw-semibold" style="font-size: 0.7rem;">Total Unpaid Bills</div>
                            </div>
                            <div class="kpi-icon-wrap bg-danger bg-opacity-10 text-danger" style="width: 32px; height: 32px; font-size: 0.9rem;">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                        </div>
                        <h3 class="fw-black mb-1 text-dark" style="font-weight: 800; font-size: 1.25rem;">₹<?= format_inr($accountsPayable) ?></h3>
                    </div>
                    <div class="text-muted small fw-semibold d-flex flex-wrap gap-2" style="font-size: 0.72rem;">
                        <span>Current : <strong class="text-success">₹<?= format_inr($payableCurrent) ?></strong></span>
                        <span class="text-muted">|</span>
                        <span>Overdue : <strong class="text-danger">₹<?= format_inr($payableOverdue) ?></strong></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Second Row: Operational Metrics -->
    <div class="row compact-row mb-3">
        <div class="col-6 col-xl-3 mb-2">
            <div class="metric-box-v2">
                <div class="val text-primary"><?= number_format($pendingQuotations) ?></div>
                <div class="lbl"><?= lang("App.active_quotes") ?></div>
            </div>
        </div>
        <div class="col-6 col-xl-3 mb-2">
            <div class="metric-box-v2">
                <div class="val text-info"><?= number_format($activeSalesOrders) ?></div>
                <div class="lbl"><?= lang("App.pending_sales_orders") ?></div>
            </div>
        </div>
        <div class="col-6 col-xl-3 mb-2">
            <div class="metric-box-v2">
                <div class="val text-warning"><?= number_format($pendingReturns) ?></div>
                <div class="lbl"><?= lang("App.pending_returns") ?></div>
            </div>
        </div>
        <div class="col-6 col-xl-3 mb-2">
            <div class="metric-box-v2">
                <div class="val text-success"><?= number_format($itemsInStock) ?></div>
                <div class="lbl"><?= lang("App.units_in_stock") ?></div>
            </div>
        </div>
    </div>

    <!-- Third Row: Production Metrics -->
    <div class="row compact-row mb-3">
        <div class="col-6 col-xl-3 mb-2">
            <div class="metric-box-v2">
                <div class="val" style="color: #8b5cf6 !important;"><?= number_format($totalWeavers) ?></div>
                <div class="lbl"><?= lang("App.active_weavers") ?></div>
            </div>
        </div>
        <div class="col-6 col-xl-3 mb-2">
            <div class="metric-box-v2">
                <div class="val" style="color: #3b82f6 !important;"><?= number_format($totalYarnStock) ?> <span style="font-size: 0.8rem; font-weight: 700;">kg</span></div>
                <div class="lbl"><?= lang("App.total_yarn_stock") ?></div>
            </div>
        </div>
        <div class="col-6 col-xl-3 mb-2">
            <div class="metric-box-v2">
                <div class="val" style="color: #ec4899 !important;"><?= number_format($yarnPurchasedYTD) ?> <span style="font-size: 0.8rem; font-weight: 700;">kg</span></div>
                <div class="lbl"><?= lang("App.yarn_purchased_ytd") ?></div>
            </div>
        </div>
        <div class="col-6 col-xl-3 mb-2">
            <div class="metric-box-v2">
                <div class="val" style="color: #f59e0b !important;"><?= number_format($pendingWeavingJobs) ?></div>
                <div class="lbl"><?= lang("App.pending_weaving_jobs") ?></div>
            </div>
        </div>
    </div>

    <!-- Main Analytics Row -->
    <div class="row compact-row mb-3">
        <div class="col-xl-8">
            <div class="card chart-card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <span id="chartTitle"><?= lang("App.financial_velocity") ?></span>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <select id="chartFilter" class="form-select form-select-sm border-0 fw-bold" style="background-color: rgba(255,255,255,0.1); color: #fff; width: auto; cursor: pointer; border-radius: 8px;">
                            <option value="current_fy" style="color:#000;" selected><?= lang("App.financial_year") ?></option>
                            <option value="compare_last_year" style="color:#000;"><?= lang("App.compare_last_year") ?></option>
                            <option value="last_fy" style="color:#000;"><?= lang("App.last_financial_year") ?></option>
                            <option value="12m" style="color:#000;"><?= lang("App.last_12_months") ?></option>
                            <option value="6m" style="color:#000;"><?= lang("App.last_6_months") ?></option>
                        </select>
                        <div id="chartLegendNormal" class="d-flex gap-2 text-white">
                            <span class="small d-flex align-items-center"><i class="fas fa-circle me-1" style="color: #15803d; font-size: 10px;"></i> <?= lang("App.revenue") ?></span>
                            <span class="small d-flex align-items-center"><i class="fas fa-circle me-1" style="color: #c2410c; font-size: 10px;"></i> <?= lang("App.expense") ?></span>
                        </div>
                        <div id="chartLegendCompare" class="d-none flex-wrap gap-2 text-white">
                            <span class="small d-flex align-items-center"><i class="fas fa-circle me-1" style="color: #15803d; font-size: 10px;"></i> Rev (Cur FY)</span>
                            <span class="small d-flex align-items-center"><i class="fas fa-circle me-1" style="color: #c2410c; font-size: 10px;"></i> Exp (Cur FY)</span>
                            <span class="small d-flex align-items-center"><i class="fas fa-circle me-1" style="color: #86efac; font-size: 10px;"></i> Rev (Last FY)</span>
                            <span class="small d-flex align-items-center"><i class="fas fa-circle me-1" style="color: #fdba74; font-size: 10px;"></i> Exp (Last FY)</span>
                        </div>
                    </div>
                </div>
                <div class="card-body chart-body-wrap">
                    <canvas id="mainBarChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card chart-card">
                <div class="card-header text-center"><?= lang("App.revenue_by_category") ?></div>
                <div class="card-body chart-body-wrap" style="display: flex; align-items: center; justify-content: center;">
                    <canvas id="categoryDoughnutChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Data Grid -->
    <div class="row compact-row">
        <!-- Top Products -->
        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card list-card border-0">
                <div class="card-header fw-bold d-flex justify-content-between align-items-center">
                    <span style="font-size: 0.85rem;"><?= lang("App.top_performing_products") ?></span>
                    <select id="topProductsFilter" class="form-select form-select-sm border-0 fw-semibold text-white" style="background-color: rgba(255,255,255,0.15); width: auto; font-size: 0.72rem; padding: 2px 24px 2px 8px; cursor: pointer; border-radius: 6px;">
                        <option value="current_fy" style="color:#000;" selected>This FY</option>
                        <option value="last_fy" style="color:#000;">Last FY</option>
                        <option value="12m" style="color:#000;">Last 12M</option>
                        <option value="compare_both" style="color:#000;">Compare Both FY</option>
                    </select>
                </div>
                <div class="card-body px-3 list-card-body-min">
                    <div id="topProductsContainer" class="list-group list-group-flush">
                        <?php foreach ($topProducts as $index => $prod): ?>
                            <div class="list-group-item px-0 d-flex align-items-center gap-2 border-bottom border-light">
                                <div class="bg-primary bg-opacity-10 rounded text-primary fw-bold text-center" style="width: 32px; height: 32px; line-height: 32px; flex: 0 0 auto;">
                                    <?= $index + 1 ?>
                                </div>
                                <div style="min-width: 0; flex: 1 1 auto;">
                                    <div class="fw-bold text-dark small" style="white-space: normal; word-break: break-word;"><?= esc($prod['product_name']) ?></div>
                                    <div class="text-muted small">Vol: <?= number_format($prod['total_qty']) ?> Units</div>
                                </div>
                                <div class="text-end fw-bolder text-success" style="font-size: 0.85rem; flex: 0 0 auto; white-space: nowrap;">
                                    ₹<?= number_format($prod['total_revenue'], 2) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if(empty($topProducts)): ?>
                            <div class="text-center text-muted small py-4">No data available</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- <?= lang("App.top_customers") ?> -->
        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card list-card border-0">
                <div class="card-header fw-bold d-flex justify-content-between align-items-center">
                    <span style="font-size: 0.85rem;"><?= lang("App.top_customers") ?></span>
                    <select id="topCustomersFilter" class="form-select form-select-sm border-0 fw-semibold text-white" style="background-color: rgba(255,255,255,0.15); width: auto; font-size: 0.72rem; padding: 2px 24px 2px 8px; cursor: pointer; border-radius: 6px;">
                        <option value="current_fy" style="color:#000;" selected>This FY</option>
                        <option value="last_fy" style="color:#000;">Last FY</option>
                        <option value="12m" style="color:#000;">Last 12M</option>
                        <option value="compare_both" style="color:#000;">Compare Both FY</option>
                    </select>
                </div>
                <div class="card-body px-3 list-card-body-min">
                    <div id="topCustomersContainer" class="list-group list-group-flush">
                        <?php foreach ($topCustomers as $index => $cust): ?>
                            <div class="list-group-item px-0 d-flex align-items-center gap-2 border-bottom border-light">
                                <div class="bg-info bg-opacity-10 rounded-circle text-info fw-bold text-center" style="width: 32px; height: 32px; line-height: 32px; font-size: 14px; flex: 0 0 auto;">
                                    <?= substr($cust['name'] ?? 'C', 0, 1) ?>
                                </div>
                                <div style="min-width: 0; flex: 1 1 auto;">
                                    <div class="fw-bold text-dark small" style="white-space: normal; word-break: break-word;"><?= esc($cust['name']) ?></div>
                                    <div class="text-muted small">Orders: <?= $cust['total_orders'] ?></div>
                                </div>
                                <div class="text-end fw-bolder text-primary" style="font-size: 0.85rem; flex: 0 0 auto; white-space: nowrap;">
                                    ₹<?= number_format($cust['total_spent'], 2) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if(empty($topCustomers)): ?>
                            <div class="text-center text-muted small py-4">No data available</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- <?= lang("App.recent_activity") ?> -->
        <div class="col-xl-4 col-md-12 mb-3">
            <div class="card list-card border-0">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <span class="fw-bold text-white"><?= lang("App.recent_activity") ?></span>
                    <ul class="nav nav-pills nav-sm" id="recentTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active py-1 px-3" style="font-size: 0.8rem; background: rgba(255,255,255,0.2); color: #fff;" id="inv-tab" data-bs-toggle="tab" data-bs-target="#inv" type="button" role="tab">Invoices</button>
                        </li>
                        <li class="nav-item ms-1" role="presentation">
                            <button class="nav-link py-1 px-3" style="font-size: 0.8rem; color: rgba(255,255,255,0.7);" id="bil-tab" data-bs-toggle="tab" data-bs-target="#bil" type="button" role="tab" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.7)'">Bills</button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <div class="tab-content" id="recentTabContent">
                        <div class="tab-pane fade show active" id="inv" role="tabpanel">
                            <table class="table custom-table mb-0">
                                <tbody>
                                    <?php foreach ($recentInvoices as $inv): ?>
                                        <tr>
                                            <td class="ps-3">
                                                <div class="fw-bold text-primary mb-1"><?= esc($inv['invoice_number']) ?></div>
                                                <div class="text-muted" style="font-size: 0.65rem;"><?= date('d M Y', strtotime($inv['invoice_date'])) ?></div>
                                            </td>
                                            <td>
                                                <div style="white-space: normal; word-break: break-word;"><?= esc($inv['customer_name'] ?? 'N/A') ?></div>
                                            </td>
                                            <td class="text-end pe-3 fw-bold text-nowrap">₹<?= number_format($inv['total_amount'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="tab-pane fade" id="bil" role="tabpanel">
                            <table class="table custom-table mb-0">
                                <tbody>
                                    <?php foreach ($recentBills as $bill): ?>
                                        <tr>
                                            <td class="ps-3">
                                                <div class="fw-bold text-warning mb-1"><?= esc($bill['bill_number']) ?></div>
                                                <div class="text-muted" style="font-size: 0.65rem;"><?= date('d M Y', strtotime($bill['bill_date'])) ?></div>
                                            </td>
                                            <td>
                                                <div style="white-space: normal; word-break: break-word;"><?= esc($bill['vendor_name'] ?? 'N/A') ?></div>
                                            </td>
                                            <td class="text-end pe-3 fw-bold text-nowrap">₹<?= number_format($bill['total_amount'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.font.weight = '600';
        Chart.defaults.color = '#64748b';

        // Main Bar Chart
        const mainCtx = document.getElementById('mainBarChart').getContext('2d');
        window.mainChart = new Chart(mainCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [
                    { label: 'Revenue', data: <?= json_encode($salesTrend) ?>, backgroundColor: '#15803d', borderRadius: 4, barPercentage: 0.85, categoryPercentage: 0.75 },
                    { label: 'Expense', data: <?= json_encode($purchaseTrend) ?>, backgroundColor: '#c2410c', borderRadius: 4, barPercentage: 0.85, categoryPercentage: 0.75 }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ₹' + Number(context.raw).toLocaleString('en-IN');
                            }
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [2, 2], color: '#f1f5f9' }, ticks: { callback: v => '₹' + Number(v).toLocaleString('en-IN') } },
                    x: { grid: { display: false } }
                }
            }
        });

        // Filter Logic
        document.getElementById('chartFilter').addEventListener('change', function() {
            let filter = this.value;
            fetch('<?= site_url('dashboard/chart-data') ?>?filter=' + filter)
                .then(res => res.json())
                .then(data => {
                    window.mainChart.data.labels = data.labels;
                    if (data.is_comparison && data.datasets) {
                        window.mainChart.data.datasets = data.datasets;
                        document.getElementById('chartLegendNormal').classList.remove('d-flex');
                        document.getElementById('chartLegendNormal').classList.add('d-none');
                        document.getElementById('chartLegendCompare').classList.remove('d-none');
                        document.getElementById('chartLegendCompare').classList.add('d-flex');
                    } else {
                        window.mainChart.data.datasets = [
                            { label: 'Revenue', data: data.sales, backgroundColor: '#15803d', borderRadius: 4, barPercentage: 0.85, categoryPercentage: 0.75 },
                            { label: 'Expense', data: data.purchases, backgroundColor: '#c2410c', borderRadius: 4, barPercentage: 0.85, categoryPercentage: 0.75 }
                        ];
                        document.getElementById('chartLegendCompare').classList.remove('d-flex');
                        document.getElementById('chartLegendCompare').classList.add('d-none');
                        document.getElementById('chartLegendNormal').classList.remove('d-none');
                        document.getElementById('chartLegendNormal').classList.add('d-flex');
                    }
                    window.mainChart.update();
                })
                .catch(err => console.error("Error fetching chart data:", err));
        });

        // Category Doughnut Chart
        const catCtx = document.getElementById('categoryDoughnutChart').getContext('2d');
        new Chart(catCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_column($categorySales, 'label')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($categorySales, 'value')) ?>,
                    backgroundColor: ['#4f46e5', '#10b981', '#f59e0b', '#0ea5e9', '#ec4899'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '70%',
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, usePointStyle: true, font: { size: 10 } } }
                }
            }
        });

        // Top Products Filter
        const topProductsFilter = document.getElementById('topProductsFilter');
        if (topProductsFilter) {
            topProductsFilter.addEventListener('change', function() {
                const filter = this.value;
                const container = document.getElementById('topProductsContainer');
                container.innerHTML = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>';

                fetch('<?= site_url('dashboard/top-data') ?>?filter=' + filter)
                    .then(res => res.json())
                    .then(data => {
                        let html = '';
                        if (data.is_comparison) {
                            html += '<div class="fw-bold small text-muted text-uppercase mb-2" style="font-size: 0.68rem;">' + data.current_fy_label + ' (Current)</div>';
                            if (data.current_products && data.current_products.length > 0) {
                                data.current_products.forEach((prod, i) => {
                                    html += renderProductRow(prod, i + 1, 'bg-primary text-primary');
                                });
                            } else {
                                html += '<div class="text-muted small py-1">No data</div>';
                            }

                            html += '<div class="fw-bold small text-muted text-uppercase mt-3 mb-2" style="font-size: 0.68rem;">' + data.last_fy_label + ' (Previous)</div>';
                            if (data.last_products && data.last_products.length > 0) {
                                data.last_products.forEach((prod, i) => {
                                    html += renderProductRow(prod, i + 1, 'bg-secondary text-secondary');
                                });
                            } else {
                                html += '<div class="text-muted small py-1">No data</div>';
                            }
                        } else {
                            if (data.products && data.products.length > 0) {
                                data.products.forEach((prod, i) => {
                                    html += renderProductRow(prod, i + 1, 'bg-primary text-primary');
                                });
                            } else {
                                html = '<div class="text-center text-muted small py-4">No data available</div>';
                            }
                        }
                        container.innerHTML = html;
                    })
                    .catch(err => {
                        console.error('Error fetching top products:', err);
                        container.innerHTML = '<div class="text-center text-danger small py-4">Failed to load data</div>';
                    });
            });
        }

        // Top Customers Filter
        const topCustomersFilter = document.getElementById('topCustomersFilter');
        if (topCustomersFilter) {
            topCustomersFilter.addEventListener('change', function() {
                const filter = this.value;
                const container = document.getElementById('topCustomersContainer');
                container.innerHTML = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-info"></div></div>';

                fetch('<?= site_url('dashboard/top-data') ?>?filter=' + filter)
                    .then(res => res.json())
                    .then(data => {
                        let html = '';
                        if (data.is_comparison) {
                            html += '<div class="fw-bold small text-muted text-uppercase mb-2" style="font-size: 0.68rem;">' + data.current_fy_label + ' (Current)</div>';
                            if (data.current_customers && data.current_customers.length > 0) {
                                data.current_customers.forEach((cust, i) => {
                                    html += renderCustomerRow(cust, 'bg-info text-info');
                                });
                            } else {
                                html += '<div class="text-muted small py-1">No data</div>';
                            }

                            html += '<div class="fw-bold small text-muted text-uppercase mt-3 mb-2" style="font-size: 0.68rem;">' + data.last_fy_label + ' (Previous)</div>';
                            if (data.last_customers && data.last_customers.length > 0) {
                                data.last_customers.forEach((cust, i) => {
                                    html += renderCustomerRow(cust, 'bg-secondary text-secondary');
                                });
                            } else {
                                html += '<div class="text-muted small py-1">No data</div>';
                            }
                        } else {
                            if (data.customers && data.customers.length > 0) {
                                data.customers.forEach((cust, i) => {
                                    html += renderCustomerRow(cust, 'bg-info text-info');
                                });
                            } else {
                                html = '<div class="text-center text-muted small py-4">No data available</div>';
                            }
                        }
                        container.innerHTML = html;
                    })
                    .catch(err => {
                        console.error('Error fetching top customers:', err);
                        container.innerHTML = '<div class="text-center text-danger small py-4">Failed to load data</div>';
                    });
            });
        }

        function renderProductRow(prod, rank, badgeClass) {
            return `
                <div class="list-group-item px-0 d-flex align-items-center gap-2 border-bottom border-light">
                    <div class="${badgeClass} bg-opacity-10 rounded fw-bold text-center" style="width: 32px; height: 32px; line-height: 32px; flex: 0 0 auto;">
                        ${rank}
                    </div>
                    <div style="min-width: 0; flex: 1 1 auto;">
                        <div class="fw-bold text-dark small" style="white-space: normal; word-break: break-word;">${escapeHtml(prod.product_name)}</div>
                        <div class="text-muted small">Vol: ${Number(prod.total_qty).toLocaleString()} Units</div>
                    </div>
                    <div class="text-end fw-bolder text-success" style="font-size: 0.85rem; flex: 0 0 auto; white-space: nowrap;">
                        ₹${Number(prod.total_revenue).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                    </div>
                </div>
            `;
        }

        function renderCustomerRow(cust, badgeClass) {
            const initial = (cust.name || 'C').charAt(0).toUpperCase();
            return `
                <div class="list-group-item px-0 d-flex align-items-center gap-2 border-bottom border-light">
                    <div class="${badgeClass} bg-opacity-10 rounded-circle fw-bold text-center" style="width: 32px; height: 32px; line-height: 32px; font-size: 14px; flex: 0 0 auto;">
                        ${initial}
                    </div>
                    <div style="min-width: 0; flex: 1 1 auto;">
                        <div class="fw-bold text-dark small" style="white-space: normal; word-break: break-word;">${escapeHtml(cust.name)}</div>
                        <div class="text-muted small">Orders: ${cust.total_orders}</div>
                    </div>
                    <div class="text-end fw-bolder text-primary" style="font-size: 0.85rem; flex: 0 0 auto; white-space: nowrap;">
                        ₹${Number(cust.total_spent).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                    </div>
                </div>
            `;
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }
    });
</script>
<?= $this->endSection() ?>