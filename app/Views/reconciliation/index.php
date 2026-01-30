<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>
Bank Reconciliation
<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0 text-dark">Bank Reconciliation</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
            <li class="breadcrumb-item active">Reconciliation</li>
        </ol>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Bank Accounts</h3>
            </div>
            <div class="card-body">
                <?php if (empty($accounts)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No bank accounts found. 
                        <a href="<?= base_url('bank_accounts/create') ?>">Create a bank account</a> first.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Bank Name</th>
                                    <th>Account Number</th>
                                    <th>Current Balance</th>
                                    <th class="text-center">Unmatched Credits</th>
                                    <th class="text-center">Unmatched Debits</th>
                                    <th class="text-center">Matched</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($accounts as $account): ?>
                                    <tr>
                                        <td><?= esc($account['bank_name']) ?></td>
                                        <td><?= esc($account['account_number']) ?></td>
                                        <td class="fw-bold text-primary">₹<?= number_format($account['current_balance'], 2) ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-warning text-dark">
                                                <?= $account['summary']['unmatched_credits'] ?? 0 ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-warning text-dark">
                                                <?= $account['summary']['unmatched_debits'] ?? 0 ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success">
                                                <?= $account['summary']['matched_transactions'] ?? 0 ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= base_url('reconciliation/account/' . $account['id']) ?>" 
                                               class="btn btn-sm btn-primary">
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
    </div>
</div>
<?= $this->endSection() ?>
