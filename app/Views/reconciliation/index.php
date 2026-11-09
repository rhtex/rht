<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Bank Reconciliation<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$totalAccounts = count($accounts ?? []);
$totalBalance = 0;
$totalUnmatched = 0;
$totalMatched = 0;

if (!empty($accounts)) {
    foreach ($accounts as $acc) {
        $totalBalance += (float)($acc['current_balance'] ?? 0);
        $unmatchedCr = (int)($acc['summary']['unmatched_credits'] ?? 0);
        $unmatchedDr = (int)($acc['summary']['unmatched_debits'] ?? 0);
        $totalUnmatched += ($unmatchedCr + $unmatchedDr);
        $totalMatched += (int)($acc['summary']['matched_transactions'] ?? 0);
    }
}
?>

<!-- Hero Header -->
<div class="bt-hero mb-3">
    <div class="row align-items-center g-3">
        <div class="col-md-7">
            <h2 class="h4 mb-1 text-white fw-bold"><i class="fas fa-check-double me-2"></i> Bank Reconciliation</h2>
            <p class="mb-0 text-white-50 small">Match your bank statements against book ledgers, track discrepancies, and maintain accurate books.</p>
        </div>
        <div class="col-md-5 text-md-end">
            <a href="<?= base_url('bank_accounts') ?>" class="btn btn-light btn-sm fw-semibold shadow-sm me-2">
                <i class="fas fa-university me-1 text-primary"></i> Bank Accounts
            </a>
            <a href="<?= base_url('bank_accounts/create') ?>" class="btn btn-warning btn-sm fw-semibold shadow-sm">
                <i class="fas fa-plus me-1"></i> Add Account
            </a>
        </div>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="bt-stat bt-g-blue">
            <div class="bt-stat-title">Connected Accounts</div>
            <div class="bt-stat-val"><?= number_format($totalAccounts) ?></div>
            <div class="small opacity-75 mt-1"><i class="fas fa-building-columns me-1"></i> Active Bank Portfolios</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="bt-stat bt-g-green">
            <div class="bt-stat-title">Total Bank Balance</div>
            <div class="bt-stat-val">₹<?= number_format($totalBalance, 2) ?></div>
            <div class="small opacity-75 mt-1"><i class="fas fa-wallet me-1"></i> Total Ledger Balance</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="bt-stat <?= $totalUnmatched > 0 ? 'bt-g-amber' : 'bt-g-slate' ?>">
            <div class="bt-stat-title">Unmatched Entries</div>
            <div class="bt-stat-val"><?= number_format($totalUnmatched) ?></div>
            <div class="small opacity-75 mt-1"><i class="fas fa-exclamation-triangle me-1"></i> Needs Reconciliation</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="bt-stat bt-g-purple">
            <div class="bt-stat-title">Matched Transactions</div>
            <div class="bt-stat-val"><?= number_format($totalMatched) ?></div>
            <div class="small opacity-75 mt-1"><i class="fas fa-check-circle me-1"></i> Successfully Verified</div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-list me-2 text-primary"></i> Bank Accounts Pending Audit</h6>
        <span class="badge text-bg-primary"><?= $totalAccounts ?> Accounts</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($accounts)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-university fa-3x mb-3 text-secondary opacity-50"></i>
                <p class="mb-2 fw-semibold">No bank accounts registered yet.</p>
                <a href="<?= base_url('bank_accounts/create') ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i> Create First Bank Account
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 list-ui-table">
                    <thead class="table-light text-uppercase small text-muted">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Bank Account</th>
                            <th>Account Number</th>
                            <th>Ledger Balance</th>
                            <th class="text-center">Unmatched Inward (Cr)</th>
                            <th class="text-center">Unmatched Outward (Dr)</th>
                            <th class="text-center">Matched</th>
                            <th class="text-center" style="width: 140px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($accounts as $idx => $account): ?>
                            <?php 
                                $unmatchedCr = (int)($account['summary']['unmatched_credits'] ?? 0);
                                $unmatchedDr = (int)($account['summary']['unmatched_debits'] ?? 0);
                                $matched = (int)($account['summary']['matched_transactions'] ?? 0);
                            ?>
                            <tr>
                                <td class="text-muted fw-bold small"><?= $idx + 1 ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 me-2 d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                            <i class="fas fa-landmark"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark"><?= esc($account['bank_name']) ?></div>
                                            <small class="text-muted"><?= esc($account['branch_name'] ?? 'Main Branch') ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="font-monospace fw-semibold text-secondary">
                                        <?= esc($account['account_number']) ?>
                                    </span>
                                </td>
                                <td>
                                    <strong class="text-primary fs-6">₹<?= number_format($account['current_balance'], 2) ?></strong>
                                </td>
                                <td class="text-center">
                                    <?php if ($unmatchedCr > 0): ?>
                                        <span class="badge text-bg-warning px-2 py-1"><i class="fas fa-arrow-down me-1 text-success"></i><?= $unmatchedCr ?> Credits</span>
                                    <?php else: ?>
                                        <span class="text-muted small">0</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($unmatchedDr > 0): ?>
                                        <span class="badge text-bg-warning px-2 py-1"><i class="fas fa-arrow-up me-1 text-danger"></i><?= $unmatchedDr ?> Debits</span>
                                    <?php else: ?>
                                        <span class="text-muted small">0</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($matched > 0): ?>
                                        <span class="badge text-bg-success px-2 py-1"><i class="fas fa-check-circle me-1"></i><?= $matched ?> Matched</span>
                                    <?php else: ?>
                                        <span class="text-muted small">0</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= base_url('reconciliation/account/' . $account['id']) ?>" 
                                       class="btn btn-sm btn-primary shadow-sm px-3">
                                        <i class="fas fa-check-double me-1"></i> Reconcile
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
