<!-- Include head.php -->
<?php include __DIR__ . '/../layouts/head.php'; ?>

<!-- Include layout-vertical.php -->
<?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>


    
        <h1>Bank List</h1>
        <a href="<?= site_url('banks/create') ?>" class="btn btn-primary">Create New Bank</a>
        <table id="bankTable" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Bank Name</th>
                    <th>Branch</th>
                    <th>Account No</th>
                    <th>IFSC Code</th>
                    <th>Account Name</th>
                    <th>Customer ID</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($banks as $bank): ?>
                    <tr>
                        <td>
                            <?= $bank['bank_name'] ?>
                        </td>
                        <td>
                            <?= $bank['branch'] ?>
                        </td>
                        <td>
                            <?= $bank['account_no'] ?>
                        </td>
                        <td>
                            <?= $bank['ifsc_code'] ?>
                        </td>
                        <td>
                            <?= $bank['account_name'] ?>
                        </td>
                        <td>
                            <?= $bank['customer_id'] ?>
                        </td>
                        <td style="text-transform:capitalize;">
                            <?= $bank['status'] ?>
                        </td>
                        <td>
                            <a href="<?= base_url('/banks/show/' . $bank['id']) ?>" class="btn btn-primary btn-sm">View</a>
                            <a href="<?= base_url('/banks/edit/' . $bank['id']) ?>"
                                class="btn btn-secondary btn-sm">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Include footer.php -->
<?php include __DIR__ . '/../layouts/footer.php'; ?>