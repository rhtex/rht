<!-- Include head.php -->
<?php include __DIR__ . '/../layouts/head.php'; ?>

<!-- Include layout-vertical.php -->
<?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>


    
        <h1 class="mt-4">View Transaction</h1>
        <p><strong>Bank Name:</strong>
            <?= $statement['bank_name'] ?>
        </p>
        <p><strong>Transaction Type:</strong>
            <?= $statement['transaction_type'] ?>
        </p>
        <p><strong>Amount:</strong>
            <?= $statement['amount'] ?>
        </p>
        <p><strong>Receiver Name:</strong>
            <?= $statement['receiver_name'] ?>
        </p>
        <p><strong>Reference No:</strong>
            <?= $statement['reference_no'] ?>
        </p>
        <p><strong>Reason for Payment:</strong>
            <?= $statement['payment_others_reason'] ?>
        </p>
        <p><strong>Type of Payment:</strong>
            <?= $statement['type_of_payment'] ?>
        </p>
        <p><strong>Mode of Payment:</strong>
            <?= $statement['mode_of_payment'] ?>
        </p>
        <p><strong>Transaction Date:</strong>
            <?= $statement['transaction_date'] ?>
        </p>
        <p><strong>Added By:</strong>
            <?= $statement['user_name'] ?>
        </p>
        <p><strong>Created At:</strong>
            <?= $statement['created_at'] ?>
        </p>
        <p><strong>Updated At:</strong>
            <?= $statement['updated_at'] ?>
        </p>

        <a href="<?= base_url('/banks/list-statements') ?>" class="btn btn-secondary">Back to List</a>


    </div>
</div>

<!-- Include footer.php -->
<?php include __DIR__ . '/../layouts/footer.php'; ?>