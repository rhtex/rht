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
                <li class="breadcrumb-item"><a href="<?= base_url('agent-payments') ?>">Agent Payments</a></li>
                <li class="breadcrumb-item active">View</li>
            </ol>
        </div>
    </div>

    <!-- Payment Information -->
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Payment Information</h3>
            <div class="card-tools">
                <a href="<?= site_url('agent-payments/delete/' . $payment['id']) ?>" 
                   class="btn btn-sm btn-danger"
                   onclick="return confirm('Are you sure you want to delete this payment? This will mark all invoices as unpaid again.')">
                    <i class="fas fa-trash"></i> Delete
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr><th width="40%">Payment Number:</th><td class="fw-bold"><?= esc($payment['payment_number']) ?></td></tr>
                        <tr><th>Agent Name:</th><td><?= esc($payment['agent_name']) ?></td></tr>
                        <tr><th>Phone:</th><td><?= esc($payment['phone_number']) ?></td></tr>
                        <tr><th>Payment Date:</th><td><?= date('d/m/Y', strtotime($payment['payment_date'])) ?></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr><th width="40%">Payment Mode:</th><td><span class="badge text-bg-info"><?= $payment['payment_mode'] ?></span></td></tr>
                        <?php if ($payment['account_name']): ?>
                        <tr><th>Bank Account:</th><td><?= esc($payment['account_name']) ?> (<?= esc($payment['account_number']) ?>)</td></tr>
                        <?php endif; ?>
                        <tr><th>Reference Number:</th><td><?= esc($payment['reference_number']) ?: '-' ?></td></tr>
                        <tr><th>Total Amount:</th><td class="fw-bold fs-5 text-success">₹<?= number_format($payment['amount'], 2) ?></td></tr>
                    </table>
                </div>
            </div>
            <?php if ($payment['notes']): ?>
            <div class="row mt-2">
                <div class="col-md-12">
                    <strong>Notes:</strong>
                    <p class="mb-0"><?= nl2br(esc($payment['notes'])) ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Invoices Paid -->
    <div class="card card-outline card-secondary">
        <div class="card-header">
            <h3 class="card-title">Invoices Paid</h3>
        </div>
        <div class="card-body">
            <div class="alert alert-light border mb-3">
                <small class="text-muted"><i class="fas fa-info-circle"></i> Note: Commission is calculated on the subtotal amount (excluding taxes and extra charges).</small>
            </div>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="table-light">
                        <tr>
                            <th width="25%">Invoice # & Customer</th>
                            <th>Invoice Date</th>
                            <th class="text-end">Subtotal</th>
                            <th class="text-end">Tax Amt</th>
                            <th class="text-end">Inv. Total</th>
                            <th class="text-end">Commission Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($payment['items'])): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No invoices linked</td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $totalCommission = 0;
                            foreach ($payment['items'] as $item): 
                                $totalCommission += $item['commission_amount'];
                            ?>
                                <tr>
                                    <td>
                                        <a href="<?= site_url('invoices/view/' . $item['invoice_id']) ?>" target="_blank" class="fw-bold">
                                            <?= esc($item['invoice_number']) ?> <i class="fas fa-external-link-alt fa-xs"></i>
                                        </a><br>
                                        <small class="text-muted"><?= esc($item['customer_name']) ?></small>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($item['invoice_date'])) ?></td>
                                    <td class="text-end">₹<?= number_format($item['subtotal'], 2) ?></td>
                                    <td class="text-end">₹<?= number_format($item['tax_amount'], 2) ?></td>
                                    <td class="text-end">₹<?= number_format($item['invoice_total'], 2) ?></td>
                                    <td class="text-end fw-bold">₹<?= number_format($item['commission_amount'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="table-secondary">
                                <td colspan="5" class="text-end fw-bold">Total Commission Paid:</td>
                                <td class="text-end fw-bold fs-5">₹<?= number_format($totalCommission, 2) ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
