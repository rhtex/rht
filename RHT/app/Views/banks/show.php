<!-- Include head.php -->
<?php include __DIR__ . '/../layouts/head.php'; ?>

<!-- Include layout-vertical.php -->
<?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>


    
        <h1>Bank Details</h1>

        <div class="card">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Bank Name:</strong>
                            <?= $bank['bank_name'] ?>
                        </p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Account Name:</strong>
                            <?= $bank['account_name'] ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Branch:</strong>
                            <?= $bank['branch'] ?>
                        </p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>IFSC Code:</strong>
                            <?= $bank['ifsc_code'] ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Branch Address:</strong>
                            <?= $bank['branch_address'] ?>
                        </p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Account No:</strong>
                            <?= $bank['account_no'] ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Customer ID:</strong>
                            <?= $bank['customer_id'] ?>
                        </p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p style="text-transform: capitalize;"><strong>Status:</strong>
                            <?= $bank['status'] ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <a href="<?= site_url('banks') ?>" class="btn btn-primary">Back to List</a>
    </div>
</div>

<!-- Include footer.php -->
<?php include __DIR__ . '/../layouts/footer.php'; ?>