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
                <li class="breadcrumb-item active">Customer Receipts</li>
            </ol>
        </div>
    </div>

    <div class="card card-outline card-success mb-3">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter me-1"></i> Filters</h3>
        </div>
        <div class="card-body">
            <form action="<?= site_url('invoice_payments') ?>" method="get" class="row g-3">
                <div class="col-md-3">
                    <label for="customer_id" class="form-label">Customer</label>
                    <select name="customer_id" id="customer_id" class="form-select select2">
                        <option value="">All Customers</option>
                        <?php foreach($customers as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $filters['customer_id'] == $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="payment_mode" class="form-label">Payment Mode</label>
                    <select name="payment_mode" id="payment_mode" class="form-select">
                        <option value="">All Modes</option>
                        <option value="Cash" <?= $filters['payment_mode'] == 'Cash' ? 'selected' : '' ?>>Cash</option>
                        <option value="Bank Transfer" <?= $filters['payment_mode'] == 'Bank Transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                        <option value="Cheque" <?= $filters['payment_mode'] == 'Cheque' ? 'selected' : '' ?>>Cheque</option>
                        <option value="Other" <?= $filters['payment_mode'] == 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" value="<?= $filters['date_from'] ?>">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" value="<?= $filters['date_to'] ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                        <a href="<?= site_url('invoice_payments') ?>" class="btn btn-secondary"><i class="fas fa-undo"></i> Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title">All Receipts</h3>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Receipt #</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Invoice #</th>
                        <th>Mode</th>
                        <th class="text-end">Cash Received</th>
                        <th class="text-end">Deductions</th>
                        <th class="text-end">Total Settlement</th>
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
                                <td><?= esc($payment['customer_name']) ?></td>
                                <td>
                                    <a href="<?= site_url('invoices/view/' . $payment['invoice_id']) ?>">
                                        <?= esc($payment['invoice_number']) ?>
                                    </a>
                                </td>
                                <td><?= esc($payment['payment_mode']) ?></td>
                                <td class="text-end">₹<?= number_format($payment['amount'], 2) ?></td>
                                <td class="text-end text-danger">
                                    <?php if ($deductions > 0): ?>
                                        ₹<?= number_format($deductions, 2) ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td class="text-end fw-bold">₹<?= number_format($settlement, 2) ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= site_url('invoice_payments/view/' . $payment['id']) ?>" class="btn btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= site_url('invoice_payments/edit/' . $payment['id']) ?>" class="btn btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger" onclick="deletePayment(<?= $payment['id'] ?>)" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-4">No receipts found.</td>
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
    if (confirm('Are you sure you want to delete this receipt? This will increase the invoice balance accordingly.')) {
        const form = document.getElementById('deleteForm');
        form.action = '<?= site_url('invoice_payments/delete/') ?>' + id;
        form.submit();
    }
}
</script>

<?= $this->endSection() ?>
