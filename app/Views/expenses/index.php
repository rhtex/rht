<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Expenses<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-receipt text-primary me-2"></i>Expense Management</h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex gap-2 align-items-center">
            <a href="<?= site_url('expenses') ?>" class="btn btn-sm btn-outline-secondary" title="Refresh List">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </a>
            <?php if (can('expense.create')): ?>
            <a href="<?= site_url('expenses/create') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Add New Expense
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Summary Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('expenses') ?>" class="bt-stat bt-g-blue text-decoration-none">
            <i class="fas fa-receipt bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['total'] ?? count($expenses)) ?></div>
            <div class="bt-stat-label">Total Expense Records</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-rose" style="cursor:default;">
            <i class="fas fa-wallet bt-stat-icon"></i>
            <div class="bt-stat-num">₹<?= number_format($stats['total_amount'] ?? 0, 2) ?></div>
            <div class="bt-stat-label">Total Amount Spent</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-amber" style="cursor:default;">
            <i class="fas fa-money-bill-wave bt-stat-icon"></i>
            <div class="bt-stat-num">₹<?= number_format($stats['cash_amount'] ?? 0, 2) ?></div>
            <div class="bt-stat-label">Paid via Cash</div>
            <div class="progress"><div class="progress-bar" style="width:<?= (!empty($stats['total_amount']) && $stats['total_amount'] > 0) ? min(100, round(($stats['cash_amount'] / $stats['total_amount']) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-teal" style="cursor:default;">
            <i class="fas fa-university bt-stat-icon"></i>
            <div class="bt-stat-num">₹<?= number_format($stats['bank_amount'] ?? 0, 2) ?></div>
            <div class="bt-stat-label">Paid via Bank / UPI</div>
            <div class="progress"><div class="progress-bar" style="width:<?= (!empty($stats['total_amount']) && $stats['total_amount'] > 0) ? min(100, round(($stats['bank_amount'] / $stats['total_amount']) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card card-outline card-primary mb-3 shadow-sm">
    <div class="card-header py-2">
        <h3 class="card-title fw-bold"><i class="fas fa-filter me-1"></i> Filter Expenses</h3>
    </div>
    <div class="card-body">
        <form action="<?= site_url('expenses') ?>" method="get" class="row g-2 align-items-end">
            <div class="col-md-3 col-sm-6">
                <label for="category_id" class="form-label">Category</label>
                <select name="category_id" id="category_id" class="form-select select2">
                    <option value="">All Categories</option>
                    <?php foreach($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= (($filters['category_id'] ?? '') == $c['id']) ? 'selected' : '' ?>><?= esc($c['category_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 col-sm-6">
                <label for="bank_account_id" class="form-label">Bank Account</label>
                <select name="bank_account_id" id="bank_account_id" class="form-select select2">
                    <option value="">All Accounts</option>
                    <?php foreach($bank_accounts as $ba): ?>
                        <option value="<?= $ba['id'] ?>" <?= (($filters['bank_account_id'] ?? '') == $ba['id']) ? 'selected' : '' ?>><?= esc($ba['bank_name']) ?> (<?= substr($ba['account_number'], -4) ?>)</option>
                    <?php endforeach; ?>
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
                <a href="<?= site_url('expenses') ?>" class="btn btn-outline-secondary" title="Reset Filters"><i class="fas fa-undo"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Expenses Table -->
<div class="card card-outline card-secondary shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th style="min-width: 110px;">Date</th>
                        <th style="min-width: 160px;">Category</th>
                        <th style="min-width: 200px;">Description</th>
                        <th class="text-end" style="min-width: 120px;">Amount</th>
                        <th class="text-center" style="min-width: 120px;">Payment Mode</th>
                        <th style="min-width: 170px;">Bank Account</th>
                        <th class="text-end" style="min-width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($expenses)): ?>
                        <?php foreach($expenses as $index => $expense): ?>
                        <tr>
                            <td class="text-muted fw-semibold"><?= $index + 1 ?></td>
                            <td><?= date('d/m/Y', strtotime($expense['expense_date'])) ?></td>
                            <td>
                                <span class="badge bg-light text-primary border fw-semibold">
                                    <i class="fas fa-tag me-1 text-primary"></i><?= esc($expense['category_name'] ?? 'N/A') ?>
                                </span>
                            </td>
                            <td><?= esc($expense['description']) ?></td>
                            <td class="text-end fw-bold text-danger">₹<?= number_format($expense['amount'], 2) ?></td>
                            <td class="text-center">
                                <?php
                                $mode = strtolower($expense['payment_mode'] ?? '');
                                $badgeClass = (strpos($mode, 'cash') !== false) ? 'text-bg-warning' : 'text-bg-info';
                                ?>
                                <span class="badge <?= $badgeClass ?> px-2 py-1">
                                    <?= ucfirst($expense['payment_mode']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if(!empty($expense['bank_name'])): ?>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-university text-secondary me-2"></i>
                                        <span><?= esc($expense['bank_name']) ?> <small class="text-muted">(...<?= substr($expense['account_number'], -4) ?>)</small></span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <?php if (can('expense.edit')): ?>
                                    <a href="<?= site_url('expenses/edit/'.$expense['id']) ?>" class="btn btn-outline-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (can('expense.delete')): ?>
                                    <a href="<?= site_url('expenses/delete/'.$expense['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Delete this expense?')" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fas fa-receipt fa-2x text-muted mb-2 d-block"></i>
                                No expenses recorded matching the filter criteria.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
