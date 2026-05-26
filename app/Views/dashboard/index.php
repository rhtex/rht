<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Executive Dashboard<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row align-items-center mb-0">
    <div class="col-sm-6">
        <h3 class="fw-bolder text-uppercase ls-1 mb-0" style="color: #1e293b; letter-spacing: -0.5px;">Analytics <span
                class="text-primary">Studio</span></h3>
        <p class="text-muted small fw-bold mb-0">Dynamic Pulse of Your Business</p>
    </div>
    <div class="col-sm-6 text-end">
        <div class="btn-group shadow-sm rounded-3">
            <button class="btn btn-white btn-sm fw-bold border-0 px-3"><i class="fas fa-sync-alt me-1 text-primary"></i>
                Live</button>
            <button class="btn btn-primary btn-sm fw-bold border-0 px-3"><?= date('M d, Y') ?></button>
        </div>
    </div>
</div>

<style>
    :root {
        --glass-bg: rgba(255, 255, 255, 0.95);
        --accent-1: #4f46e5;
        --accent-2: #10b981;
        --accent-3: #f59e0b;
        --accent-4: #06b6d4;
    }

    .dashboard-container {
        padding: 10px;
    }

    .compact-row {
        margin-left: -5px;
        margin-right: -5px;
    }

    .compact-row>[class*="col-"] {
        padding-left: 5px;
        padding-right: 5px;
    }

    .stat-card-v2 {
        border: none;
        border-radius: 12px;
        color: white;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        cursor: pointer;
    }

    .text-zoho {
        color: white;
    }

    .stat-card-v2:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .stat-card-v2 .card-body {
        padding: 18px;
        z-index: 2;
        position: relative;
    }

    .stat-card-v2 .label {
        font-size: 0.75rem;
        font-weight: 700;
        text-uppercase: uppercase;
        opacity: 0.85;
        letter-spacing: 0.5px;
    }

    .stat-card-v2 .value {
        font-size: 1.6rem;
        font-weight: 900;
        margin: 4px 0;
    }

    .stat-card-v2 .meta {
        font-size: 0.7rem;
        font-weight: 600;
        background: rgba(0, 0, 0, 0.1);
        display: inline-block;
        padding: 2px 8px;
        border-radius: 20px;
    }

    .bg-gradient-vibrant-1 {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    }

    .bg-gradient-vibrant-2 {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .bg-gradient-vibrant-3 {
        background: linear-gradient(135deg, #06b6d4 0%, #2563eb 100%);
    }

    .bg-gradient-vibrant-4 {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }

    .chart-card {
        background: white;
        border-radius: 15px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        margin-bottom: 10px;
    }

    .chart-card .card-header {
        background: transparent;
        border-bottom: none;
        padding: 15px 20px 0;
        font-weight: 700;
        color: #334155;
        font-size: 0.95rem;
    }

    .activity-feed-card {
        background: #1e293b;
        border-radius: 15px;
        color: white;
        height: 100%;
    }

    .custom-table {
        font-size: 0.82rem;
    }

    .custom-table th {
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.7rem;
        border-bottom: 2px solid #f1f5f9 !important;
    }

    .custom-table td {
        font-weight: 600;
        color: #334155;
        padding: 10px 8px;
    }

    .progress-thin {
        height: 6px;
        border-radius: 10px;
        background: #f1f5f9;
    }

    .badge-soft {
        font-weight: 700;
        font-size: 0.7rem;
        padding: 4px 10px;
        border-radius: 6px;
    }

    .text-success-vibrant {
        color: #10b981;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="dashboard-container">


    <!-- Pulse Row -->
    <div class="row compact-row mb-2">
        <div class="col-xl-3 col-sm-6 mb-2">
            <div class="card stat-card-v2 bg-gradient-vibrant-1">
                <div class="card-body">
                    <div class="label">Revenue Performance</div>
                    <div class="value">₹<?= number_format($totalSales / 1000, 1) ?>K</div>
                    <div class="meta"><i class="fas fa-arrow-up me-1"></i> FY <?= date('Y') ?> Overview</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-2">
            <div class="card stat-card-v2 bg-gradient-vibrant-2">
                <div class="card-body">
                    <div class="label">Net Profitability</div>
                    <div class="value">₹<?= number_format($totalProfit / 1000, 1) ?>K</div>
                    <div class="meta"><?= number_format($profitMargin, 1) ?>% Net Margin</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-2">
            <div class="card stat-card-v2 bg-gradient-vibrant-3">
                <div class="card-body">
                    <div class="label">Asset Valuation</div>
                    <div class="value"><?= number_format($itemsInStock) ?> <span style="font-size: 0.8rem">Units</span>
                    </div>
                    <div class="meta">Inventory in Hand</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-2">
            <div class="card stat-card-v2 bg-gradient-vibrant-4">
                <div class="card-body">
                    <div class="label">Outstanding Comm.</div>
                    <div class="value">₹<?= number_format($pendingCommissions / 1000, 1) ?>K</div>
                    <div class="meta">Sales Force Support</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Analytics Row -->
    <div class="row compact-row mb-2">
        <div class="col-xl-8">
            <div class="card chart-card">
                <div class="card-header d-flex justify-content-between">
                    <span>OPERATIONAL VELOCITY</span>
                    <div class="d-flex gap-2">
                        <span class="small opacity-75 d-flex align-items-center"><i class="fas fa-circle me-1"
                                style="color: #4f46e5; font-size: 8px;"></i> Revenue</span>
                        <span class="small opacity-75 d-flex align-items-center"><i class="fas fa-circle me-1"
                                style="color: #f59e0b; font-size: 8px;"></i> Expense</span>
                        <span class="small opacity-75 d-flex align-items-center"><i class="fas fa-circle me-1"
                                style="color: #10b981; font-size: 8px;"></i> Profit</span>
                    </div>
                </div>
                <div class="card-body" style="height: 300px;">
                    <canvas id="mainBarChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card chart-card">
                <div class="card-header text-center">CATEGORY DEPARTMENTAL REACH</div>
                <div class="card-body" style="height: 300px;">
                    <canvas id="categoryBarChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Data Grid -->
    <div class="row compact-row">
        <!-- Top Products List -->
        <div class="col-xl-4 col-md-6 mb-2">
            <div class="card activity-feed-card border-0 shadow-lg"
                style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
                <div class="card-body">
                    <h6 class="fw-bold mb-3 text-white-50 small text-uppercase ls-1">Elite Product Leaderboard</h6>
                    <div class="list-group list-group-flush bg-transparent">
                        <?php foreach ($topProducts as $index => $prod): ?>
                            <div class="list-group-item bg-transparent border-white border-opacity-10 px-0 d-flex align-items-center animate__animated animate__fadeInUp"
                                style="animation-delay: <?= $index * 0.1 ?>s">
                                <div class="stats-icon bg-info bg-opacity-10 rounded-3 p-2 me-3 text-info">
                                    <i
                                        class="fas fa-<?= ['crown', 'medal', 'award', 'star', 'rocket'][$index] ?? 'bolt' ?> fa-sm"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-white small text-truncate" style="max-width: 140px;">
                                        <?= esc($prod['product_name']) ?>
                                    </div>
                                    <div class="text-white-50 smaller">Vol: <?= $prod['total_qty'] ?> Units</div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-info small">
                                        ₹<?= number_format($prod['total_revenue'] / 1000, 1) ?>K</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card chart-card border-0">
            <div class="card-header px-3 d-flex justify-content-between align-items-center">
                <span>RECENT ACTIVITY</span>
                <ul class="nav nav-pills nav-sm" id="recentTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active py-1 px-3" style="font-size: 0.75rem;" id="invoices-tab"
                            data-bs-toggle="tab" data-bs-target="#invoices" type="button" role="tab"
                            aria-selected="true">Invoices</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-1 px-3" style="font-size: 0.75rem;" id="bills-tab"
                            data-bs-toggle="tab" data-bs-target="#bills" type="button" role="tab"
                            aria-selected="false">Bills</button>
                    </li>
                </ul>
            </div>
            <div class="card-body p-0">
                <div class="tab-content" id="recentTabContent">
                    <!-- Invoices Tab -->
                    <div class="tab-pane fade show active" id="invoices" role="tabpanel" aria-labelledby="invoices-tab">
                        <div class="table-responsive">
                            <table class="table table-hover custom-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Ref #</th>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Status</th>
                                        <th class="text-end">Amount</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($recentInvoices)): ?>
                                        <?php foreach ($recentInvoices as $inv): ?>
                                            <tr>
                                                <td>
                                                    <span class="fw-bold text-primary"><?= esc($inv['invoice_number']) ?></span>
                                                </td>
                                                <td class="text-muted"><?= date('d M', strtotime($inv['invoice_date'])) ?>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sx bg-soft-info me-2 rounded-circle text-center fw-bold"
                                                            style="width: 20px; height: 20px; line-height: 20px; font-size: 9px;">
                                                            <?= substr($inv['customer_name'] ?? 'C', 0, 1) ?>
                                                        </div>
                                                        <span class="text-truncate"
                                                            style="max-width: 100px;"><?= esc($inv['customer_name'] ?? 'N/A') ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php
                                                    $statusClass = match ($inv['status']) {
                                                        'Paid' => 'success',
                                                        'Partially Paid' => 'warning',
                                                        'Draft' => 'secondary',
                                                        'Overdue' => 'danger',
                                                        default => 'primary'
                                                    };
                                                    ?>
                                                    <span
                                                        class="badge-soft bg-<?= $statusClass ?> text-<?= $statusClass ?>"><?= $inv['status'] ?></span>
                                                </td>
                                                <td class="text-end fw-bold">
                                                    ₹<?= number_format($inv['total_amount'], 2) ?>
                                                </td>
                                                <td class="text-end">
                                                    <a href="<?= site_url('invoices/view/' . $inv['id']) ?>"
                                                        class="btn btn-sm btn-white text-muted p-1"><i
                                                            class="fas fa-eye"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-3 text-muted">No recent invoices found.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Bills Tab -->
                    <div class="tab-pane fade" id="bills" role="tabpanel" aria-labelledby="bills-tab">
                        <div class="table-responsive">
                            <table class="table table-hover custom-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Ref #</th>
                                        <th>Date</th>
                                        <th>Vendor</th>
                                        <th>Status</th>
                                        <th class="text-end">Amount</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($recentBills)): ?>
                                        <?php foreach ($recentBills as $bill): ?>
                                            <tr>
                                                <td>
                                                    <span class="fw-bold text-primary"><?= esc($bill['bill_number']) ?></span>
                                                </td>
                                                <td class="text-muted"><?= date('d M', strtotime($bill['bill_date'])) ?>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sx bg-soft-warning me-2 rounded-circle text-center fw-bold"
                                                            style="width: 20px; height: 20px; line-height: 20px; font-size: 9px;">
                                                            <?= substr($bill['vendor_name'] ?? 'V', 0, 1) ?>
                                                        </div>
                                                        <span class="text-truncate"
                                                            style="max-width: 100px;"><?= esc($bill['vendor_name'] ?? 'N/A') ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php
                                                    $statusClass = match ($bill['status']) {
                                                        'Paid' => 'success',
                                                        'Partially Paid' => 'warning',
                                                        'Draft' => 'secondary',
                                                        'Overdue' => 'danger',
                                                        default => 'primary'
                                                    };
                                                    ?>
                                                    <span
                                                        class="badge-soft bg-<?= $statusClass ?> text-<?= $statusClass ?>"><?= $bill['status'] ?></span>
                                                </td>
                                                <td class="text-end fw-bold">
                                                    ₹<?= number_format($bill['total_amount'], 2) ?>
                                                </td>
                                                <td class="text-end">
                                                    <a href="<?= site_url('bills/view/' . $bill['id']) ?>"
                                                        class="btn btn-sm btn-white text-muted p-1"><i
                                                            class="fas fa-eye"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-3 text-muted">No recent bills found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary & Team -->
        <div class="col-xl-3 col-md-12 mb-2">
            <div class="card chart-card border-0" style="background: #f8fafc;">
                <div class="card-body p-3">
                    <div class="row g-2 mt-2">
                        <div class="col-6">
                            <div
                                class="p-3 bg-white rounded-4 text-center shadow-sm border-bottom border-3 border-info">
                                <div class="fw-black h5 mb-0" style="font-weight: 900;"><?= $employeeCount ?></div>
                                <div class="smaller fw-bold text-muted text-uppercase">Force</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div
                                class="p-3 bg-white rounded-4 text-center shadow-sm border-bottom border-3 border-primary">
                                <div class="fw-black h5 mb-0" style="font-weight: 900;"><?= $userCount ?></div>
                                <div class="smaller fw-bold text-muted text-uppercase">Users</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 text-center">
                        <div class="badge-soft bg-success text-zoho d-inline-block">
                            <i class="fas fa-shield-alt me-1"></i> Zoho Synchronized
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
        // Shared Configuration
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.font.weight = '600';
        Chart.defaults.color = '#64748b';

        // Main Multi-Bar Chart
        const mainCtx = document.getElementById('mainBarChart').getContext('2d');
        new Chart(mainCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [
                    {
                        label: 'Revenue',
                        data: <?= json_encode($salesTrend) ?>,
                        backgroundColor: '#4f46e5',
                        borderRadius: 4,
                        maxBarThickness: 30
                    },
                    {
                        label: 'Expense',
                        data: <?= json_encode($purchaseTrend) ?>,
                        backgroundColor: '#f59e0b',
                        borderRadius: 4,
                        maxBarThickness: 30
                    },
                    {
                        label: 'Profit',
                        data: <?= json_encode($profitTrend) ?>,
                        backgroundColor: '#10b981',
                        borderRadius: 4,
                        maxBarThickness: 30
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: '#1e293b',
                        padding: 10,
                        titleFont: { size: 12, weight: 'bold' },
                        bodyFont: { size: 11 }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [2, 2], color: '#f1f5f9' },
                        ticks: {
                            callback: function (v) { return '₹' + (v >= 1000 ? (v / 1000) + 'k' : v); },
                            font: { size: 10 }
                        }
                    },
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                }
            }
        });

        // Category Horizontal Bar Chart
        const catCtx = document.getElementById('categoryBarChart').getContext('2d');
        new Chart(catCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($categorySales, 'label')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($categorySales, 'value')) ?>,
                    backgroundColor: ['#4f46e5', '#10b981', '#f59e0b', '#06b6d4', '#ec4899'],
                    borderRadius: 4,
                    barThickness: 20
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { display: false }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { font: { size: 10, weight: 'bold' }, color: '#334155' }
                    }
                }
            }
        });
    });
</script>
<?= $this->endSection() ?>