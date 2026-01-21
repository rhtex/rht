<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Bank Accounts<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Bank Accounts Manager</h1>
    </div>
    <div class="col-sm-6">
        <a href="<?= site_url('bank_accounts/create') ?>" class="btn btn-primary float-sm-end">
            <i class="fas fa-plus"></i> Add New Account
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">List of Bank Accounts</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Bank Name</th>
                        <th>Account Number</th>
                        <th>Branch</th>
                        <th>IFSC Code</th>
                        <th>Current Balance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($accounts)): ?>
                        <?php foreach($accounts as $account): ?>
                        <tr>
                            <td><?= $account['id'] ?></td>
                            <td><?= esc($account['bank_name']) ?></td>
                            <td><?= esc($account['account_number']) ?></td>
                            <td><?= esc($account['branch_name']) ?></td>
                            <td><?= esc($account['ifsc_code']) ?></td>
                            <td><?= number_format($account['current_balance'], 2) ?></td>
                            <td>
                                <span class="badge text-bg-<?= $account['status'] == 'active' ? 'success' : 'danger' ?>">
                                    <?= ucfirst($account['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= site_url('bank_accounts/statement/'.$account['id']) ?>" class="btn btn-sm btn-info" title="Statement">
                                    <i class="fas fa-list"></i>
                                </a>
                                <a href="<?= site_url('bank_accounts/edit/'.$account['id']) ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?= site_url('bank_accounts/delete/'.$account['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No bank accounts found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
