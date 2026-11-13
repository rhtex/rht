<!-- Sales Order Detail Pane Content -->
<div class="sales-order-detail-content">
    <!-- Action Bar -->
    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <h4 class="mb-0 fw-bold text-dark"><i class="fas fa-shopping-cart text-primary me-2"></i><?= esc($order['sales_order_number']) ?></h4>
            <?php
            $colors = ['Draft'=>'secondary','Confirmed'=>'primary','Closed'=>'success','Void'=>'dark'];
            $color = $colors[$order['status']] ?? 'secondary';
            ?>
            <span class="badge text-bg-<?= $color ?>"><?= esc($order['status']) ?></span>
            <?= function_exists('zoho_sync_badge') ? zoho_sync_badge('sales_orders', (int) $order['id']) : '' ?>
            <?php if (!empty($order['converted_to_invoice_id'])): ?>
                <span class="badge bg-success-subtle text-success border border-success-subtle">
                    <i class="fas fa-check-double me-1"></i>Invoiced
                </span>
            <?php endif; ?>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <a href="<?= site_url('sales_orders/view/' . $order['id']) ?>?mode=single" class="btn btn-sm btn-outline-dark" title="Open Full Standalone Page">
                <i class="fas fa-external-link-alt me-1"></i> Full Page View
            </a>
            <?php if (!empty($order['converted_to_invoice_id'])): ?>
                <a href="<?= site_url('invoices/view/' . $order['converted_to_invoice_id']) ?>" class="btn btn-sm btn-success">
                    <i class="fas fa-file-invoice me-1"></i> View Invoice <?= !empty($order['converted_invoice_number']) ? '(' . esc($order['converted_invoice_number']) . ')' : '' ?>
                </a>
            <?php else: ?>
                <?php if (can('invoice.create')): ?>
                <a href="<?= site_url('sales_orders/convert/' . $order['id']) ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-file-invoice-dollar me-1"></i> Convert to Invoice
                </a>
                <?php endif; ?>
                <?php if (can('sales_order.edit')): ?>
                <a href="<?= site_url('sales_orders/edit/' . $order['id']) ?>" class="btn btn-sm btn-warning">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <?php endif; ?>
            <?php endif; ?>
            <a href="<?= site_url('sales_orders/print/' . $order['id']) ?>" target="_blank" class="btn btn-sm btn-info text-white">
                <i class="fas fa-print me-1"></i> Print
            </a>
        </div>
    </div>

    <!-- Conversion Banner -->
    <?php if (empty($order['converted_to_invoice_id'])): ?>
        <div class="alert alert-warning py-2 mb-3 d-flex align-items-center">
            <i class="fas fa-exclamation-circle me-2 text-warning fs-5"></i>
            <span class="small">This Sales Order has <strong>not yet been converted</strong> to an invoice.</span>
            <?php if (can('invoice.create')): ?>
            <a href="<?= site_url('sales_orders/convert/' . $order['id']) ?>" class="btn btn-sm btn-primary ms-auto">
                <i class="fas fa-file-invoice-dollar me-1"></i> Convert Now
            </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-success py-2 mb-3 d-flex align-items-center">
            <i class="fas fa-check-circle me-2 text-success fs-5"></i>
            <span class="small">Converted to <strong>Invoice <?= esc($order['converted_invoice_number'] ?? '#' . $order['converted_to_invoice_id']) ?></strong>.</span>
            <a href="<?= site_url('invoices/view/' . $order['converted_to_invoice_id']) ?>" class="btn btn-sm btn-success ms-auto">
                <i class="fas fa-file-invoice me-1"></i> View Invoice &rarr;
            </a>
        </div>
    <?php endif; ?>

    <!-- Overview Cards -->
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card card-outline card-primary h-100 shadow-none border">
                <div class="card-header py-2 bg-light">
                    <h6 class="card-title m-0 fw-bold"><i class="fas fa-user text-primary me-1"></i> Customer & Order Details</h6>
                </div>
                <div class="card-body p-2">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th class="text-muted" width="40%">Customer:</th><td class="fw-bold text-dark"><?= esc($order['customer_name']) ?></td></tr>
                        <tr><th class="text-muted">P.O Number:</th><td><?= esc($order['reference_number']) ?: '-' ?></td></tr>
                        <?php if (!empty($order['quotation_id'])): ?>
                            <tr><th class="text-muted">Source Quote:</th><td><a href="<?= site_url('quotations/view/' . $order['quotation_id']) ?>" class="badge bg-info text-decoration-none"><?= esc($order['source_quotation_number'] ?? 'QTN #' . $order['quotation_id']) ?></a></td></tr>
                        <?php endif; ?>
                        <?php if (!empty($order['selection_type'])): ?>
                            <tr><th class="text-muted">Selection Type:</th><td><span class="badge bg-secondary"><?= esc($order['selection_type']) ?></span></td></tr>
                        <?php endif; ?>
                        <tr><th class="text-muted">Transport:</th><td><?= esc($order['transport_name']) ?: '-' ?></td></tr>
                        <tr><th class="text-muted">Waybill / LR:</th><td><?= esc($order['waybill_number']) ?: '-' ?></td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-outline card-info h-100 shadow-none border">
                <div class="card-header py-2 bg-light">
                    <h6 class="card-title m-0 fw-bold"><i class="fas fa-calendar-alt text-info me-1"></i> Dates & Shipping</h6>
                </div>
                <div class="card-body p-2">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th class="text-muted" width="40%">Order Date:</th><td class="fw-semibold"><?= date('d/m/Y', strtotime($order['order_date'])) ?></td></tr>
                        <tr><th class="text-muted">Shipment Date:</th><td><?= $order['shipment_date'] ? date('d/m/Y', strtotime($order['shipment_date'])) : '-' ?></td></tr>
                        <?php if (!empty($order['agent_id'])): ?>
                            <tr><th class="text-muted">Agent:</th><td><?= esc($order['agent_name'] ?? 'Assigned') ?></td></tr>
                        <?php endif; ?>
                        <tr><th class="text-muted">Total Amount:</th><td class="fw-bold text-primary fs-6">₹<?= number_format($order['total_amount'], 2) ?></td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Line Items Table -->
    <div class="card card-outline card-secondary mb-3 shadow-none border">
        <div class="card-header py-2 bg-light">
            <h6 class="card-title m-0 fw-bold"><i class="fas fa-boxes text-secondary me-1"></i> Line Items (<?= count($order['items'] ?? []) ?>)</h6>
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
                        <?php if (!empty($order['items'])): ?>
                            <?php foreach ($order['items'] as $index => $item): ?>
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
                        <tr><td colspan="6" class="text-end fw-semibold">Subtotal:</td><td class="text-end">₹<?= number_format($order['subtotal'], 2) ?></td></tr>
                        <?php if ($order['discount_amount'] > 0): ?>
                            <?php $actualDisc = ($order['discount_type'] == 'Percentage') ? ($order['subtotal'] * $order['discount_amount'] / 100) : $order['discount_amount']; ?>
                            <tr><td colspan="6" class="text-end text-danger">Discount (<?= $order['discount_type'] == 'Percentage' ? esc($order['discount_amount']).'%' : 'Fixed' ?>):</td><td class="text-end text-danger">-₹<?= number_format($actualDisc, 2) ?></td></tr>
                        <?php endif; ?>

                        <?php
                        $taxGroups = [];
                        $subtotal = $order['subtotal'];
                        $discountAmt = ($order['discount_type'] == 'Percentage') ? ($subtotal * $order['discount_amount'] / 100) : $order['discount_amount'];

                        if (!empty($order['items'])) {
                            foreach ($order['items'] as $item) {
                                $rate = (float)($item['tax_percentage'] ?? 0);
                                if ($rate > 0) {
                                    $itemAmt = $item['quantity'] * $item['rate'];
                                    $itemDiscount = ($subtotal > 0) ? ($itemAmt / $subtotal * $discountAmt) : 0;
                                    $taxableVal = $itemAmt - $itemDiscount;
                                    $taxAmt = ($taxableVal * $rate) / 100;
                                    if (!isset($taxGroups["$rate"])) $taxGroups["$rate"] = 0;
                                    $taxGroups["$rate"] += $taxAmt;
                                }
                            }
                        }
                        ksort($taxGroups);
                        $isInterState = !empty($order['is_inter_state']);
                        ?>

                        <?php foreach ($taxGroups as $r => $amt): ?>
                            <?php if ($isInterState): ?>
                                <tr><td colspan="6" class="text-end">IGST (<?= $r + 0 ?>%):</td><td class="text-end">₹<?= number_format($amt, 2) ?></td></tr>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-end">CGST (<?= ($r / 2) + 0 ?>%):</td><td class="text-end">₹<?= number_format($amt / 2, 2) ?></td></tr>
                                <tr><td colspan="6" class="text-end">SGST (<?= ($r / 2) + 0 ?>%):</td><td class="text-end">₹<?= number_format($amt / 2, 2) ?></td></tr>
                            <?php endif; ?>
                        <?php endforeach; ?>

                        <?php if ($order['shipping_charge'] > 0): ?>
                            <tr><td colspan="6" class="text-end">Shipping Charges:</td><td class="text-end">₹<?= number_format($order['shipping_charge'], 2) ?></td></tr>
                        <?php endif; ?>
                        <?php if (!empty($order['roundoff_amount']) && $order['roundoff_amount'] != 0): ?>
                            <tr><td colspan="6" class="text-end">Roundoff:</td><td class="text-end">₹<?= number_format($order['roundoff_amount'], 2) ?></td></tr>
                        <?php endif; ?>
                        <tr class="table-primary"><td colspan="6" class="text-end fw-bold fs-5">Total:</td><td class="text-end fw-bold fs-5">₹<?= number_format($order['total_amount'], 2) ?></td></tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Notes & Terms -->
    <?php if (!empty($order['notes']) || !empty($order['terms'])): ?>
        <div class="row g-2 mb-3">
            <?php if (!empty($order['notes'])): ?>
                <div class="col-md-6">
                    <div class="p-2 border rounded bg-light">
                        <strong class="small text-muted text-uppercase d-block mb-1">Notes:</strong>
                        <div class="small"><?= nl2br(esc((string) $order['notes'])) ?></div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (!empty($order['terms'])): ?>
                <div class="col-md-6">
                    <div class="p-2 border rounded bg-light">
                        <strong class="small text-muted text-uppercase d-block mb-1">Terms:</strong>
                        <div class="small"><?= nl2br(esc((string) $order['terms'])) ?></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

