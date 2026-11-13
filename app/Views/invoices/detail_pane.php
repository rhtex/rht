<!-- Invoice Detail Pane Content -->
<div class="invoice-detail-content">
    <!-- Invoice Actions Bar -->
    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <h4 class="mb-0 fw-bold text-dark"><i class="fas fa-file-invoice text-primary me-2"></i><?= esc($invoice['invoice_number']) ?></h4>
            <?php
            $statusColors = [
                'Draft'          => 'secondary',
                'Open'           => 'primary',
                'Paid'           => 'success',
                'Partially Paid' => 'info',
                'Overdue'        => 'danger',
                'Void'           => 'dark'
            ];
            $color = $statusColors[$invoice['status']] ?? 'secondary';
            ?>
            <span class="badge text-bg-<?= $color ?>"><?= esc($invoice['status']) ?></span>
            <?= function_exists('zoho_sync_badge') ? zoho_sync_badge('invoices', (int) $invoice['id']) : '' ?>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <a href="<?= site_url('invoices/view/' . $invoice['id']) ?>?mode=single" class="btn btn-sm btn-outline-dark" title="Open Full Standalone Page">
                <i class="fas fa-external-link-alt me-1"></i> Full Page View
            </a>
            <?php if ($invoice['status'] == 'Draft' && can('invoice.edit')): ?>
                <a href="<?= site_url('invoices/mark-sent/' . $invoice['id']) ?>" class="btn btn-sm btn-success">
                    <i class="fas fa-paper-plane me-1"></i> Mark Sent
                </a>
            <?php endif; ?>
            <?php if ($invoice['status'] != 'Void' && $invoice['status'] != 'Paid' && can('invoice.edit')): ?>
                <a href="<?= site_url('invoices/edit/' . $invoice['id']) ?>" class="btn btn-sm btn-warning">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
            <?php endif; ?>
            <?php if ($invoice['balance'] > 0 && $invoice['status'] != 'Void' && can('invoice.edit')): ?>
                <a href="<?= site_url('invoices/payment/' . $invoice['id']) ?>" class="btn btn-sm btn-success">
                    <i class="fas fa-money-bill-wave me-1"></i> Payment
                </a>
            <?php endif; ?>
            <a href="<?= site_url('invoices/print/' . $invoice['id']) ?>" target="_blank" class="btn btn-sm btn-info text-white">
                <i class="fas fa-print me-1"></i> Print
            </a>
        </div>
    </div>

    <!-- Overview Details Cards -->
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card card-outline card-primary h-100 shadow-none border">
                <div class="card-header py-2 bg-light">
                    <h6 class="card-title m-0 fw-bold"><i class="fas fa-user text-primary me-1"></i> Customer & Details</h6>
                </div>
                <div class="card-body p-2">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th class="text-muted" width="40%">Customer:</th><td class="fw-bold text-dark"><?= esc($invoice['customer_name']) ?></td></tr>
                        <?php if (!empty($invoice['customer_state'])): ?>
                            <tr><th class="text-muted">Place of Supply:</th><td><?= esc($invoice['customer_state']) ?></td></tr>
                        <?php endif; ?>
                        <tr><th class="text-muted">P.O Number:</th><td><?= esc($invoice['reference_number']) ?: '-' ?></td></tr>
                        <tr><th class="text-muted">P.O Date:</th><td><?= $invoice['po_date'] ? date('d/m/Y', strtotime($invoice['po_date'])) : '-' ?></td></tr>
                        <tr><th class="text-muted">Transport:</th><td><?= esc($invoice['transport_name']) ?: '-' ?></td></tr>
                        <tr><th class="text-muted">Waybill / LR:</th><td><?= esc($invoice['waybill_number']) ?: '-' ?></td></tr>
                        <tr><th class="text-muted">Waybill Date:</th><td><?= $invoice['waybill_date'] ? date('d/m/Y', strtotime($invoice['waybill_date'])) : '-' ?></td></tr>
                        <tr>
                            <th class="text-muted">Delivery:</th>
                            <td>
                                <?php
                                $delStatusColors = [
                                    'Pending'    => 'secondary',
                                    'In Transit' => 'primary',
                                    'Delivered'  => 'success',
                                    'Cancelled'  => 'danger'
                                ];
                                $delColor = $delStatusColors[$invoice['delivery_status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $delColor ?>"><?= esc($invoice['delivery_status']) ?></span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-outline card-info h-100 shadow-none border">
                <div class="card-header py-2 bg-light">
                    <h6 class="card-title m-0 fw-bold"><i class="fas fa-calendar-alt text-info me-1"></i> Dates & References</h6>
                </div>
                <div class="card-body p-2">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th class="text-muted" width="40%">Invoice Date:</th><td class="fw-semibold"><?= date('d/m/Y', strtotime($invoice['invoice_date'])) ?></td></tr>
                        <tr><th class="text-muted">Due Date:</th><td class="fw-semibold"><?= date('d/m/Y', strtotime($invoice['due_date'])) ?></td></tr>
                        <?php if (!empty($invoice['so_number'])): ?>
                            <tr><th class="text-muted">SO Number:</th><td>
                                <?php if (!empty($linked_sales_order)): ?>
                                    <a href="<?= site_url('sales_orders/view/' . $linked_sales_order['id']) ?>" class="badge bg-primary text-decoration-none">
                                        <i class="fas fa-shopping-cart me-1"></i> <?= esc($invoice['so_number']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-primary fw-bold"><?= esc($invoice['so_number']) ?></span>
                                <?php endif; ?>
                            </td></tr>
                        <?php endif; ?>
                        <?php if (!empty($invoice['so_date'])): ?>
                            <tr><th class="text-muted">SO Date:</th><td><?= date('d/m/Y', strtotime($invoice['so_date'])) ?></td></tr>
                        <?php endif; ?>
                        <?php if (!empty($linked_quotation)): ?>
                            <tr><th class="text-muted">Source Quote:</th><td>
                                <a href="<?= site_url('quotations/view/' . $linked_quotation['id']) ?>" class="badge bg-info text-decoration-none">
                                    <i class="fas fa-file-invoice me-1"></i> <?= esc($linked_quotation['quotation_number']) ?>
                                </a>
                            </td></tr>
                        <?php endif; ?>
                        <?php if (!empty($invoice['selection_type'])): ?>
                            <tr><th class="text-muted">Selection:</th><td><span class="badge bg-secondary"><?= esc($invoice['selection_type']) ?></span></td></tr>
                        <?php endif; ?>
                        <?php if (!empty($agent)): ?>
                            <tr><th class="text-muted">Agent:</th><td><?= esc($agent['agent_name']) ?></td></tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Line Items Table -->
    <div class="card card-outline card-secondary mb-3 shadow-none border">
        <div class="card-header py-2 bg-light">
            <h6 class="card-title m-0 fw-bold"><i class="fas fa-boxes text-secondary me-1"></i> Line Items (<?= count($invoice['items'] ?? []) ?>)</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="4%">#</th>
                            <th width="38%">Item & Description</th>
                            <th>HSN Code</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Rate</th>
                            <th class="text-center">GST %</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($invoice['items'] ?? []) as $index => $item): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td>
                                    <strong><?= esc(!empty($item['product_name']) ? $item['product_name'] : ($item['description'] ?? '-')) ?></strong>
                                    <?php if (!empty($item['description']) && !empty($item['product_name']) && $item['description'] !== $item['product_name']): ?>
                                        <br><small class="text-muted"><?= esc($item['description']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc(!empty($item['hsn_code']) ? $item['hsn_code'] : ($item['product_hsn'] ?? '-')) ?></td>
                                <td class="text-center fw-semibold"><?= $item['quantity'] + 0 ?></td>
                                <td class="text-end">₹<?= number_format($item['rate'], 2) ?></td>
                                <td class="text-center"><span class="badge bg-light text-dark border"><?= $item['tax_percentage'] + 0 ?>%</span></td>
                                <td class="text-end fw-bold">₹<?= number_format($item['amount'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-secondary">
                        <tr>
                            <td colspan="6" class="text-end fw-bold">Subtotal:</td>
                            <td class="text-end fw-bold">₹<?= number_format($invoice['subtotal'], 2) ?></td>
                        </tr>
                        
                        <?php if ($invoice['discount_amount'] > 0): ?>
                            <?php 
                            $actualDiscount = ($invoice['discount_type'] == 'Percentage') ? ($invoice['subtotal'] * $invoice['discount_amount'] / 100) : $invoice['discount_amount']; 
                            ?>
                            <tr>
                                <td colspan="6" class="text-end text-danger">Discount (<?= $invoice['discount_type'] == 'Percentage' ? ($invoice['discount_amount']+0).'%' : 'Fixed' ?>):</td>
                                <td class="text-end text-danger fw-bold">-₹<?= number_format($actualDiscount, 2) ?></td>
                            </tr>
                        <?php endif; ?>

                        <?php
                        $cgstGroups = [];
                        $sgstGroups = [];
                        $igstGroups = [];
                        
                        $grossSubtotal = (float)$invoice['subtotal'];
                        $discVal = 0;
                        if ($invoice['discount_amount'] > 0) {
                            $discVal = ($invoice['discount_type'] == 'Percentage') ? ($grossSubtotal * (float)$invoice['discount_amount'] / 100) : (float)$invoice['discount_amount'];
                        }

                        foreach ($invoice['items'] as $item) {
                            $rate = (float)$item['tax_percentage'];
                            if ($rate > 0) {
                                $itemGross = (float)$item['quantity'] * (float)$item['rate'];
                                $itemDiscount = ($grossSubtotal > 0) ? ($itemGross / $grossSubtotal * $discVal) : 0;
                                $taxableVal = $itemGross - $itemDiscount;
                                
                                $taxAmt = ($taxableVal * $rate) / 100;
                                
                                if (!empty($invoice['is_inter_state'])) {
                                    if (!isset($igstGroups["$rate"])) $igstGroups["$rate"] = 0;
                                    $igstGroups["$rate"] += $taxAmt;
                                } else {
                                    $halfRate = $rate / 2;
                                    $halfTax = $taxAmt / 2;
                                    if (!isset($cgstGroups["$halfRate"])) $cgstGroups["$halfRate"] = 0;
                                    if (!isset($sgstGroups["$halfRate"])) $sgstGroups["$halfRate"] = 0;
                                    $cgstGroups["$halfRate"] += $halfTax;
                                    $sgstGroups["$halfRate"] += $halfTax;
                                }
                            }
                        }
                        ksort($cgstGroups);
                        ksort($sgstGroups);
                        ksort($igstGroups);
                        ?>

                        <?php if ($invoice['is_inter_state']): ?>
                            <?php foreach($igstGroups as $r => $amt): ?>
                                <tr><td colspan="6" class="text-end">IGST (<?= $r+0 ?>%):</td><td class="text-end">₹<?= number_format($amt, 2) ?></td></tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php foreach($cgstGroups as $r => $amt): ?>
                                <tr><td colspan="6" class="text-end">CGST (<?= $r+0 ?>%):</td><td class="text-end">₹<?= number_format($amt, 2) ?></td></tr>
                            <?php endforeach; ?>
                            
                            <?php foreach($sgstGroups as $r => $amt): ?>
                                <tr><td colspan="6" class="text-end">SGST (<?= $r+0 ?>%):</td><td class="text-end">₹<?= number_format($amt, 2) ?></td></tr>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if ($invoice['shipping_charge'] > 0): ?>
                            <tr><td colspan="6" class="text-end">Shipping Charges:</td><td class="text-end">₹<?= number_format($invoice['shipping_charge'], 2) ?></td></tr>
                        <?php endif; ?>

                        <?php if ($invoice['roundoff_amount'] != 0): ?>
                            <tr><td colspan="6" class="text-end">Roundoff:</td><td class="text-end">₹<?= number_format($invoice['roundoff_amount'], 2) ?></td></tr>
                        <?php endif; ?>

                        <tr class="table-primary"><td colspan="6" class="text-end fw-bold fs-5">Total:</td><td class="text-end fw-bold fs-5">₹<?= number_format($invoice['total_amount'], 2) ?></td></tr>
                        <?php 
                        $totalCredits = !empty($invoice['credit_notes']) ? array_sum(array_column($invoice['credit_notes'], 'total_amount')) : 0;
                        $totalReceipts = !empty($invoice['payments']) ? array_sum(array_column($invoice['payments'], 'amount')) : 0;
                        ?>
                        <?php if ($totalReceipts > 0): ?>
                            <tr class="table-light"><td colspan="6" class="text-end">Payments Received:</td><td class="text-end text-success fw-semibold">₹<?= number_format($totalReceipts, 2) ?></td></tr>
                        <?php endif; ?>
                        <?php if ($totalCredits > 0): ?>
                            <tr class="table-light"><td colspan="6" class="text-end">Credits Applied (CN):</td><td class="text-end text-danger fw-semibold">₹<?= number_format($totalCredits, 2) ?></td></tr>
                        <?php endif; ?>
                        <tr class="table-warning"><td colspan="6" class="text-end fw-bold">Balance Due:</td><td class="text-end fw-bold text-danger">₹<?= number_format($invoice['balance'], 2) ?></td></tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Receipts / Payments -->
    <?php if (!empty($invoice['payments'])): ?>
        <div class="card card-outline card-success mb-3 shadow-none border">
            <div class="card-header py-2 bg-light d-flex justify-content-between align-items-center">
                <h6 class="card-title m-0 fw-bold"><i class="fas fa-receipt text-success me-1"></i> Receipts & Payments (<?= count($invoice['payments']) ?>)</h6>
                <span class="badge bg-success-subtle text-success border border-success-subtle small">
                    ₹<?= number_format(array_sum(array_column($invoice['payments'], 'amount')), 2) ?>
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Receipt #</th>
                                <th>Date</th>
                                <th>Mode</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">Deductions</th>
                                <th class="text-end">Total Settled</th>
                                <th>Reference</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invoice['payments'] as $payment): ?>
                                <tr>
                                    <td class="fw-bold text-primary"><?= esc($payment['payment_number']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($payment['payment_date'])) ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= esc($payment['payment_mode']) ?></span></td>
                                    <td class="text-end fw-semibold">₹<?= number_format($payment['amount'], 2) ?></td>
                                    <td class="text-end text-danger">
                                        <?php 
                                        $ded = ($payment['discount_amount'] ?? 0) + ($payment['mahimai_amount'] ?? 0) + ($payment['postal_charges'] ?? 0);
                                        echo ($ded > 0) ? '₹' . number_format($ded, 2) : '-';
                                        ?>
                                    </td>
                                    <td class="text-end fw-bold text-success">₹<?= number_format($payment['amount'] + $ded, 2) ?></td>
                                    <td><?= esc($payment['reference_number']) ?: '-' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Credit Notes / Returns -->
    <?php if (!empty($invoice['credit_notes'])): ?>
        <div class="card card-outline card-warning mb-3 shadow-none border">
            <div class="card-header py-2 bg-light d-flex justify-content-between align-items-center">
                <h6 class="card-title m-0 fw-bold"><i class="fas fa-undo text-warning me-1"></i> Credit Notes / Returns (<?= count($invoice['credit_notes']) ?>)</h6>
                <span class="badge bg-warning-subtle text-dark border border-warning-subtle small">
                    ₹<?= number_format(array_sum(array_column($invoice['credit_notes'], 'total_amount')), 2) ?>
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Credit Note #</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th class="text-end">Total Credited</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invoice['credit_notes'] as $cn): ?>
                                <tr>
                                    <td class="fw-bold">
                                        <a href="<?= site_url('sales_returns/view/' . $cn['id']) ?>?mode=single" class="text-primary text-decoration-none">
                                            <?= esc($cn['return_number']) ?>
                                        </a>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($cn['return_date'])) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $cn['status'] == 'Closed' ? 'success' : ($cn['status'] == 'Void' ? 'danger' : 'warning text-dark') ?>" style="font-size: 0.65rem;">
                                            <?= esc($cn['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold text-danger">₹<?= number_format($cn['total_amount'], 2) ?></td>
                                    <td class="text-center">
                                        <a href="<?= site_url('sales_returns/view/' . $cn['id']) ?>?mode=single" class="btn btn-xs btn-outline-primary py-0 px-1" title="View Credit Note" target="_blank">
                                            <i class="fas fa-eye"></i>
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
    <?php if (!empty($invoice['notes']) || !empty($invoice['terms'])): ?>
        <div class="row g-2 mb-3">
            <?php if (!empty($invoice['notes'])): ?>
                <div class="col-md-6">
                    <div class="p-2 border rounded bg-light">
                        <strong class="small text-muted text-uppercase d-block mb-1">Notes:</strong>
                        <div class="small"><?= nl2br(esc((string) $invoice['notes'])) ?></div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (!empty($invoice['terms'])): ?>
                <div class="col-md-6">
                    <div class="p-2 border rounded bg-light">
                        <strong class="small text-muted text-uppercase d-block mb-1">Terms:</strong>
                        <div class="small"><?= nl2br(esc((string) $invoice['terms'])) ?></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

