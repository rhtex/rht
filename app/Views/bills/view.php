<?= $this->extend('layouts/master') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark"><?= esc($title) ?></h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('bills') ?>">Purchase Bills</a></li>
                <li class="breadcrumb-item active">View</li>
            </ol>
        </div>
    </div>

    <!-- Bill Header Card -->
    <div class="card card-outline card-primary shadow-sm mb-3">
        <div class="card-header">
            <h3 class="card-title fw-bold"><i class="fas fa-file-invoice-dollar me-1"></i> Bill Information</h3>
            <div class="card-tools d-flex gap-2">
                <?php if ($bill['status'] == 'Draft'): ?>
                    <a href="<?= site_url('bills/mark-open/' . $bill['id']) ?>" class="btn btn-sm btn-success" onclick="return confirm('Mark this bill as Open?');">
                        <i class="fas fa-check-circle me-1"></i> Mark as Open
                    </a>
                <?php endif; ?>
                <?php if ($bill['status'] != 'Void' && $bill['status'] != 'Paid'): ?>
                    <a href="<?= site_url('bills/edit/' . $bill['id']) ?>" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>
                <?php endif; ?>
                <?php if ($bill['balance'] > 0 && $bill['status'] != 'Void'): ?>
                    <a href="<?= site_url('bills/payment/' . $bill['id']) ?>" class="btn btn-sm btn-success">
                        <i class="fas fa-money-bill-wave me-1"></i> Record Payment
                    </a>
                <?php endif; ?>
                <a href="<?= site_url('bills/print/' . $bill['id']) ?>" target="_blank" class="btn btn-sm btn-info text-white">
                    <i class="fas fa-print me-1"></i> Print
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 border-end">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th width="40%" class="text-muted text-uppercase small">Bill Number:</th><td class="fw-bold fs-6 text-primary"><?= esc($bill['bill_number']) ?></td></tr>
                        <tr><th class="text-muted text-uppercase small">Vendor:</th><td class="fw-bold"><?= esc($bill['vendor_name']) ?></td></tr>
                        <?php if (!empty($bill['vendor_gstin'])): ?>
                            <tr><th class="text-muted text-uppercase small">Vendor GSTIN:</th><td><span class="badge bg-light text-dark border"><?= esc($bill['vendor_gstin']) ?></span></td></tr>
                        <?php endif; ?>
                        <?php if (!empty($bill['vendor_phone'])): ?>
                            <tr><th class="text-muted text-uppercase small">Phone / WA:</th><td><?= esc($bill['vendor_phone']) ?> <?= !empty($bill['vendor_whatsapp']) ? ' / ' . esc($bill['vendor_whatsapp']) : '' ?></td></tr>
                        <?php endif; ?>
                        <?php if (!empty($bill['vendor_city'])): ?>
                            <tr><th class="text-muted text-uppercase small">City / State:</th><td><?= esc($bill['vendor_city']) ?><?= !empty($bill['vendor_state']) ? ', ' . esc($bill['vendor_state']) : '' ?></td></tr>
                        <?php endif; ?>
                        <tr><th class="text-muted text-uppercase small">Vendor Inv / Ref #:</th><td><?= esc($bill['reference_number']) ?: '-' ?></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th width="40%" class="text-muted text-uppercase small">Bill Date:</th><td class="fw-bold"><?= date('d/m/Y', strtotime($bill['bill_date'])) ?></td></tr>
                        <tr><th class="text-muted text-uppercase small">Due Date:</th><td><?= date('d/m/Y', strtotime($bill['due_date'])) ?></td></tr>
                        <tr>
                            <th class="text-muted text-uppercase small">Status:</th>
                            <td>
                                <?php
                                $statusColors = [
                                    'Draft' => 'secondary',
                                    'Open' => 'primary',
                                    'Paid' => 'success',
                                    'Partially Paid' => 'info',
                                    'Overdue' => 'danger',
                                    'Void' => 'dark'
                                ];
                                $color = $statusColors[$bill['status']] ?? 'secondary';
                                ?>
                                <span class="badge text-bg-<?= $color ?> px-2 py-1"><?= $bill['status'] ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted text-uppercase small">Tax Mode:</th>
                            <td>
                                <span class="badge <?= !empty($bill['is_inter_state']) ? 'bg-info' : 'bg-primary' ?>">
                                    <?= !empty($bill['is_inter_state']) ? 'Inter-State (IGST)' : 'Intra-State (CGST + SGST)' ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Line Items Table -->
    <div class="card card-outline card-secondary shadow-sm mb-3">
        <div class="card-header">
            <h3 class="card-title fw-bold"><i class="fas fa-boxes me-1"></i> Line Items</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="4%" class="text-center">#</th>
                            <th width="42%">Description</th>
                            <th width="12%">HSN / SAC</th>
                            <th width="10%" class="text-center">Quantity</th>
                            <th width="12%" class="text-end">Rate (₹)</th>
                            <th width="8%" class="text-center">GST %</th>
                            <th width="12%" class="text-end">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bill['items'] as $index => $item): ?>
                            <tr>
                                <td class="text-center fw-bold text-muted"><?= $index + 1 ?></td>
                                <td><?= esc($item['description']) ?></td>
                                <td><?= esc($item['hsn_code']) ?: '-' ?></td>
                                <td class="text-center fw-bold"><?= $item['quantity'] + 0 ?></td>
                                <td class="text-end">₹<?= number_format($item['rate'], 2) ?></td>
                                <td class="text-center"><span class="badge bg-light text-dark border"><?= $item['tax_percentage'] + 0 ?>%</span></td>
                                <td class="text-end fw-bold">₹<?= number_format($item['amount'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tax Breakdown & Summary Totals -->
    <div class="row">
        <!-- Tax Breakdown by Slab -->
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h6 class="card-title fw-bold mb-0"><i class="fas fa-percent me-1"></i> GST Breakdown By Slab</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-bordered mb-0 align-middle">
                        <thead class="table-light">
                            <tr class="text-center small">
                                <th>Slab</th>
                                <th>Taxable Value</th>
                                <?php if (empty($bill['is_inter_state'])): ?>
                                    <th>CGST</th>
                                    <th>SGST</th>
                                <?php else: ?>
                                    <th>IGST</th>
                                <?php endif; ?>
                                <th>Total Tax</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $subtotal = (float) ($bill['subtotal'] ?? 0);
                            $discAmt = (float) ($bill['discount_amount'] ?? 0);
                            $discType = $bill['discount_type'] ?? 'Amount';
                            $totalDiscount = ($discType == 'Percentage') ? ($subtotal * $discAmt / 100) : $discAmt;
                            $isInterState = !empty($bill['is_inter_state']);

                            $taxSlabs = [];
                            foreach ($bill['items'] as $it) {
                                $taxRate = (float) $it['tax_percentage'];
                                $lineAmt = (float) $it['quantity'] * (float) $it['rate'];
                                $lineDisc = ($subtotal > 0) ? ($lineAmt / $subtotal * $totalDiscount) : 0;
                                $taxable = max(0, $lineAmt - $lineDisc);

                                if (!isset($taxSlabs[$taxRate])) {
                                    $taxSlabs[$taxRate] = ['taxable' => 0, 'tax' => 0];
                                }
                                $taxSlabs[$taxRate]['taxable'] += $taxable;
                                $taxSlabs[$taxRate]['tax'] += ($taxable * $taxRate / 100);
                            }
                            ?>
                            <?php if (empty($taxSlabs)): ?>
                                <tr><td colspan="5" class="text-center text-muted py-2">No taxable items</td></tr>
                            <?php else: ?>
                                <?php foreach ($taxSlabs as $rate => $slabData): ?>
                                    <tr class="text-center">
                                        <td class="fw-bold"><?= $rate ?>%</td>
                                        <td class="text-end">₹<?= number_format($slabData['taxable'], 2) ?></td>
                                        <?php if (!$isInterState): ?>
                                            <td class="text-end">₹<?= number_format($slabData['tax'] / 2, 2) ?> <small class="text-muted">(<?= $rate / 2 ?>%)</small></td>
                                            <td class="text-end">₹<?= number_format($slabData['tax'] / 2, 2) ?> <small class="text-muted">(<?= $rate / 2 ?>%)</small></td>
                                        <?php else: ?>
                                            <td class="text-end">₹<?= number_format($slabData['tax'], 2) ?> <small class="text-muted">(<?= $rate ?>%)</small></td>
                                        <?php endif; ?>
                                        <td class="text-end fw-bold">₹<?= number_format($slabData['tax'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Totals Summary Card -->
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h6 class="card-title fw-bold mb-0"><i class="fas fa-calculator me-1"></i> Amount Summary</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td class="ps-3 py-2 fw-semibold">Sub Total:</td>
                            <td class="text-end pe-3 py-2 fw-bold">₹<?= number_format($bill['subtotal'], 2) ?></td>
                        </tr>
                        <?php if (!empty($bill['discount_amount']) && $bill['discount_amount'] > 0): ?>
                            <tr>
                                <td class="ps-3 py-2 text-danger fw-semibold">Discount (<?= ($bill['discount_type'] ?? 'Amount') == 'Percentage' ? ($bill['discount_amount'] + 0) . '%' : 'Fixed' ?>):</td>
                                <td class="text-end pe-3 py-2 text-danger fw-bold">-₹<?= number_format($totalDiscount, 2) ?></td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <td class="ps-3 py-2 fw-semibold">Total GST:</td>
                            <td class="text-end pe-3 py-2">₹<?= number_format($bill['tax_amount'] ?? 0, 2) ?></td>
                        </tr>
                        <?php if (!empty($bill['shipping_charge']) && $bill['shipping_charge'] > 0): ?>
                            <tr>
                                <td class="ps-3 py-2 fw-semibold">Shipping / Freight:</td>
                                <td class="text-end pe-3 py-2">₹<?= number_format($bill['shipping_charge'], 2) ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if (!empty($bill['roundoff_amount']) && $bill['roundoff_amount'] != 0): ?>
                            <tr>
                                <td class="ps-3 py-2 fw-semibold">Round Off:</td>
                                <td class="text-end pe-3 py-2"><?= $bill['roundoff_amount'] > 0 ? '+' : '' ?>₹<?= number_format($bill['roundoff_amount'], 2) ?></td>
                            </tr>
                        <?php endif; ?>
                        <tr class="table-light border-top border-2">
                            <td class="ps-3 py-2 fw-bold text-uppercase fs-6">Grand Total:</td>
                            <td class="text-end pe-3 py-2 fw-bolder fs-5 text-primary">₹<?= number_format($bill['total_amount'], 2) ?></td>
                        </tr>
                        <tr>
                            <td class="ps-3 py-2 text-success fw-semibold">Amount Paid:</td>
                            <td class="text-end pe-3 py-2 text-success fw-bold">₹<?= number_format($bill['paid_amount'], 2) ?></td>
                        </tr>
                        <tr class="table-warning border-top">
                            <td class="ps-3 py-2 fw-bold text-uppercase">Balance Due:</td>
                            <td class="text-end pe-3 py-2 fw-bolder text-danger fs-6">₹<?= number_format($bill['balance'], 2) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment History -->
    <?php if (!empty($bill['payments'])): ?>
        <div class="card card-outline card-success shadow-sm mb-3">
            <div class="card-header">
                <h3 class="card-title fw-bold"><i class="fas fa-history me-1"></i> Payment History</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Payment Method</th>
                                <th>Reference #</th>
                                <th>Amount</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bill['payments'] as $payment): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($payment['payment_date'])) ?></td>
                                    <td><span class="badge bg-secondary"><?= esc($payment['payment_method']) ?></span></td>
                                    <td><?= esc($payment['reference_number']) ?: '-' ?></td>
                                    <td class="fw-bold text-success">₹<?= number_format($payment['amount'], 2) ?></td>
                                    <td class="text-muted"><?= esc($payment['notes']) ?: '-' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Notes & Terms -->
    <?php if (!empty($bill['notes']) || !empty($bill['terms'])): ?>
        <div class="row">
            <?php if (!empty($bill['notes'])): ?>
                <div class="col-md-6 mb-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-light">
                            <h6 class="card-title fw-bold mb-0"><i class="fas fa-sticky-note me-1"></i> Vendor / Purchase Notes</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0 text-muted" style="white-space: pre-wrap;"><?= esc($bill['notes']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (!empty($bill['terms'])): ?>
                <div class="col-md-6 mb-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-light">
                            <h6 class="card-title fw-bold mb-0"><i class="fas fa-gavel me-1"></i> Payment & Purchase Terms</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0 text-muted" style="white-space: pre-wrap;"><?= esc($bill['terms']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>
<?= $this->endSection() ?>
