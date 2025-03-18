<!-- Include head.php -->
<?php include __DIR__ . '/../layouts/head.php'; ?>

<!-- Include layout-vertical.php -->
<?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>


    

        <h1 class="mt-4">Edit Transaction</h1>

        <form action="<?= base_url('/banks/update-statement/' . $statement['id']) ?>" method="post">
            <div class="mb-3">
                <label for="bank_id" class="form-label">Select Bank</label>
                <select class="form-control" name="bank_id" id="bank_id" required>
                    <?php foreach ($banks as $bank): ?>
                        <option value="<?= $bank['id'] ?>" <?= $bank['id'] == $statement['bank_id'] ? 'selected' : '' ?>>
                            <?= $bank['bank_name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="transaction_type" class="form-label">Transaction Type</label>
                    <select class="form-control" name="transaction_type" id="transaction_type" required>
                        <option value="credit" <?= $statement['transaction_type'] == 'credit' ? 'selected' : '' ?>>Credit
                        </option>
                        <option value="debit" <?= $statement['transaction_type'] == 'debit' ? 'selected' : '' ?>>Debit
                        </option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="amount" class="form-label">Amount</label>
                    <input type="text" class="form-control" name="amount" id="amount"
                        value="<?= $statement['amount'] ?>" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="receiver_name" class="form-label">Receiver Name</label>
                    <input type="text" class="form-control" name="receiver_name" id="receiver_name"
                        value="<?= $statement['receiver_name'] ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="reference_no" class="form-label">Reference No</label>
                    <input type="text" class="form-control" name="reference_no" id="reference_no"
                        value="<?= $statement['reference_no'] ?>">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="payment_others_reason" class="form-label">Reason for Payment</label>
                    <select class="form-control" name="payment_others_reason" id="payment_others_reason" required>
                        <option value="invoice" <?= $statement['payment_others_reason'] == 'invoice' ? 'selected' : '' ?>>
                            Invoice</option>
                        <option value="purchase_payment" <?= $statement['payment_others_reason'] == 'purchase_payment' ? 'selected' : '' ?>>Purchase Payment</option>
                        <option value="eb_bill" <?= $statement['payment_others_reason'] == 'eb_bill' ? 'selected' : '' ?>>
                            EB Bill</option>
                        <option value="kolmudhal" <?= $statement['payment_others_reason'] == 'kolmudhal' ? 'selected' : '' ?>>Kolmudhal</option>
                        <option value="stationary" <?= $statement['payment_others_reason'] == 'stationary' ? 'selected' : '' ?>>Stationary</option>
                        <option value="others" <?= $statement['payment_others_reason'] == 'others' ? 'selected' : '' ?>>
                            Others</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="type_of_payment" class="form-label">Type of Payment</label>
                    <input type="text" class="form-control" name="type_of_payment" id="type_of_payment"
                        value="<?= $statement['type_of_payment'] ?>">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="mode_of_payment" class="form-label">Mode of Payment</label>
                    <input type="text" class="form-control" name="mode_of_payment" id="mode_of_payment"
                        value="<?= $statement['mode_of_payment'] ?>">
                </div>
                <div class="col-md-6">
                    <label for="transaction_date" class="form-label">Transaction Date</label>
                    <input type="date" class="form-control" name="transaction_date" id="transaction_date"
                        value="<?= $statement['transaction_date'] ?>" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
            <a href="<?= base_url('/banks/list-statements') ?>" class="btn btn-secondary">Back to List</a>
        </form>


    </div>
</div>

<!-- Include footer.php -->
<?php include __DIR__ . '/../layouts/footer.php'; ?>