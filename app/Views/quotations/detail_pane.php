<!-- Quotation Detail Pane Content -->
<div class="quotation-detail-content">
    <?php
    $soTargetId = !empty($quotation['converted_to_so_id']) ? $quotation['converted_to_so_id'] : (!empty($quotation['linked_so_id']) ? $quotation['linked_so_id'] : null);
    $invTargetId = !empty($quotation['converted_to_invoice_id']) ? $quotation['converted_to_invoice_id'] : (!empty($quotation['linked_invoice_id']) ? $quotation['linked_invoice_id'] : null);
    $isConvertedToSO = !empty($soTargetId);
    $isConvertedToInv = !empty($invTargetId);
    $isConverted = $isConvertedToSO || $isConvertedToInv || in_array($quotation['status'], ['Accepted', 'Invoiced']);
    ?>

    <!-- Action Bar -->
    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <h4 class="mb-0 fw-bold text-dark"><i class="fas fa-file-invoice text-primary me-2"></i><?= esc($quotation['quotation_number']) ?></h4>
            <?php
            $colors = ['Draft'=>'secondary','Sent'=>'info','Accepted'=>'success','Declined'=>'danger','Invoiced'=>'primary','Expired'=>'warning'];
            $color = $colors[$quotation['status']] ?? 'secondary';
            ?>
            <span class="badge text-bg-<?= $color ?>"><?= esc($quotation['status']) ?></span>
            <?= function_exists('zoho_sync_badge') ? zoho_sync_badge('quotations', (int) $quotation['id']) : '' ?>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <a href="<?= site_url('quotations/view/' . $quotation['id']) ?>?mode=single" class="btn btn-sm btn-outline-dark" title="Open Full Standalone Page">
                <i class="fas fa-external-link-alt me-1"></i> Full Page View
            </a>
            <?php if ($isConvertedToSO): ?>
                <a href="<?= site_url('sales_orders/view/' . $soTargetId) ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-shopping-cart me-1"></i> View SO <?= !empty($quotation['converted_so_number']) ? '(' . esc($quotation['converted_so_number']) . ')' : '' ?>
                </a>
            <?php elseif ($isConvertedToInv): ?>
                <a href="<?= site_url('invoices/view/' . $invTargetId) ?>" class="btn btn-sm btn-success">
                    <i class="fas fa-file-invoice-dollar me-1"></i> View Invoice <?= !empty($quotation['converted_invoice_number']) ? '(' . esc($quotation['converted_invoice_number']) . ')' : '' ?>
                </a>
            <?php elseif (!$isConverted): ?>
                <a href="<?= site_url('quotations/convert-so/' . $quotation['id']) ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-shopping-cart me-1"></i> Convert to SO
                </a>
                <a href="<?= site_url('quotations/convert-invoice/' . $quotation['id']) ?>" class="btn btn-sm btn-success">
                    <i class="fas fa-file-invoice-dollar me-1"></i> Convert to Invoice
                </a>
                <a href="<?= site_url('quotations/edit/' . $quotation['id']) ?>" class="btn btn-sm btn-warning">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
            <?php endif; ?>
            <a href="<?= site_url('quotations/print/' . $quotation['id']) ?>" target="_blank" class="btn btn-sm btn-info text-white">
                <i class="fas fa-print me-1"></i> Print
            </a>
        </div>
    </div>

    <!-- Status Banner -->
    <?php if ($isConvertedToSO): ?>
        <div class="alert alert-info py-2 mb-3 d-flex align-items-center">
            <i class="fas fa-check-circle me-2 text-info fs-5"></i>
            <span class="small">Converted to <strong>Sales Order <?= esc($quotation['converted_so_number'] ?? '#' . $soTargetId) ?></strong>.</span>
            <a href="<?= site_url('sales_orders/view/' . $soTargetId) ?>" class="btn btn-sm btn-primary ms-auto">
                <i class="fas fa-shopping-cart me-1"></i> View SO &rarr;
            </a>
        </div>
    <?php elseif ($isConvertedToInv): ?>
        <div class="alert alert-success py-2 mb-3 d-flex align-items-center">
            <i class="fas fa-check-circle me-2 text-success fs-5"></i>
            <span class="small">Converted to <strong>Invoice <?= esc($quotation['converted_invoice_number'] ?? '#' . $invTargetId) ?></strong>.</span>
            <a href="<?= site_url('invoices/view/' . $invTargetId) ?>" class="btn btn-sm btn-success ms-auto">
                <i class="fas fa-file-invoice-dollar me-1"></i> View Invoice &rarr;
            </a>
        </div>
    <?php endif; ?>

    <!-- Overview Cards -->
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card card-outline card-primary h-100 shadow-none border">
                <div class="card-header py-2 bg-light">
                    <h6 class="card-title m-0 fw-bold"><i class="fas fa-user text-primary me-1"></i> Customer & Reference</h6>
                </div>
                <div class="card-body p-2">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th class="text-muted" width="40%">Customer:</th><td class="fw-bold text-dark"><?= esc($quotation['customer_name']) ?></td></tr>
                        <?php if (!empty($quotation['customer_gstin'])): ?>
                            <tr><th class="text-muted">GSTIN:</th><td><span class="badge bg-light text-dark border"><?= esc($quotation['customer_gstin']) ?></span></td></tr>
                        <?php endif; ?>
                        <tr><th class="text-muted">P.O / Ref #:</th><td><?= esc($quotation['reference_number']) ?: '-' ?></td></tr>
                        <tr><th class="text-muted">Transport:</th><td><?= esc($quotation['transport_name']) ?: '-' ?></td></tr>
                        <tr><th class="text-muted">Waybill:</th><td><?= esc($quotation['waybill_number']) ?: '-' ?></td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-outline card-info h-100 shadow-none border">
                <div class="card-header py-2 bg-light">
                    <h6 class="card-title m-0 fw-bold"><i class="fas fa-calendar-alt text-info me-1"></i> Dates & Status</h6>
                </div>
                <div class="card-body p-2">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th class="text-muted" width="40%">Quotation Date:</th><td class="fw-semibold"><?= date('d/m/Y', strtotime($quotation['quotation_date'])) ?></td></tr>
                        <tr><th class="text-muted">Valid Until:</th><td><?= $quotation['expiry_date'] ? date('d/m/Y', strtotime($quotation['expiry_date'])) : '-' ?></td></tr>
                        <tr><th class="text-muted">Tax Mode:</th><td><span class="badge <?= !empty($quotation['is_inter_state']) ? 'bg-info' : 'bg-primary' ?>"><?= !empty($quotation['is_inter_state']) ? 'IGST' : 'CGST + SGST' ?></span></td></tr>
                        <tr><th class="text-muted">Total:</th><td class="fw-bold text-primary fs-6">₹<?= number_format($quotation['total_amount'], 2) ?></td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Line Items Table -->
    <div class="card card-outline card-secondary mb-3 shadow-none border">
        <div class="card-header py-2 bg-light">
            <h6 class="card-title m-0 fw-bold"><i class="fas fa-boxes text-secondary me-1"></i> Line Items (<?= count($quotation['items'] ?? []) ?>)</h6>
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
                        <?php if (!empty($quotation['items'])): ?>
                            <?php foreach ($quotation['items'] as $index => $item): ?>
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
                        <tr><td colspan="6" class="text-end fw-semibold">Subtotal:</td><td class="text-end">₹<?= number_format($quotation['subtotal'], 2) ?></td></tr>
                        <?php if ($quotation['discount_amount'] > 0): ?>
                            <?php $actualDisc = ($quotation['discount_type'] == 'Percentage') ? ($quotation['subtotal'] * $quotation['discount_amount'] / 100) : $quotation['discount_amount']; ?>
                            <tr><td colspan="6" class="text-end text-danger">Discount (<?= $quotation['discount_type'] == 'Percentage' ? esc($quotation['discount_amount']).'%' : 'Fixed' ?>):</td><td class="text-end text-danger">-₹<?= number_format($actualDisc, 2) ?></td></tr>
                        <?php endif; ?>
                        <tr><td colspan="6" class="text-end">Tax Amount:</td><td class="text-end">₹<?= number_format($quotation['tax_amount'], 2) ?></td></tr>
                        <?php if ($quotation['shipping_charge'] > 0): ?>
                            <tr><td colspan="6" class="text-end">Shipping Charges:</td><td class="text-end">₹<?= number_format($quotation['shipping_charge'], 2) ?></td></tr>
                        <?php endif; ?>
                        <?php if (!empty($quotation['roundoff_amount']) && $quotation['roundoff_amount'] != 0): ?>
                            <tr><td colspan="6" class="text-end">Roundoff:</td><td class="text-end">₹<?= number_format($quotation['roundoff_amount'], 2) ?></td></tr>
                        <?php endif; ?>
                        <tr class="table-primary"><td colspan="6" class="text-end fw-bold fs-5">Total:</td><td class="text-end fw-bold fs-5">₹<?= number_format($quotation['total_amount'], 2) ?></td></tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Notes & Terms -->
    <?php if (!empty($quotation['notes']) || !empty($quotation['terms'])): ?>
        <div class="row g-2 mb-3">
            <?php if (!empty($quotation['notes'])): ?>
                <div class="col-md-6">
                    <div class="p-2 border rounded bg-light">
                        <strong class="small text-muted text-uppercase d-block mb-1">Notes:</strong>
                        <div class="small"><?= nl2br(esc((string) $quotation['notes'])) ?></div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (!empty($quotation['terms'])): ?>
                <div class="col-md-6">
                    <div class="p-2 border rounded bg-light">
                        <strong class="small text-muted text-uppercase d-block mb-1">Terms:</strong>
                        <div class="small"><?= nl2br(esc((string) $quotation['terms'])) ?></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

