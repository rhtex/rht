<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Bank Accounts<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-university text-primary me-2"></i>Bank Accounts & Treasury</h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex gap-2 align-items-center">
            <a href="<?= site_url('bank_accounts') ?>" class="btn btn-sm btn-outline-secondary" title="Refresh List">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </a>
            <?php if (can('bank_account.create')): ?>
            <a href="<?= site_url('bank_accounts/create') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Add New Account
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
        <a href="<?= site_url('bank_accounts') ?>" class="bt-stat bt-g-blue text-decoration-none <?= empty($filters['status']) && empty($filters['account_type']) ? 'active' : '' ?>">
            <i class="fas fa-university bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['total'] ?? count($accounts)) ?></div>
            <div class="bt-stat-label">Total Accounts</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-emerald" style="cursor:default;">
            <i class="fas fa-coins bt-stat-icon"></i>
            <div class="bt-stat-num">₹<?= number_format($stats['total_balance'] ?? 0, 2) ?></div>
            <div class="bt-stat-label">Total Current Liquidity</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('bank_accounts?account_type=Bank') ?>" class="bt-stat bt-g-purple text-decoration-none <?= ($filters['account_type'] ?? '') === 'Bank' ? 'active' : '' ?>">
            <i class="fas fa-landmark bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['bank_count'] ?? 0) ?></div>
            <div class="bt-stat-label">Bank Accounts</div>
            <div class="progress"><div class="progress-bar" style="width:<?= (!empty($stats['total']) && $stats['total'] > 0) ? min(100, round(($stats['bank_count'] / $stats['total']) * 100)) : 0 ?>%"></div></div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('bank_accounts?account_type=Cash') ?>" class="bt-stat bt-g-amber text-decoration-none <?= ($filters['account_type'] ?? '') === 'Cash' ? 'active' : '' ?>">
            <i class="fas fa-money-bill-wave bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['cash_count'] ?? 0) ?></div>
            <div class="bt-stat-label">Cash Registers</div>
            <div class="progress"><div class="progress-bar" style="width:<?= (!empty($stats['total']) && $stats['total'] > 0) ? min(100, round(($stats['cash_count'] / $stats['total']) * 100)) : 0 ?>%"></div></div>
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card card-outline card-primary mb-3 shadow-sm">
    <div class="card-header py-2">
        <h3 class="card-title fw-bold"><i class="fas fa-filter me-1"></i> Filter Accounts</h3>
    </div>
    <div class="card-body">
        <form action="<?= site_url('bank_accounts') ?>" method="get" class="row g-2 align-items-end">
            <div class="col-md-5 col-sm-12">
                <label for="search" class="form-label">Search</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Search by bank name, account #, or branch..." value="<?= esc($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-2 col-sm-4">
                <label for="account_type" class="form-label">Type</label>
                <select name="account_type" id="account_type" class="form-select">
                    <option value="">All Types</option>
                    <option value="Bank" <?= ($filters['account_type'] ?? '') === 'Bank' ? 'selected' : '' ?>>Bank</option>
                    <option value="Cash" <?= ($filters['account_type'] ?? '') === 'Cash' ? 'selected' : '' ?>>Cash</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-4">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-3 col-sm-4 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100" title="Apply Filter"><i class="fas fa-search"></i> Search</button>
                <a href="<?= site_url('bank_accounts') ?>" class="btn btn-outline-secondary" title="Reset Filters"><i class="fas fa-undo"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Bank Accounts Table -->
<div class="card card-outline card-secondary shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th style="min-width: 180px;">Bank / Name</th>
                        <th style="min-width: 90px;">Type</th>
                        <th style="min-width: 140px;">Account Number</th>
                        <th style="min-width: 130px;">Branch</th>
                        <th style="min-width: 120px;">IFSC Code</th>
                        <th class="text-end" style="min-width: 130px;">Current Balance</th>
                        <th class="text-center" style="min-width: 90px;">Status</th>
                        <th class="text-end" style="min-width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($accounts)): ?>
                        <?php foreach($accounts as $account): ?>
                        <tr>
                            <td class="text-muted fw-semibold">#<?= $account['id'] ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary-subtle text-primary fw-bold me-2 d-flex align-items-center justify-content-center" style="width:34px;height:34px;min-width:34px;">
                                        <i class="fas <?= ($account['account_type'] ?? 'Bank') === 'Cash' ? 'fa-wallet' : 'fa-landmark' ?> small"></i>
                                    </div>
                                    <span class="fw-bold text-dark"><?= esc($account['bank_name']) ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="badge text-bg-<?= ($account['account_type'] ?? 'Bank') === 'Cash' ? 'warning' : 'info' ?> px-2 py-1">
                                    <?= esc($account['account_type'] ?? 'Bank') ?>
                                </span>
                            </td>
                            <td><code class="text-dark fw-semibold"><?= esc($account['account_number']) ?></code></td>
                            <td><?= esc($account['branch_name'] ?: '-') ?></td>
                            <td>
                                <?php if (($account['account_type'] ?? 'Bank') === 'Cash'): ?>
                                    <span class="text-muted">-</span>
                                <?php else: ?>
                                    <code class="text-secondary"><?= esc($account['ifsc_code']) ?></code>
                                <?php endif; ?>
                            </td>
                            <td class="text-end fw-bold <?= ($account['current_balance'] >= 0) ? 'text-success' : 'text-danger' ?>">
                                ₹<?= number_format($account['current_balance'], 2) ?>
                            </td>
                            <td class="text-center">
                                <span class="badge text-bg-<?= $account['status'] == 'active' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($account['status']) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= site_url('bank_accounts/statement/'.$account['id']) ?>" class="btn btn-outline-primary" title="View Statement">
                                        <i class="fas fa-list"></i>
                                    </a>
                                    <?php if (can('bank_account.edit')): ?>
                                    <a href="<?= site_url('bank_accounts/edit/'.$account['id']) ?>" class="btn btn-outline-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (can('bank_account.delete')): ?>
                                    <a href="<?= site_url('bank_accounts/delete/'.$account['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this account?')" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fas fa-university fa-2x text-muted mb-2 d-block"></i>
                                No bank accounts found matching the criteria.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
