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
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title fw-bold mb-0"><i class="fas fa-file-invoice-dollar me-1"></i> Bill Information</h3>
            <div class="card-tools d-flex gap-2 align-items-center ms-auto">
                <a href="<?= site_url('bills') ?>" class="btn btn-sm btn-light border fw-semibold shadow-sm text-dark" title="Back to Split Bills View">
                    <i class="fas fa-columns me-1 text-primary"></i> Split View
                </a>
                <?php if ($bill['status'] == 'Draft' && can('bill.edit')): ?>
                    <a href="<?= site_url('bills/mark-open/' . $bill['id']) ?>" class="btn btn-sm btn-success" onclick="return confirm('Mark this bill as Open?');">
                        <i class="fas fa-check-circle me-1"></i> Mark as Open
                    </a>
                <?php endif; ?>
                <?php if ($bill['status'] != 'Void' && $bill['status'] != 'Paid' && can('bill.edit')): ?>
                    <a href="<?= site_url('bills/edit/' . $bill['id']) ?>" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>
                <?php endif; ?>
                <?php if ($bill['balance'] > 0 && $bill['status'] != 'Void' && can('bill.edit')): ?>
                    <a href="<?= site_url('bills/payment/' . $bill['id']) ?>" class="btn btn-sm btn-success">
                        <i class="fas fa-money-bill-wave me-1"></i> Record Payment
                    </a>
                <?php endif; ?>
                <a href="<?= site_url('bills/print/' . $bill['id']) ?>" target="_blank" class="btn btn-sm btn-info text-white">
                    <i class="fas fa-print me-1"></i> Print
                </a>
                <?php if (can('bill.create')): ?>
                    <a href="<?= site_url('bills/clone/' . $bill['id']) ?>" class="btn btn-sm btn-outline-secondary" title="Clone Purchase Bill">
                        <i class="fas fa-copy me-1"></i> Clone
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 border-end">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th width="40%" class="text-muted text-uppercase small">Bill Number:</th><td class="fw-bold fs-6 text-primary"><?= esc($bill['bill_number']) ?></td></tr>
                        <tr>
                            <th class="text-muted text-uppercase small">Vendor:</th>
                            <td class="fw-bold">
                                <?php if (!empty($bill['vendor_id'])): ?>
                                    <a href="<?= site_url('vendors/view/' . $bill['vendor_id']) ?>" class="text-decoration-none text-primary" title="View Vendor Details">
                                        <?= esc($bill['vendor_name']) ?> <i class="fas fa-external-link-alt small ms-1" style="font-size: 0.75rem;"></i>
                                    </a>
                                <?php else: ?>
                                    <?= esc($bill['vendor_name']) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
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
                                <?= function_exists('zoho_sync_badge') ? zoho_sync_badge('bills', (int) $bill['id']) : '' ?>
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
                                <td>
                                    <strong><?= esc(!empty($item['product_name']) ? $item['product_name'] : ($item['description'] ?? '-')) ?></strong>
                                    <?php if (!empty($item['description']) && !empty($item['product_name']) && $item['description'] !== $item['product_name']): ?>
                                        <br><small class="text-muted"><?= esc($item['description']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc(!empty($item['hsn_code']) ? $item['hsn_code'] : ($item['product_hsn'] ?? '-')) ?></td>
                                <td class="text-center fw-bold"><?= $item['quantity'] + 0 ?></td>
                                <td class="text-end">₹<?= number_format($item['rate'], 2) ?></td>
                                <td class="text-center"><span class="badge bg-light text-dark border"><?= $item['tax_percentage'] + 0 ?>%</span></td>
                                <td class="text-end fw-bold">₹<?= number_format($item['amount'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <?php
                    $subtotal = (float) ($bill['subtotal'] ?? 0);
                    $discAmt = (float) ($bill['discount_amount'] ?? 0);
                    $discType = $bill['discount_type'] ?? 'Amount';
                    $totalDiscount = ($discType == 'Percentage') ? ($subtotal * $discAmt / 100) : $discAmt;
                    $isInterState = !empty($bill['is_inter_state']);

                    $taxSlabs = [];
                    foreach ($bill['items'] as $it) {
                        $taxRate = (float) ($it['tax_percentage'] ?? 0);
                        $lineAmt = (float) ($it['quantity'] ?? 0) * (float) ($it['rate'] ?? 0);
                        $lineDisc = ($subtotal > 0) ? ($lineAmt / $subtotal * $totalDiscount) : 0;
                        $taxable = max(0, $lineAmt - $lineDisc);

                        if (!isset($taxSlabs[$taxRate])) {
                            $taxSlabs[$taxRate] = ['taxable' => 0, 'tax' => 0];
                        }
                        $taxSlabs[$taxRate]['taxable'] += $taxable;
                        $taxSlabs[$taxRate]['tax'] += ($taxable * $taxRate / 100);
                    }
                    ksort($taxSlabs);
                    ?>
                    <tfoot class="table-secondary">
                        <tr>
                            <td colspan="6" class="text-end fw-bold">Subtotal:</td>
                            <td class="text-end fw-bold">₹<?= number_format($bill['subtotal'], 2) ?></td>
                        </tr>
                        
                        <?php if (!empty($bill['discount_amount']) && $bill['discount_amount'] > 0): ?>
                            <tr>
                                <td colspan="6" class="text-end text-danger">Discount (<?= ($bill['discount_type'] ?? 'Amount') == 'Percentage' ? ($bill['discount_amount'] + 0) . '%' : 'Fixed' ?>):</td>
                                <td class="text-end text-danger fw-bold">-₹<?= number_format($totalDiscount, 2) ?></td>
                            </tr>
                        <?php endif; ?>

                        <?php if (!empty($taxSlabs)): ?>
                            <?php foreach ($taxSlabs as $rate => $slabData): ?>
                                <?php if ($rate > 0): ?>
                                    <?php if ($isInterState): ?>
                                        <tr>
                                            <td colspan="6" class="text-end">IGST (<?= $rate + 0 ?>%):</td>
                                            <td class="text-end">₹<?= number_format($slabData['tax'], 2) ?></td>
                                        </tr>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-end">CGST (<?= ($rate / 2) + 0 ?>%):</td>
                                            <td class="text-end">₹<?= number_format($slabData['tax'] / 2, 2) ?></td>
                                        </tr>
                                        <tr>
                                            <td colspan="6" class="text-end">SGST (<?= ($rate / 2) + 0 ?>%):</td>
                                            <td class="text-end">₹<?= number_format($slabData['tax'] / 2, 2) ?></td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php elseif (!empty($bill['tax_amount']) && $bill['tax_amount'] > 0): ?>
                            <tr>
                                <td colspan="6" class="text-end">Total GST:</td>
                                <td class="text-end">₹<?= number_format($bill['tax_amount'], 2) ?></td>
                            </tr>
                        <?php endif; ?>

                        <?php if (!empty($bill['shipping_charge']) && $bill['shipping_charge'] > 0): ?>
                            <tr>
                                <td colspan="6" class="text-end">Shipping Charges:</td>
                                <td class="text-end">₹<?= number_format($bill['shipping_charge'], 2) ?></td>
                            </tr>
                        <?php endif; ?>

                        <?php if (!empty($bill['roundoff_amount']) && $bill['roundoff_amount'] != 0): ?>
                            <tr>
                                <td colspan="6" class="text-end">Roundoff:</td>
                                <td class="text-end"><?= $bill['roundoff_amount'] > 0 ? '+' : '' ?>₹<?= number_format($bill['roundoff_amount'], 2) ?></td>
                            </tr>
                        <?php endif; ?>

                        <tr class="table-primary">
                            <td colspan="6" class="text-end fw-bold fs-5">Total:</td>
                            <td class="text-end fw-bold fs-5">₹<?= number_format($bill['total_amount'], 2) ?></td>
                        </tr>

                        <?php if (!empty($bill['paid_amount']) && $bill['paid_amount'] > 0): ?>
                            <tr class="table-light">
                                <td colspan="6" class="text-end">Payments Made:</td>
                                <td class="text-end text-success fw-semibold">₹<?= number_format($bill['paid_amount'], 2) ?></td>
                            </tr>
                        <?php endif; ?>

                        <tr class="table-warning">
                            <td colspan="6" class="text-end fw-bold">Balance Due:</td>
                            <td class="text-end fw-bold text-danger">₹<?= number_format($bill['balance'], 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Payment History -->
    <?php if (!empty($bill['payments'])): ?>
        <div class="card card-outline card-success shadow-sm mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title fw-bold m-0"><i class="fas fa-history me-1 text-success"></i> Payment History</h3>
                <span class="badge bg-secondary"><?= count($bill['payments']) ?> Payment(s)</span>
            </div>
            <div class="card-body p-0">
                <div class="erp-mobile-list p-2 bg-light">
                    <?php foreach ($bill['payments'] as $payment): ?>
                        <?php
                        $deductions = (float) ($payment['discount_amount'] ?? 0)
                                    + (float) ($payment['mahimai_amount'] ?? 0)
                                    + (float) ($payment['postal_charges'] ?? 0);
                        $grossSettled = (float) ($payment['amount'] ?? 0) + $deductions;
                        ?>
                        <a href="<?= site_url('payments/view/' . $payment['id']) ?>" class="erp-mobile-card">
                            <div class="fw-bold text-primary"><?= esc($payment['payment_number'] ?? '#'.$payment['id']) ?></div>
                            <div class="d-flex justify-content-between align-items-start gap-2 mt-1">
                                <span class="text-muted small" style="white-space: normal; word-break: break-word; min-width: 0; flex: 1 1 auto;">
                                    <?= date('d/m/Y', strtotime($payment['payment_date'])) ?> &middot; <?= esc($payment['payment_mode'] ?? $payment['payment_method'] ?? 'Bank') ?>
                                    <?php if (!empty($payment['reference_number'])): ?>
                                        &middot; Ref: <?= esc($payment['reference_number']) ?>
                                    <?php endif; ?>
                                    <?php if ($deductions > 0): ?>
                                        &middot; Ded: ₹<?= format_inr($deductions) ?>
                                    <?php endif; ?>
                                </span>
                                <span class="fw-bold small text-success" style="flex: 0 0 auto; white-space: nowrap;">
                                    ₹<?= format_inr($grossSettled) ?>
                                </span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
                <div class="table-responsive erp-desktop-wrap">
                    <table class="table table-striped table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Payment #</th>
                                <th>Date</th>
                                <th>Payment Method</th>
                                <th>Reference #</th>
                                <th class="text-end">Paid Amount</th>
                                <th class="text-end">Deductions</th>
                                <th class="text-end">Gross Settled</th>
                                <th>Notes</th>
                                <th class="text-center" style="width: 90px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bill['payments'] as $payment): ?>
                                <?php
                                $deductions = (float) ($payment['discount_amount'] ?? 0)
                                            + (float) ($payment['mahimai_amount'] ?? 0)
                                            + (float) ($payment['postal_charges'] ?? 0);
                                $grossSettled = (float) ($payment['amount'] ?? 0) + $deductions;
                                ?>
                                <tr>
                                    <td class="fw-bold text-primary">
                                        <a href="<?= site_url('payments/view/' . $payment['id']) ?>" class="text-decoration-none fw-bold">
                                            <?= esc($payment['payment_number'] ?? '#'.$payment['id']) ?>
                                        </a>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($payment['payment_date'])) ?></td>
                                    <td><span class="badge bg-secondary"><?= esc($payment['payment_mode'] ?? $payment['payment_method'] ?? 'Bank') ?></span></td>
                                    <td><?= esc($payment['reference_number'] ?? '-') ?></td>
                                    <td class="text-end fw-bold text-success">₹<?= number_format($payment['amount'], 2) ?></td>
                                    <td class="text-end">
                                        <?php if ($deductions > 0): ?>
                                            <span class="fw-semibold text-danger">₹<?= number_format($deductions, 2) ?></span>
                                            <?php
                                            $dedParts = [];
                                            if ((float)($payment['discount_amount'] ?? 0) > 0) $dedParts[] = 'Disc: ₹' . number_format($payment['discount_amount'], 2);
                                            if ((float)($payment['mahimai_amount'] ?? 0) > 0) $dedParts[] = 'Mahi: ₹' . number_format($payment['mahimai_amount'], 2);
                                            if ((float)($payment['postal_charges'] ?? 0) > 0) $dedParts[] = 'Post: ₹' . number_format($payment['postal_charges'], 2);
                                            ?>
                                            <div class="small text-muted" style="font-size: 0.75rem;"><?= implode(', ', $dedParts) ?></div>
                                        <?php else: ?>
                                            <span class="text-muted">₹0.00</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end fw-bold text-dark">₹<?= number_format($grossSettled, 2) ?></td>
                                    <td class="text-muted small"><?= esc($payment['notes'] ?? '-') ?></td>
                                    <td class="text-center">
                                        <a href="<?= site_url('payments/view/' . $payment['id']) ?>" class="btn btn-xs btn-outline-primary py-1 px-2" title="View Payment Details">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
                                    </td>
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
                    <div class="card shadow-sm h-100 card-info">
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
                    <div class="card shadow-sm h-100 card-warning">
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
