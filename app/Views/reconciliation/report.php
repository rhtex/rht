<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>
Reconciliation Report - <?= esc($account['bank_name']) ?>
<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0 text-dark">Reconciliation Report</h1>
    </div>
    <div class="col-sm-6 text-end">
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-print me-1"></i> Print Report
        </button>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary shadow-sm">
    <div class="card-body p-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold mb-1"><?= get_setting('org_name', 'RasiDev HR') ?></h2>
            <h5 class="text-muted">Bank Reconciliation Statement</h5>
            <div class="mt-2">
                <span class="badge bg-light text-dark border p-2">
                    Account: <?= esc($account['bank_name']) ?> (<?= esc($account['account_number']) ?>)
                </span>
                <span class="badge bg-light text-dark border p-2 ms-2">
                    Period: <?= date('d M Y', strtotime($start_date)) ?> to <?= date('d M Y', strtotime($end_date)) ?>
                </span>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <table class="table table-bordered">
                    <thead class="bg-light">
                        <tr>
                            <th colspan="2">Details</th>
                            <th class="text-end" style="width: 200px;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="2" class="fw-bold">Bank Balance as per Books</td>
                            <td class="text-end fw-bold">₹<?= number_format($closing_balance, 2) ?></td>
                        </tr>
                        
                        <tr class="table-info">
                            <td colspan="3"><i class="fas fa-plus-circle me-1"></i> <strong>Add: Unmatched Credit Transactions</strong> (Money in Bank but not in Books)</td>
                        </tr>
                        <?php if (empty($unmatched_credits)): ?>
                            <tr><td colspan="2" class="text-muted ps-4 small italic">Nil</td><td class="text-end italic small">0.00</td></tr>
                        <?php else: ?>
                            <?php foreach ($unmatched_credits as $c): ?>
                                <tr>
                                    <td class="ps-4 small text-muted"><?= date('d-M-y', strtotime($c['transaction_date'])) ?></td>
                                    <td class="small"><?= esc($c['description']) ?></td>
                                    <td class="text-end small">₹<?= number_format($c['amount'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <tr class="table-info">
                            <td colspan="3"><i class="fas fa-minus-circle me-1"></i> <strong>Less: Unmatched Debit Transactions</strong> (Money out of Bank but not in Books)</td>
                        </tr>
                        <?php if (empty($unmatched_debits)): ?>
                            <tr><td colspan="2" class="text-muted ps-4 small italic">Nil</td><td class="text-end italic small">0.00</td></tr>
                        <?php else: ?>
                            <?php foreach ($unmatched_debits as $d): ?>
                                <tr>
                                    <td class="ps-4 small text-muted"><?= date('d-M-y', strtotime($d['transaction_date'])) ?></td>
                                    <td class="small"><?= esc($d['description']) ?></td>
                                    <td class="text-end small text-danger">(₹<?= number_format($d['amount'], 2) ?>)</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <tr class="bg-light">
                            <td colspan="2" class="fw-bold text-end">Adjusted Bank Balance</td>
                            <td class="text-end fw-bold border-top border-dark">₹<?= number_format($closing_balance + array_sum(array_column($unmatched_credits, 'amount')) - array_sum(array_column($unmatched_debits, 'amount')), 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-md-6">
                <p class="small text-muted mb-0">Generated on: <?= date('d M Y, H:i') ?></p>
            </div>
            <div class="col-md-6 text-end">
                <div style="width: 200px; display: inline-block; border-top: 1px solid #ddd;" class="pt-2 small text-muted">
                    Authorized Signatory
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .app-header, .app-sidebar, .app-footer, .breadcrumb, .btn { display: none !important; }
    .card { border: none !important; box-shadow: none !important; }
    .card-body { padding: 0 !important; }
    body { background: white !important; }
    .content-wrapper { margin-left: 0 !important; }
}
</style>
<?= $this->endSection() ?>
