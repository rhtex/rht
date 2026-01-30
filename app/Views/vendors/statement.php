<?= $this->extend(isset($is_pdf) && $is_pdf ? 'layouts/print' : 'layouts/master') ?>

<?= $this->section('title') ?>Vendor Statement - <?= esc($vendor['name']) ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<?php if (!isset($is_pdf) || !$is_pdf): ?>
<div class="row align-items-center mb-4 no-print">
    <div class="col-sm-6">
        <h1 class="m-0 text-dark fw-bold">Vendor Statement</h1>
        <p class="text-muted mb-0"><?= esc($vendor['name']) ?></p>
    </div>
    <div class="col-sm-6 text-end">
        <div class="btn-group">
            <a href="<?= current_url() . '?' . http_build_query(array_merge($_GET, ['format' => 'pdf'])) ?>" class="btn btn-danger shadow-sm">
                <i class="fas fa-file-pdf me-1"></i> PDF
            </a>
            <a href="<?= current_url() . '?' . http_build_query(array_merge($_GET, ['format' => 'csv'])) ?>" class="btn btn-success shadow-sm">
                <i class="fas fa-file-excel me-1"></i> Excel
            </a>
            <button onclick="window.print()" class="btn btn-primary shadow-sm">
                <i class="fas fa-print me-1"></i> Print
            </button>
            <a href="<?= site_url('vendors/view/'.$vendor['id']) ?>" class="btn btn-secondary shadow-sm">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>
