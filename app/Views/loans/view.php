<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>View Loan Details<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>View Loan Details</h1>
    </div>
    <div class="col-sm-6">
        <a href="<?= site_url('loans') ?>" class="btn btn-secondary float-sm-end">Back to List</a>
        <a href="<?= site_url('loans/edit/'.$loan['id']) ?>" class="btn btn-warning float-sm-end me-2"><?= lang("App.edit") ?> Loan</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Loan Details Card -->
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">Loan Information</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <td class="fw-bold">Loan ID:</td>
                        <td><?= $loan['id'] ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Employee:</td>
                        <td><?= $loan['first_name'] . ' ' . $loan['last_name'] ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Mobile:</td>
                        <td><?= $loan['mobile_number'] ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Loan Date:</td>
                        <td><?= date('d M Y', strtotime($loan['loan_date'])) ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Processed Date:</td>
                        <td>
                            <?php if ($loan['processed_date']): ?>
                                <?= date('d M Y', strtotime($loan['processed_date'])) ?>
                            <?php else: ?>
                                <span class="text-muted">Not processed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Credited Date:</td>
                        <td>
                            <?php if ($loan['credited_date']): ?>
                                <?= date('d M Y', strtotime($loan['credited_date'])) ?>
                            <?php else: ?>
                                <span class="text-muted">Not credited</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <td class="fw-bold">Total Loan Amount:</td>
                        <td>₹<?= number_format($loan['loan_amount'], 2) ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Monthly Deduction:</td>
                        <td>₹<?= number_format($loan['monthly_deduction'], 2) ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Remaining Amount:</td>
                        <td class="<?= $loan['remaining_amount'] <= 0 ? 'text-success' : 'text-danger' ?>">
                            ₹<?= number_format($loan['remaining_amount'], 2) ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Amount Paid:</td>
                        <td class="text-success">₹<?= number_format($loan['loan_amount'] - $loan['remaining_amount'], 2) ?></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Status:</td>
                        <td>
                            <?php
                            $statusClass = [
                                'Pending' => 'warning',
                                'Approved' => 'success',
                                'Rejected' => 'danger',
                                'Completed' => 'info'
                            ];
                            $class = $statusClass[$loan['status']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $class ?>"><?= $loan['status'] ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Credit Status:</td>
                        <td>
                            <?php
                            $creditClass = $loan['credit_status'] == 'Credited' ? 'success' : 'warning';
                            ?>
                            <span class="badge bg-<?= $creditClass ?>"><?= $loan['credit_status'] ?></span>
                            <?php if ($loan['credit_status'] == 'Pending'): ?>
                                <br><small class="text-muted">Deductions will start after amount is credited</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Payment History Card -->
<div class="card card-info card-outline mt-3">
    <div class="card-header">
        <h3 class="card-title">Payment History</h3>
    </div>
    <div class="card-body p-0">
        <?php if (!empty($payments)): ?>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Payment Date</th>
                    <th>Payment Month</th>
                    <th>Amount Paid</th>
                    <th>Remaining After Payment</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $index => $payment): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= date('d M Y', strtotime($payment['payment_date'])) ?></td>
                    <td><?= date('M Y', strtotime($payment['payment_month'] . '-01')) ?></td>
                    <td class="text-success">₹<?= number_format($payment['amount_paid'], 2) ?></td>
                    <td>₹<?= number_format($payment['remaining_after_payment'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="table-secondary fw-bold">
                    <td colspan="3">Total Paid</td>
                    <td class="text-success">₹<?= number_format(array_sum(array_column($payments, 'amount_paid')), 2) ?></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        <?php else: ?>
        <div class="p-4 text-center text-muted">
            <i class="fas fa-info-circle fa-3x mb-3"></i>
            <p>No payment history yet. Payments will be recorded when salary is processed.</p>
        </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
