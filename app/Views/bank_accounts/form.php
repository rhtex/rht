<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= isset($account) ? lang('App.edit') . ' Bank Account' : lang('App.add_new') . ' Bank Account' ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1><?= isset($account) ? lang('App.edit') . ' Bank Account' : lang('App.add_new') . ' Bank Account' ?></h1>
    </div>
    <div class="col-sm-6">
        <a href="<?= site_url('bank_accounts') ?>" class="btn btn-secondary float-sm-end">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title"><?= isset($account) ? lang('App.edit') . ' Account Details' : 'New Account Details' ?></h3>
    </div>
    <form action="<?= isset($account) ? site_url('bank_accounts/update/'.$account['id']) : site_url('bank_accounts/store') ?>" method="post">
        <?= csrf_field() ?>
        <div class="card-body">
            <?php if (session()->has('errors')) : ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                    <?php foreach (session('errors') as $error) : ?>
                        <li><?= $error ?></li>
                    <?php endforeach ?>
                    </ul>
                </div>
            <?php endif ?>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="bank_name" class="form-label">Bank Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="bank_name" id="bank_name" value="<?= old('bank_name', $account['bank_name'] ?? '') ?>" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="account_type" class="form-label">Account Type <span class="text-danger">*</span></label>
                    <select class="form-select" name="account_type" id="account_type" required>
                        <option value="Bank" <?= old('account_type', $account['account_type'] ?? 'Bank') === 'Bank' ? 'selected' : '' ?>>Bank</option>
                        <option value="Cash" <?= old('account_type', $account['account_type'] ?? '') === 'Cash' ? 'selected' : '' ?>>Cash</option>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="account_number" class="form-label">Account Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="account_number" id="account_number" value="<?= old('account_number', $account['account_number'] ?? '') ?>" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="branch_name" class="form-label">Branch Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="branch_name" id="branch_name" value="<?= old('branch_name', $account['branch_name'] ?? '') ?>" required>
                </div>

                <div class="col-md-6 mb-3" id="ifsc_code_container">
                    <label for="ifsc_code" class="form-label">IFSC Code <span class="text-danger">*</span></label>
                    <input type="text" class="form-control text-uppercase" name="ifsc_code" id="ifsc_code" maxlength="11" minlength="11" placeholder="e.g. SBIN0001234" value="<?= old('ifsc_code', ($account['ifsc_code'] ?? '') === 'N/A' ? '' : ($account['ifsc_code'] ?? '')) ?>" required>
                    <div class="form-text">Must be exactly 11 alphanumeric characters (Bank accounts only).</div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="current_balance" class="form-label">Current/Opening Balance</label>
                    <input type="number" step="0.01" class="form-control" name="current_balance" id="current_balance" value="<?= old('current_balance', $account['current_balance'] ?? '0.00') ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select class="form-select" name="status" id="status" required>
                        <option value="active" <?= old('status', $account['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= old('status', $account['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> <?= isset($account) ? 'Update Account' : 'Save Account' ?>
            </button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const accountTypeSelect = document.getElementById('account_type');
    const ifscContainer = document.getElementById('ifsc_code_container');
    const ifscInput = document.getElementById('ifsc_code');

    function toggleIfscField() {
        if (!accountTypeSelect || !ifscContainer || !ifscInput) return;

        if (accountTypeSelect.value === 'Cash') {
            ifscContainer.style.display = 'none';
            ifscInput.removeAttribute('required');
            ifscInput.value = '';
        } else {
            ifscContainer.style.display = '';
            ifscInput.setAttribute('required', 'required');
        }
    }

    if (accountTypeSelect) {
        accountTypeSelect.addEventListener('change', toggleIfscField);
        toggleIfscField();
    }

    if (ifscInput) {
        ifscInput.addEventListener('input', function () {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        });
    }
});
</script>
<?= $this->endSection() ?>