</div>
<?php endif; ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php if (!isset($is_pdf) || !$is_pdf): ?>
<div class="card border-0 shadow-sm mb-4 no-print">
    <div class="card-body">
        <form action="<?= current_url() ?>" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-bold">From Date</label>
                <input type="date" name="date_from" class="form-control" value="<?= esc($date_from) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">To Date</label>
                <input type="date" name="date_to" class="form-control" value="<?= esc($date_to) ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i> Filter Statement
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="statement-container bg-white p-5 shadow-sm rounded-4">
    <!-- Statement Header -->
    <div class="statement-header mb-5">
        <table style="width: 100%; border-collapse: collapse; border: none;">
            <tr>
                <td style="width: 50%; vertical-align: top; border: none;">
                    <h2 class="fw-bold text-primary mb-1" style="margin: 0; padding: 0;">VENDOR STATEMENT</h2>
                    <p class="text-muted" style="margin: 0; padding: 0;">Account Summary</p>
                    
                    <div style="margin-top: 20px;">
                        <h6 class="text-uppercase text-muted small fw-bold mb-2">Vendor Details:</h6>
                        <h4 class="fw-bold mb-1" style="margin: 0; padding: 0;"><?= esc($vendor['name']) ?></h4>
                        <?php if ($billing_address): ?>
                            <p class="text-muted mb-0">
                                <?= esc($billing_address['address_line1']) ?><br>
                                <?= $billing_address['address_line2'] ? esc($billing_address['address_line2']) . '<br>' : '' ?>
                                <?= esc($billing_address['city']) ?>, <?= $billing_state ? esc($billing_state['name']) : '' ?> - <?= esc($billing_address['pincode']) ?>
                            </p>
                        <?php endif; ?>
                        <p class="text-muted mt-2">
                            <i class="fas fa-phone-alt me-1 small"></i> <?= esc($vendor['phone']) ?>
                        </p>
                    </div>
                </td>
                <td style="width: 50%; vertical-align: top; text-align: right; border: none;">
                    <h4 class="fw-bold mb-4" style="margin: 0; padding: 0;">RASI DESIGNS</h4>
                    <div style="margin-top: 20px;">
                        <div class="mb-2">
                            <span class="text-muted small text-uppercase">Statement Period:</span><br>
                            <span class="fw-bold text-dark">
                                <?= $date_from ? date('d M, Y', strtotime($date_from)) : 'Beginning' ?> - 
                                <?= $date_to ? date('d M, Y', strtotime($date_to)) : date('d M, Y') ?>
                            </span>
                        </div>
                        <div>
                            <span class="text-muted small text-uppercase">Net Balance Payable:</span><br>
                            <?php
                                // Calculate final balance
                                // Vendor Liability: Cr (Opening Cr + Bills) - Dr (Opening Dr + Payments + Credits)
                                $totalDebit = ($opening_type == 'Dr' ? $opening_balance : 0);
                                $totalCredit = ($opening_type == 'Cr' ? $opening_balance : 0);
                                foreach($transactions as $t) {
                                    $totalDebit += $t['debit'];
                                    $totalCredit += $t['credit'];
                                }
                                $finalBalance = $totalCredit - $totalDebit; // Net amount we owe them
                            ?>
                            <h3 style="margin: 0; padding: 0;" class="fw-bold <?= $finalBalance >= 0 ? 'text-danger' : 'text-success' ?>">
                                ₹<?= number_format(abs($finalBalance), 2) ?> 
                                <small style="font-size: 14px; font-weight: normal;"><?= $finalBalance >= 0 ? 'Cr' : 'Dr' ?></small>
                            </h3>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Statement Table -->
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="bg-light">
                <tr>
                    <th class="py-3">Date</th>
                    <th class="py-3">Details</th>
                    <th class="py-3 text-end">Debit (Dr)</th>
                    <th class="py-3 text-end">Credit (Cr)</th>
                    <th class="py-3 text-end">Balance</th>
                </tr>
            </thead>
            <tbody>
                <!-- Opening Balance -->
                <tr class="table-info bg-opacity-10">
                    <td class="fw-bold"><?= $date_from ? date('d M, Y', strtotime($date_from)) : '---' ?></td>
                    <td>
                        <div class="fw-bold"><?= esc($opening_balance_desc) ?></div>
                    </td>
                    <td class="text-end">
                        <?= $opening_type == 'Dr' ? '₹' . number_format($opening_balance, 2) : '' ?>
                    </td>
                    <td class="text-end">
                        <?= $opening_type == 'Cr' ? '₹' . number_format($opening_balance, 2) : '' ?>
                    </td>
                    <td class="text-end fw-bold">
                        ₹<?= number_format($opening_balance, 2) ?> <?= $opening_type ?>
                    </td>
                </tr>

                <?php 
                // Liability balance: Cr increases it, Dr decreases it
                $runningBalance = ($opening_type == 'Cr' ? $opening_balance : -$opening_balance);
                foreach($transactions as $t): 
                    $runningBalance += ($t['credit'] - $t['debit']);
                    $balanceType = $runningBalance >= 0 ? 'Cr' : 'Dr';
                ?>
                <tr>
                    <td><?= date('d M, Y', strtotime($t['date'])) ?></td>
                    <td>
                        <div class="fw-bold"><?= esc($t['type']) ?></div>
                        <small class="text-muted"><?= esc($t['description']) ?></small>
                    </td>
                    <td class="text-end text-success">
                        <?= $t['debit'] > 0 ? '₹' . number_format($t['debit'], 2) : '' ?>
                    </td>
                    <td class="text-end text-danger">
                        <?= $t['credit'] > 0 ? '₹' . number_format($t['credit'], 2) : '' ?>
                    </td>
                    <td class="text-end fw-bold">
                        ₹<?= number_format(abs($runningBalance), 2) ?> <?= $balanceType ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="bg-light fw-bold">
                <tr>
                    <td colspan="2" class="text-end py-3">Totals</td>
                    <td class="text-end py-3 text-success">₹<?= number_format($totalDebit, 2) ?></td>
                    <td class="text-end py-3 text-danger">₹<?= number_format($totalCredit, 2) ?></td>
                    <td class="text-end py-3">
                        ₹<?= number_format(abs($finalBalance), 2) ?> <?= $finalBalance >= 0 ? 'Cr' : 'Dr' ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Footer -->
    <div class="mt-5 pt-5 text-center text-muted small border-top">
        <p>This is a computer-generated statement and does not require a signature.</p>
        <p class="mb-0">© <?= date('Y') ?> Rasi Designs. All Rights Reserved.</p>
    </div>
</div>

<style>
    .statement-container {
        max-width: 1000px;
        margin: 0 auto;
    }
    .table th {
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 1px;
    }
    @media print {
        .no-print {
            display: none !important;
        }
        body {
            background-color: white !important;
        }
        .main-content {
            padding: 0 !important;
            margin: 0 !important;
        }
        .statement-container {
            box-shadow: none !important;
            padding: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }
        .card {
            border: none !important;
        }
        .table {
            border-color: #dee2e6 !important;
        }
    }
</style>
<?= $this->endSection() ?>
