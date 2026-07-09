<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Executive Dashboard<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row align-items-center mb-0">
    <div class="col-sm-6">
        <h3 class="fw-bolder text-uppercase ls-1 mb-0" style="color: #1e293b; letter-spacing: -0.5px;">Analytics <span class="text-primary">Studio</span></h3>
        <p class="text-muted small fw-bold mb-0">Comprehensive Business Overview</p>
    </div>
    <div class="col-sm-6 text-end">
        <div class="btn-group shadow-sm rounded-3 me-2">
            <a href="<?= site_url('invoices/create') ?>" class="btn btn-outline-primary btn-sm fw-bold"><i class="fas fa-plus me-1"></i> Invoice</a>
            <a href="<?= site_url('quotations/create') ?>" class="btn btn-outline-info btn-sm fw-bold"><i class="fas fa-plus me-1"></i> Quote</a>
            <a href="<?= site_url('bills/create') ?>" class="btn btn-outline-warning btn-sm fw-bold"><i class="fas fa-plus me-1"></i> Bill</a>
        </div>
        <div class="btn-group shadow-sm rounded-3">
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
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="dashboard-container">

    <!-- Top Row: Financial KPIs -->
    <div class="row compact-row mb-3">
        <div class="col-xl-3 col-sm-6 mb-2">
            <div class="card kpi-card-v3">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 0.5px;"><?= lang("App.revenue_ytd") ?></div>
                        <div class="kpi-icon-wrap bg-primary bg-opacity-10 text-primary" style="width: 32px; height: 32px; font-size: 0.9rem;">
                            <i class="fas fa-wallet"></i>
                        </div>
                    </div>
                    <h3 class="fw-black mb-1 text-dark" style="font-weight: 800; font-size: 1.25rem;">₹<?= number_format($totalSales / 1000, 1) ?>K</h3>
                    <div class="text-muted small fw-semibold" style="font-size: 0.7rem;"><span class="text-success"><i class="fas fa-arrow-up me-1"></i>FY <?= date('Y') ?></span> <?= lang("App.gross_sales") ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-2">
            <div class="card kpi-card-v3">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 0.5px;"><?= lang("App.net_profit") ?></div>
                        <div class="kpi-icon-wrap bg-success bg-opacity-10 text-success" style="width: 32px; height: 32px; font-size: 0.9rem;">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                    </div>
                    <h3 class="fw-black mb-1 text-dark" style="font-weight: 800; font-size: 1.25rem;">₹<?= number_format($totalProfit / 1000, 1) ?>K</h3>
                    <div class="text-muted small fw-semibold" style="font-size: 0.7rem;"><span class="text-success"><i class="fas fa-check-circle me-1"></i><?= number_format($profitMargin, 1) ?>%</span> <?= lang("App.margin") ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-2">
            <div class="card kpi-card-v3">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 0.5px;"><?= lang("App.ac_receivable") ?></div>
                        <div class="kpi-icon-wrap bg-warning bg-opacity-10 text-warning" style="width: 32px; height: 32px; font-size: 0.9rem;">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                    </div>
                    <h3 class="fw-black mb-1 text-dark" style="font-weight: 800; font-size: 1.25rem;">₹<?= number_format($accountsReceivable / 1000, 1) ?>K</h3>
                    <div class="text-muted small fw-semibold" style="font-size: 0.7rem;"><span class="text-warning"><i class="fas fa-clock me-1"></i>Unpaid</span> <?= lang("App.invoices") ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-2">
            <div class="card kpi-card-v3">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 0.5px;"><?= lang("App.ac_payable") ?></div>
                        <div class="kpi-icon-wrap bg-danger bg-opacity-10 text-danger" style="width: 32px; height: 32px; font-size: 0.9rem;">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                    </div>
                    <h3 class="fw-black mb-1 text-dark" style="font-weight: 800; font-size: 1.25rem;">₹<?= number_format($accountsPayable / 1000, 1) ?>K</h3>
                    <div class="text-muted small fw-semibold" style="font-size: 0.7rem;"><span class="text-danger"><i class="fas fa-exclamation-circle me-1"></i>Unpaid</span> <?= lang("App.bills") ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Second Row: Operational Metrics -->
    <div class="row compact-row mb-3">
        <div class="col-xl-3 col-sm-6 mb-2">
            <div class="metric-box-v2">
                <div class="val text-primary"><?= number_format($pendingQuotations) ?></div>
                <div class="lbl"><?= lang("App.active_quotes") ?></div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-2">
            <div class="metric-box-v2">
                <div class="val text-info"><?= number_format($activeSalesOrders) ?></div>
                <div class="lbl"><?= lang("App.pending_sales_orders") ?></div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-2">
            <div class="metric-box-v2">
                <div class="val text-warning"><?= number_format($pendingReturns) ?></div>
                <div class="lbl"><?= lang("App.pending_returns") ?></div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-2">
            <div class="metric-box-v2">
                <div class="val text-success"><?= number_format($itemsInStock) ?></div>
                <div class="lbl"><?= lang("App.units_in_stock") ?></div>
            </div>
        </div>
    </div>

    <!-- Third Row: Production Metrics -->
    <div class="row compact-row mb-3">
        <div class="col-xl-3 col-sm-6 mb-2">
            <div class="metric-box-v2">
                <div class="val" style="color: #8b5cf6 !important;"><?= number_format($totalWeavers) ?></div>
                <div class="lbl"><?= lang("App.active_weavers") ?></div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-2">
            <div class="metric-box-v2">
                <div class="val" style="color: #3b82f6 !important;"><?= number_format($totalYarnStock) ?> <span style="font-size: 0.8rem; font-weight: 700;">kg</span></div>
                <div class="lbl"><?= lang("App.total_yarn_stock") ?></div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-2">
            <div class="metric-box-v2">
                <div class="val" style="color: #ec4899 !important;"><?= number_format($yarnPurchasedYTD) ?> <span style="font-size: 0.8rem; font-weight: 700;">kg</span></div>
                <div class="lbl"><?= lang("App.yarn_purchased_ytd") ?></div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-2">
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
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span id="chartTitle"><?= lang("App.financial_velocity") ?></span>
                    <div class="d-flex align-items-center gap-3">
                        <select id="chartFilter" class="form-select form-select-sm border-0 fw-bold" style="background-color: rgba(255,255,255,0.1); color: #fff; width: auto; cursor: pointer; border-radius: 8px;">
                            <option value="6m" style="color:#000;"><?= lang("App.last_6_months") ?></option>
                            <option value="12m" style="color:#000;"><?= lang("App.last_12_months") ?></option>
                            <option value="fy" style="color:#000;"><?= lang("App.last_financial_year") ?></option>
                            <option value="compare" style="color:#000;"><?= lang("App.compare_month") ?></option>
                        </select>
                        <div class="d-flex gap-2 text-white">
                            <span class="small d-flex align-items-center"><i class="fas fa-circle me-1" style="color: #6366f1; font-size: 10px;"></i> <?= lang("App.revenue") ?></span>
                            <span class="small d-flex align-items-center"><i class="fas fa-circle me-1" style="color: #f87171; font-size: 10px;"></i> <?= lang("App.expense") ?></span>
                            <span class="small d-flex align-items-center"><i class="fas fa-circle me-1" style="color: #34d399; font-size: 10px;"></i> <?= lang("App.profit") ?></span>
                        </div>
                    </div>
                </div>
                <div class="card-body" style="height: 320px;">
                    <canvas id="mainBarChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card chart-card">
                <div class="card-header text-center"><?= lang("App.revenue_by_category") ?></div>
                <div class="card-body" style="height: 320px; display: flex; align-items: center; justify-content: center;">
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
                <div class="card-header fw-bold">
                    <?= lang("App.top_performing_products") ?>
                </div>
                <div class="card-body px-3">
                    <div class="list-group list-group-flush">
                        <?php foreach ($topProducts as $index => $prod): ?>
                            <div class="list-group-item px-0 d-flex align-items-center border-bottom border-light">
                                <div class="bg-primary bg-opacity-10 rounded text-primary fw-bold text-center me-3" style="width: 32px; height: 32px; line-height: 32px;">
                                    <?= $index + 1 ?>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-dark small text-truncate" style="max-width: 150px;"><?= esc($prod['product_name']) ?></div>
                                    <div class="text-muted" style="font-size: 0.7rem;">Vol: <?= $prod['total_qty'] ?> Units</div>
                                </div>
                                <div class="text-end fw-bolder text-success" style="font-size: 0.85rem;">
                                    ₹<?= number_format($prod['total_revenue'] / 1000, 1) ?>K
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
                <div class="card-header fw-bold">
                    <?= lang("App.top_customers") ?>
                </div>
                <div class="card-body px-3">
                    <div class="list-group list-group-flush">
                        <?php foreach ($topCustomers as $index => $cust): ?>
                            <div class="list-group-item px-0 d-flex align-items-center border-bottom border-light">
                                <div class="bg-info bg-opacity-10 rounded-circle text-info fw-bold text-center me-3" style="width: 32px; height: 32px; line-height: 32px; font-size: 14px;">
                                    <?= substr($cust['name'] ?? 'C', 0, 1) ?>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-dark small text-truncate" style="max-width: 150px;"><?= esc($cust['name']) ?></div>
                                    <div class="text-muted" style="font-size: 0.7rem;">Orders: <?= $cust['total_orders'] ?></div>
                                </div>
                                <div class="text-end fw-bolder text-primary" style="font-size: 0.85rem;">
                                    ₹<?= number_format($cust['total_spent'] / 1000, 1) ?>K
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
                <div class="card-header d-flex justify-content-between align-items-center">
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
                                                <div class="text-truncate" style="max-width: 100px;"><?= esc($inv['customer_name'] ?? 'N/A') ?></div>
                                            </td>
                                            <td class="text-end pe-3 fw-bold">₹<?= number_format($inv['total_amount'], 2) ?></td>
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
                                                <div class="text-truncate" style="max-width: 100px;"><?= esc($bill['vendor_name'] ?? 'N/A') ?></div>
                                            </td>
                                            <td class="text-end pe-3 fw-bold">₹<?= number_format($bill['total_amount'], 2) ?></td>
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
                    { label: 'Revenue', data: <?= json_encode($salesTrend) ?>, backgroundColor: '#4f46e5', borderRadius: 4, barPercentage: 0.6 },
                    { label: 'Expense', data: <?= json_encode($purchaseTrend) ?>, backgroundColor: '#ef4444', borderRadius: 4, barPercentage: 0.6 },
                    { label: 'Profit', data: <?= json_encode($profitTrend) ?>, backgroundColor: '#10b981', borderRadius: 4, barPercentage: 0.6 }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [2, 2], color: '#f1f5f9' }, ticks: { callback: v => '₹' + (v >= 1000 ? (v / 1000) + 'k' : v) } },
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
                    window.mainChart.data.datasets[0].data = data.sales;
                    window.mainChart.data.datasets[1].data = data.purchases;
                    window.mainChart.data.datasets[2].data = data.profit;
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
    });
</script>
<?= $this->endSection() ?>