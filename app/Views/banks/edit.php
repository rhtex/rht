<!-- Include head.php -->
<?php include __DIR__ . '/../layouts/head.php'; ?>

<!-- Include layout-vertical.php -->
<?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>


    
        <h1>Edit Bank</h1>
        <form action="<?= base_url('/banks/update/' . $bank['id']) ?>" method="post">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="branch" class="form-label">Branch</label>
                    <input type="text" class="form-control" name="branch" id="branch" value="<?= $bank['branch'] ?>"
                        required>
                </div>
                <div class="col-md-6">
                    <label for="ifsc_code" class="form-label">IFSC Code</label>
                    <input type="text" class="form-control" name="ifsc_code" id="ifsc_code"
                        value="<?= $bank['ifsc_code'] ?>" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="branch_address" class="form-label">Branch Address</label>
                    <textarea class="form-control" name="branch_address"
                        id="branch_address"><?= $bank['branch_address'] ?></textarea>
                </div>
                <div class="col-md-6">
                    <label for="account_no" class="form-label">Account No</label>
                    <input type="text" class="form-control" name="account_no" id="account_no"
                        value="<?= $bank['account_no'] ?>" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="account_name" class="form-label">Account Name</label>
                    <input type="text" class="form-control" name="account_name" id="account_name"
                        value="<?= $bank['account_name'] ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="customer_id" class="form-label">Customer ID</label>
                    <input type="text" class="form-control" name="customer_id" id="customer_id"
                        value="<?= $bank['customer_id'] ?>">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-control" name="status" id="status">
                        <option value="active" <?= ($bank['status'] == 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($bank['status'] == 'inactive') ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="bank_name" class="form-label">Bank Name</label>
                    <input type="text" class="form-control" name="bank_name" id="bank_name"
                        value="<?= $bank['bank_name'] ?>" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Update Bank</button>
        </form>
    </div>
</div>

<!-- Include footer.php -->
<?php include __DIR__ . '/../layouts/footer.php'; ?>