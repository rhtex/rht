<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= isset($expense) ? '<?= lang("App.edit") ?> Expense' : '<?= lang("App.add_new") ?> Expense' ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1><?= isset($expense) ? '<?= lang("App.edit") ?> Expense' : '<?= lang("App.add_new") ?> Expense' ?></h1>
    </div>
    <div class="col-sm-6">
        <a href="<?= site_url('expenses') ?>" class="btn btn-secondary float-sm-end">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title"><?= isset($expense) ? '<?= lang("App.edit") ?> Expense Details' : 'New Expense Details' ?></h3>
    </div>
    <form action="<?= isset($expense) ? site_url('expenses/update/'.$expense['id']) : site_url('expenses/store') ?>" method="post">
        <?= csrf_field() ?>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="expense_date" class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="expense_date" id="expense_date" value="<?= old('expense_date', isset($expense) ? date('Y-m-d', strtotime($expense['expense_date'])) : date('Y-m-d')) ?>" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                    <select name="category_id" id="category_id" class="form-select" required>
                        <option value="">Select Category</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (old('category_id', $expense['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                                <?= esc($cat['category_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" step="0.01" class="form-control" name="amount" id="amount" value="<?= old('amount', $expense['amount'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="payment_mode" class="form-label">Payment Mode <span class="text-danger">*</span></label>
                    <select class="form-select" name="payment_mode" id="payment_mode" required onchange="toggleBankAccount()">
                        <option value="cash" <?= old('payment_mode', $expense['payment_mode'] ?? '') == 'cash' ? 'selected' : '' ?>>Cash</option>
                        <option value="bank" <?= old('payment_mode', $expense['payment_mode'] ?? '') == 'bank' ? 'selected' : '' ?>>Online Transfer / Bank</option>
                        <option value="cheque" <?= old('payment_mode', $expense['payment_mode'] ?? '') == 'cheque' ? 'selected' : '' ?>>Cheque</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3" id="bank_account_div" style="display: none;">
                    <label for="bank_account_id" class="form-label">Bank Account <span class="text-danger">*</span></label>
                    <select class="form-select" name="bank_account_id" id="bank_account_id">
                        <option value="">-- Select Bank Account --</option>
                        <?php if(!empty($bank_accounts)): ?>
                            <?php foreach($bank_accounts as $account): ?>
                                <option value="<?= $account['id'] ?>" <?= old('bank_account_id', $expense['bank_account_id'] ?? '') == $account['id'] ? 'selected' : '' ?>>
                                    <?= $account['bank_name'] ?> - <?= $account['account_number'] ?> (Bal: <?= $account['current_balance'] ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="reference_number" class="form-label">Reference Number</label>
                    <input type="text" class="form-control" name="reference_number" id="reference_number" value="<?= old('reference_number', $expense['reference_number'] ?? '') ?>" placeholder="Transaction ID or Cheque No">
                </div>

                <div class="col-12 mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" name="description" id="description" rows="3"><?= old('description', $expense['description'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> <?= isset($expense) ? 'Update Expense' : 'Save Expense' ?>
            </button>
        </div>
    </form>
</div>

<script>
function toggleBankAccount() {
    const mode = document.getElementById('payment_mode').value;
    const bankDiv = document.getElementById('bank_account_div');
    const bankSelect = document.getElementById('bank_account_id');
    
    if (mode === 'bank' || mode === 'cheque') {
        bankDiv.style.display = 'block';
        bankSelect.setAttribute('required', 'required');
    } else {
        bankDiv.style.display = 'none';
        bankSelect.removeAttribute('required');
        bankSelect.value = '';
    }
}

// Run on load
document.addEventListener('DOMContentLoaded', function() {
    toggleBankAccount();
});
</script>
<?= $this->endSection() ?>
