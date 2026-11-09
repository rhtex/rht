<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Sales Returns<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-undo text-primary me-2"></i>Sales Returns (Credit Notes)</h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex gap-2 align-items-center">
            <a href="<?= site_url('sales_returns') ?>" class="btn btn-sm btn-outline-secondary" title="Refresh List">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </a>
            <a href="<?= site_url('sales_returns/create') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Record Return
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$totalReturns = count($returns ?? []);
$totalReturnAmount = 0;
$openReturns = 0;
$closedReturns = 0;

if (!empty($returns)) {
    foreach ($returns as $r) {
        $totalReturnAmount += (float)($r['total_amount'] ?? 0);
        $st = $r['status'] ?? '';
        if ($st === 'Open') $openReturns++;
        elseif ($st === 'Closed') $closedReturns++;
    }
}
?>

<!-- Summary Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('sales_returns') ?>" class="bt-stat bt-g-blue text-decoration-none <?= empty($filters['status']) ? 'active' : '' ?>">
            <i class="fas fa-undo bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($totalReturns) ?></div>
            <div class="bt-stat-label">Total Sales Returns</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-rose" style="cursor:default;">
            <i class="fas fa-coins bt-stat-icon"></i>
            <div class="bt-stat-num">₹<?= number_format($totalReturnAmount, 2) ?></div>
            <div class="bt-stat-label">Total Returned Value</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('sales_returns?status=Open') ?>" class="bt-stat bt-g-amber text-decoration-none <?= ($filters['status'] ?? '') === 'Open' ? 'active' : '' ?>">
            <i class="fas fa-clock bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($openReturns) ?></div>
            <div class="bt-stat-label">Open Credit Notes</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalReturns > 0) ? min(100, round(($openReturns / $totalReturns) * 100)) : 0 ?>%"></div></div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('sales_returns?status=Closed') ?>" class="bt-stat bt-g-green text-decoration-none <?= ($filters['status'] ?? '') === 'Closed' ? 'active' : '' ?>">
            <i class="fas fa-check-circle bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($closedReturns) ?></div>
            <div class="bt-stat-label">Settled / Closed</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalReturns > 0) ? min(100, round(($closedReturns / $totalReturns) * 100)) : 0 ?>%"></div></div>
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card card-outline card-primary mb-3 shadow-sm">
    <div class="card-header py-2">
        <h3 class="card-title fw-bold"><i class="fas fa-filter me-1"></i> Filter Sales Returns</h3>
    </div>
    <div class="card-body">
        <form action="<?= site_url('sales_returns') ?>" method="get" class="row g-2 align-items-end">
            <div class="col-md-3 col-sm-6">
                <label for="customer_id" class="form-label">Customer</label>
                <select name="customer_id" id="customer_id" class="form-select select2">
                    <option value="">All Customers</option>
                    <?php foreach($customers as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= (($filters['customer_id'] ?? '') == $c['id']) ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 col-sm-6">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="Open" <?= (($filters['status'] ?? '') == 'Open') ? 'selected' : '' ?>>Open</option>
                    <option value="Closed" <?= (($filters['status'] ?? '') == 'Closed') ? 'selected' : '' ?>>Closed</option>
                    <option value="Void" <?= (($filters['status'] ?? '') == 'Void') ? 'selected' : '' ?>>Void</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-4">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" name="date_from" id="date_from" class="form-control" value="<?= esc($filters['date_from'] ?? '') ?>">
            </div>
            <div class="col-md-2 col-sm-4">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" name="date_to" id="date_to" class="form-control" value="<?= esc($filters['date_to'] ?? '') ?>">
            </div>
            <div class="col-md-2 col-sm-4 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100" title="Apply Filter"><i class="fas fa-search"></i> Search</button>
                <a href="<?= site_url('sales_returns') ?>" class="btn btn-outline-secondary" title="Reset Filters"><i class="fas fa-undo"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Sales Returns Table -->
<div class="card card-outline card-secondary shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0" id="returnsTable">
                <thead class="table-light">
                    <tr>
                        <th style="min-width: 140px;">Return #</th>
                        <th style="min-width: 110px;">Date</th>
                        <th style="min-width: 190px;">Customer</th>
                        <th style="min-width: 130px;">Invoice #</th>
                        <th class="text-end" style="min-width: 120px;">Amount</th>
                        <th class="text-center" style="min-width: 100px;">Status</th>
                        <th class="text-end" style="min-width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($returns)): ?>
                        <?php foreach($returns as $ret): ?>
                        <tr>
                            <td>
                                <a href="<?= site_url('sales_returns/view/'.$ret['id']) ?>" class="fw-bold text-primary text-decoration-none">
                                    <?= esc($ret['return_number']) ?>
                                </a>
                            </td>
                            <td><?= date('d/m/Y', strtotime($ret['return_date'])) ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary-subtle text-primary fw-bold me-2 d-flex align-items-center justify-content-center" style="width:32px;height:32px;min-width:32px;font-size:0.8rem;">
                                        <?= strtoupper(substr($ret['customer_name'] ?? 'C', 0, 1)) ?>
                                    </div>
                                    <span class="fw-semibold text-dark"><?= esc($ret['customer_name']) ?></span>
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($ret['invoice_id'])): ?>
                                    <a href="<?= site_url('invoices/view/'.$ret['invoice_id']) ?>" class="badge bg-light text-primary border text-decoration-none">
                                        <i class="fas fa-file-invoice me-1"></i><?= esc($ret['invoice_number'] ?: 'Invoice') ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end fw-bold text-danger">₹<?= number_format($ret['total_amount'], 2) ?></td>
                            <td class="text-center">
                                <?php
                                $statusClass = match($ret['status']) {
                                    'Open'   => 'primary',
                                    'Closed' => 'success',
                                    'Void'   => 'dark',
                                    default  => 'secondary'
                                };
                                ?>
                                <span class="badge text-bg-<?= $statusClass ?> px-2 py-1">
                                    <?= esc($ret['status']) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= site_url('sales_returns/view/'.$ret['id']) ?>" class="btn btn-outline-primary" title="View Details"><i class="fas fa-eye"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fas fa-undo fa-2x text-muted mb-2 d-block"></i>
                                No sales returns found matching your filters.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
