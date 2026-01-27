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
                <li class="breadcrumb-item"><a href="<?= base_url('invoice_payments') ?>">Customer Receipts</a></li>
                <li class="breadcrumb-item active">View Details</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">Receipt Information</h3>
                    <div class="card-tools">
                        <a href="<?= site_url('invoice_payments/edit/' . $payment['id']) ?>" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="<?= site_url('invoice_payments') ?>" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Receipt Basics</h5>
                            <table class="table table-bordered table-sm">
                                <tr><th width="40%">Receipt #</th><td><?= esc($payment['payment_number']) ?></td></tr>
                                <tr><th>Date</th><td><?= date('d/m/Y', strtotime($payment['payment_date'])) ?></td></tr>
                                <tr><th>Customer</th><td><?= esc($payment['customer_name']) ?></td></tr>
                                <tr><th>Invoice Reference</th><td><a href="<?= site_url('invoices/view/' . $payment['invoice_id']) ?>"><?= esc($payment['invoice_number']) ?></a></td></tr>
                                <tr><th>Mode</th><td><?= esc($payment['payment_mode']) ?></td></tr>
                                <tr><th>Reference #</th><td><?= esc($payment['reference_number']) ?: '-' ?></td></tr>
                            </table>

                            <h5 class="mt-4">Bank Details</h5>
                            <table class="table table-bordered table-sm">
                                <tr><th width="40%">Bank Name</th><td><?= esc($payment['bank_name']) ?: '-' ?></td></tr>
                                <tr><th>Account #</th><td><?= esc($payment['account_number']) ?: '-' ?></td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Financial Breakdown</h5>
                            <table class="table table-bordered">
                                <tr class="table-success">
                                    <th width="50%">Cash Received</th>
                                    <td class="text-right fw-bold">₹<?= number_format($payment['amount'], 2) ?></td>
                                </tr>
                                <tr class="table-danger">
                                    <th>Post-sale Discount</th>
                                    <td class="text-right">₹<?= number_format($payment['discount_amount'], 2) ?></td>
                                </tr>
                                <tr class="table-danger">
                                    <th>Mahimai Amount</th>
                                    <td class="text-right">₹<?= number_format($payment['mahimai_amount'], 2) ?></td>
                                </tr>
                                <tr class="table-danger">
                                    <th>Postal Charges</th>
                                    <td class="text-right">₹<?= number_format($payment['postal_charges'], 2) ?></td>
                                </tr>
                                <tr class="table-primary">
                                    <th class="fs-5">Total Settled</th>
                                    <?php $settlement = $payment['amount'] + $payment['discount_amount'] + $payment['mahimai_amount'] + $payment['postal_charges']; ?>
                                    <td class="text-right fw-bold fs-5">₹<?= number_format($settlement, 2) ?></td>
                                </tr>
                            </table>

                            <?php if ($payment['notes']): ?>
                                <div class="mt-4">
                                    <strong>Notes:</strong><br>
                                    <p class="text-muted"><?= nl2br(esc($payment['notes'])) ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mt-4">
                                <strong>Zoho Status:</strong>
                                <?php if ($payment['zoho_sync_status'] == 'Synced'): ?>
                                    <span class="badge text-bg-success"><i class="fas fa-check"></i> Synced</span>
                                    <small class="d-block text-muted">Zoho ID: <?= $payment['zoho_payment_id'] ?></small>
                                <?php else: ?>
                                    <span class="badge text-bg-warning"><?= $payment['zoho_sync_status'] ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-muted small">
                    Created on: <?= date('d/m/Y H:i', strtotime($payment['created_at'])) ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
