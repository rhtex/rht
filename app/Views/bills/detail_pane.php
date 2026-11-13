<!-- Bill Detail Pane Content -->
<div class="bill-detail-content">
    <!-- Action Bar -->
    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <h4 class="mb-0 fw-bold text-dark"><i class="fas fa-file-invoice-dollar text-primary me-2"></i><?= esc($bill['bill_number']) ?></h4>
            <?php
            $statusColors = [
                'Draft'          => 'secondary',
                'Open'           => 'primary',
                'Paid'           => 'success',
                'Partially Paid' => 'info',
                'Overdue'        => 'danger',
                'Void'           => 'dark'
            ];
            $color = $statusColors[$bill['status']] ?? 'secondary';
            ?>
            <span class="badge text-bg-<?= $color ?>"><?= esc($bill['status']) ?></span>
            <?= function_exists('zoho_sync_badge') ? zoho_sync_badge('bills', (int) $bill['id']) : '' ?>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <a href="<?= site_url('bills/view/' . $bill['id']) ?>?mode=single" class="btn btn-sm btn-outline-dark" title="Open Full Standalone Page">
                <i class="fas fa-external-link-alt me-1"></i> Full Page View
            </a>
            <?php if ($bill['status'] == 'Draft' && can('bill.edit')): ?>
                <a href="<?= site_url('bills/mark-open/' . $bill['id']) ?>" class="btn btn-sm btn-success">
                    <i class="fas fa-check-circle me-1"></i> Mark Open
                </a>
            <?php endif; ?>
            <?php if ($bill['status'] != 'Void' && $bill['status'] != 'Paid' && can('bill.edit')): ?>
                <a href="<?= site_url('bills/edit/' . $bill['id']) ?>" class="btn btn-sm btn-warning">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
            <?php endif; ?>
            <?php if ($bill['balance'] > 0 && $bill['status'] != 'Void' && can('bill.edit')): ?>
                <a href="<?= site_url('bills/payment/' . $bill['id']) ?>" class="btn btn-sm btn-success">
                    <i class="fas fa-money-bill-wave me-1"></i> Payment
                </a>
            <?php endif; ?>
            <a href="<?= site_url('bills/print/' . $bill['id']) ?>" target="_blank" class="btn btn-sm btn-info text-white">
                <i class="fas fa-print me-1"></i> Print
            </a>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card card-outline card-primary h-100 shadow-none border">
                <div class="card-header py-2 bg-light">
                    <h6 class="card-title m-0 fw-bold"><i class="fas fa-user-tag text-primary me-1"></i> Vendor Details</h6>
                </div>
                <div class="card-body p-2">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th class="text-muted" width="40%">Vendor:</th><td class="fw-bold text-dark"><?= esc($bill['vendor_name']) ?></td></tr>
                        <?php if (!empty($bill['vendor_gstin'])): ?>
                            <tr><th class="text-muted">GSTIN:</th><td><span class="badge bg-light text-dark border"><?= esc($bill['vendor_gstin']) ?></span></td></tr>
                        <?php endif; ?>
                        <tr><th class="text-muted">Vendor Inv / Ref:</th><td><?= esc($bill['reference_number']) ?: '-' ?></td></tr>
                        <?php if (!empty($bill['vendor_city'])): ?>
                            <tr><th class="text-muted">City / State:</th><td><?= esc($bill['vendor_city']) ?><?= !empty($bill['vendor_state']) ? ', ' . esc($bill['vendor_state']) : '' ?></td></tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-outline card-info h-100 shadow-none border">
                <div class="card-header py-2 bg-light">
                    <h6 class="card-title m-0 fw-bold"><i class="fas fa-calendar-alt text-info me-1"></i> Bill Dates & Status</h6>
                </div>
                <div class="card-body p-2">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th class="text-muted" width="40%">Bill Date:</th><td class="fw-semibold"><?= date('d/m/Y', strtotime($bill['bill_date'])) ?></td></tr>
                        <tr><th class="text-muted">Due Date:</th><td><?= date('d/m/Y', strtotime($bill['due_date'])) ?></td></tr>
                        <tr><th class="text-muted">Total:</th><td class="fw-bold text-primary fs-6">₹<?= number_format($bill['total_amount'], 2) ?></td></tr>
                        <tr><th class="text-muted">Balance:</th><td class="fw-bold text-danger">₹<?= number_format($bill['balance'], 2) ?></td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Line Items Table -->
    <div class="card card-outline card-secondary mb-3 shadow-none border">
        <div class="card-header py-2 bg-light">
            <h6 class="card-title m-0 fw-bold"><i class="fas fa-boxes text-secondary me-1"></i> Line Items (<?= count($bill['items'] ?? []) ?>)</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th>Description</th>
                            <th>HSN</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Rate</th>
                            <th class="text-end">GST%</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($bill['items'])): ?>
                            <?php foreach ($bill['items'] as $index => $item): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= esc(!empty($item['product_name']) ? $item['product_name'] : ($item['description'] ?? '-')) ?></div>
                                        <?php if (!empty($item['description']) && !empty($item['product_name']) && $item['description'] !== $item['product_name']): ?>
                                            <small class="text-muted"><?= esc($item['description']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc(!empty($item['hsn_code']) ? $item['hsn_code'] : ($item['product_hsn'] ?? '-')) ?></td>
                                    <td class="text-end fw-semibold"><?= $item['quantity'] + 0 ?></td>
                                    <td class="text-end">₹<?= number_format($item['rate'], 2) ?></td>
                                    <td class="text-end"><?= $item['tax_percentage'] + 0 ?>%</td>
                                    <td class="text-end fw-bold">₹<?= number_format($item['amount'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr><td colspan="6" class="text-end fw-semibold">Subtotal:</td><td class="text-end">₹<?= number_format($bill['subtotal'], 2) ?></td></tr>
                        <?php if ($bill['discount_amount'] > 0): ?>
                            <?php $actualDisc = ($bill['discount_type'] == 'Percentage') ? ($bill['subtotal'] * $bill['discount_amount'] / 100) : $bill['discount_amount']; ?>
                            <tr><td colspan="6" class="text-end text-danger">Discount (<?= $bill['discount_type'] == 'Percentage' ? esc($bill['discount_amount']).'%' : 'Fixed' ?>):</td><td class="text-end text-danger">-₹<?= number_format($actualDisc, 2) ?></td></tr>
                        <?php endif; ?>
                        <tr><td colspan="6" class="text-end">Total GST:</td><td class="text-end">₹<?= number_format($bill['tax_amount'] ?? 0, 2) ?></td></tr>
                        <?php if (!empty($bill['shipping_charge']) && $bill['shipping_charge'] > 0): ?>
                            <tr><td colspan="6" class="text-end">Shipping / Freight:</td><td class="text-end">₹<?= number_format($bill['shipping_charge'], 2) ?></td></tr>
                        <?php endif; ?>
                        <?php if (!empty($bill['roundoff_amount']) && $bill['roundoff_amount'] != 0): ?>
                            <tr><td colspan="6" class="text-end">Roundoff:</td><td class="text-end">₹<?= number_format($bill['roundoff_amount'], 2) ?></td></tr>
                        <?php endif; ?>
                        <tr class="table-primary"><td colspan="6" class="text-end fw-bold fs-5">Total:</td><td class="text-end fw-bold fs-5">₹<?= number_format($bill['total_amount'], 2) ?></td></tr>
                        <tr><td colspan="6" class="text-end text-success fw-semibold">Amount Paid:</td><td class="text-end text-success fw-bold">₹<?= number_format($bill['paid_amount'], 2) ?></td></tr>
                        <tr class="table-warning"><td colspan="6" class="text-end fw-bold">Balance Due:</td><td class="text-end fw-bold text-danger">₹<?= number_format($bill['balance'], 2) ?></td></tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Payments Made -->
    <?php if (!empty($bill['payments'])): ?>
        <div class="card card-outline card-success mb-3 shadow-none border">
            <div class="card-header py-2 bg-light">
                <h6 class="card-title m-0 fw-bold"><i class="fas fa-money-check-alt text-success me-1"></i> Payments (<?= count($bill['payments']) ?>)</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Payment #</th>
                            <th>Date</th>
                            <th>Mode</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bill['payments'] as $payment): ?>
                            <tr>
                                <td class="fw-bold text-primary"><?= esc($payment['payment_number'] ?? '#'.$payment['id']) ?></td>
                                <td><?= date('d/m/Y', strtotime($payment['payment_date'])) ?></td>
                                <td><span class="badge bg-light text-dark border"><?= esc($payment['payment_mode'] ?? 'Bank') ?></span></td>
                                <td class="text-end fw-bold">₹<?= number_format($payment['amount'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- Notes & Terms -->
    <?php if (!empty($bill['notes']) || !empty($bill['terms'])): ?>
        <div class="row g-2 mb-3">
            <?php if (!empty($bill['notes'])): ?>
                <div class="col-md-6">
                    <div class="p-2 border rounded bg-light">
                        <strong class="small text-muted text-uppercase d-block mb-1">Notes:</strong>
                        <div class="small"><?= nl2br(esc((string) $bill['notes'])) ?></div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (!empty($bill['terms'])): ?>
                <div class="col-md-6">
                    <div class="p-2 border rounded bg-light">
                        <strong class="small text-muted text-uppercase d-block mb-1">Terms:</strong>
                        <div class="small"><?= nl2br(esc((string) $bill['terms'])) ?></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

