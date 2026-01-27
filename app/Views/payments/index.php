<?= $this->extend('layouts/master') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark"><?= $title ?></h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                <li class="breadcrumb-item active">Payments</li>
            </ol>
        </div>
    </div>

    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title">All Payments</h3>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Payment #</th>
                        <th>Date</th>
                        <th>Vendor</th>
                        <th>Bill #</th>
                        <th>Mode</th>
                        <th class="text-right">Paid Amount</th>
                        <th class="text-right">Deductions</th>
                        <th class="text-right">Total Settlement</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($payments)): ?>
                        <?php foreach ($payments as $payment): ?>
                            <?php 
                            $deductions = ($payment['discount_amount'] ?? 0) + ($payment['mahimai_amount'] ?? 0) + ($payment['postal_charges'] ?? 0);
                            $settlement = $payment['amount'] + $deductions;
                            ?>
                            <tr>
                                <td><?= esc($payment['payment_number']) ?></td>
                                <td><?= date('d/m/Y', strtotime($payment['payment_date'])) ?></td>
                                <td><?= esc($payment['vendor_name']) ?></td>
                                <td>
                                    <a href="<?= site_url('bills/view/' . $payment['bill_id']) ?>">
                                        <?= esc($payment['bill_number']) ?>
                                    </a>
                                </td>
                                <td><?= esc($payment['payment_mode']) ?></td>
                                <td class="text-right">₹<?= number_format($payment['amount'], 2) ?></td>
                                <td class="text-right text-danger">
                                    <?php if ($deductions > 0): ?>
                                        ₹<?= number_format($deductions, 2) ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td class="text-right fw-bold">₹<?= number_format($settlement, 2) ?></td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="<?= site_url('payments/view/' . $payment['id']) ?>" class="btn btn-sm btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= site_url('payments/edit/' . $payment['id']) ?>" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deletePayment(<?= $payment['id'] ?>)" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">No payments found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<form id="deleteForm" action="" method="post" style="display: none;">
    <?= csrf_field() ?>
</form>

<script>
function deletePayment(id) {
    if (confirm('Are you sure you want to delete this payment? This will increase the bill balance accordingly.')) {
        const form = document.getElementById('deleteForm');
        form.action = '<?= site_url('payments/delete/') ?>' + id;
        form.submit();
    }
}
</script>

<?= $this->endSection() ?>
