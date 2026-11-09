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
                <li class="breadcrumb-item"><a href="<?= base_url('quotations') ?>">Quotations</a></li>
                <li class="breadcrumb-item active">View</li>
            </ol>
        </div>
    </div>

    <!-- Quotation Header Card -->
    <div class="card card-outline card-primary shadow-sm mb-3">
        <div class="card-header">
            <h3 class="card-title fw-bold"><i class="fas fa-file-invoice me-1"></i> Quotation / Estimate Details</h3>
            <?php
            $soTargetId = !empty($quotation['converted_to_so_id']) ? $quotation['converted_to_so_id'] : (!empty($quotation['linked_so_id']) ? $quotation['linked_so_id'] : null);
            $invTargetId = !empty($quotation['converted_to_invoice_id']) ? $quotation['converted_to_invoice_id'] : (!empty($quotation['linked_invoice_id']) ? $quotation['linked_invoice_id'] : null);
            $isConvertedToSO = !empty($soTargetId);
            $isConvertedToInv = !empty($invTargetId);
            $isConverted = $isConvertedToSO || $isConvertedToInv || in_array($quotation['status'], ['Accepted', 'Invoiced']);
            ?>
            <div class="card-tools d-flex gap-2">
                <?php if ($isConvertedToSO): ?>
                    <a href="<?= site_url('sales_orders/view/' . $soTargetId) ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-shopping-cart me-1"></i> View Sales Order <?= !empty($quotation['converted_so_number']) ? '(' . esc($quotation['converted_so_number']) . ')' : '' ?>
                    </a>
                <?php elseif ($isConvertedToInv): ?>
                    <a href="<?= site_url('invoices/view/' . $invTargetId) ?>" class="btn btn-sm btn-success">
                        <i class="fas fa-file-invoice-dollar me-1"></i> View Invoice <?= !empty($quotation['converted_invoice_number']) ? '(' . esc($quotation['converted_invoice_number']) . ')' : '' ?>
                    </a>
                <?php elseif (!$isConverted): ?>
                    <a href="<?= site_url('quotations/convert-so/' . $quotation['id']) ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-shopping-cart me-1"></i> Convert to Sales Order
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
        <div class="card-body">
            <?php if ($isConvertedToSO): ?>
                <div class="alert alert-info d-flex align-items-center py-2 mb-3">
                    <i class="fas fa-check-circle me-2"></i>
                    <span>This quotation has been converted to <strong>Sales Order <?= esc($quotation['converted_so_number'] ?? '#' . $soTargetId) ?></strong>. Once converted, it cannot be converted again.</span>
                    <a href="<?= site_url('sales_orders/view/' . $soTargetId) ?>" class="btn btn-sm btn-primary ms-auto">
                        <i class="fas fa-shopping-cart me-1"></i> View Sales Order &rarr;
                    </a>
                </div>
            <?php elseif ($isConvertedToInv): ?>
                <div class="alert alert-success d-flex align-items-center py-2 mb-3">
                    <i class="fas fa-check-circle me-2"></i>
                    <span>This quotation has been converted to <strong>Invoice <?= esc($quotation['converted_invoice_number'] ?? '#' . $invTargetId) ?></strong>. Once converted, it cannot be converted again.</span>
                    <a href="<?= site_url('invoices/view/' . $invTargetId) ?>" class="btn btn-sm btn-success ms-auto">
                        <i class="fas fa-file-invoice-dollar me-1"></i> View Invoice &rarr;
                    </a>
                </div>
            <?php elseif ($isConverted): ?>
                <div class="alert alert-secondary d-flex align-items-center py-2 mb-3">
                    <i class="fas fa-check-circle me-2"></i>
                    <span>This quotation is marked as <strong><?= esc($quotation['status']) ?></strong> and cannot be converted again.</span>
                </div>
            <?php else: ?>
                <div class="alert alert-danger d-flex align-items-center py-2 mb-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <span>This Quotation has <strong>not yet been converted</strong> to a Sales Order or Invoice.</span>
                    <div class="ms-auto d-flex gap-2">
                        <a href="<?= site_url('quotations/convert-so/' . $quotation['id']) ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-shopping-cart me-1"></i> Convert to SO
                        </a>
                        <a href="<?= site_url('quotations/convert-invoice/' . $quotation['id']) ?>" class="btn btn-sm btn-success">
                            <i class="fas fa-file-invoice-dollar me-1"></i> Convert to Invoice
                        </a>
                    </div>
                </div>
            <?php endif; ?>
            <div class="row">
                <div class="col-md-6 border-end">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th width="40%" class="text-muted text-uppercase small">Quotation #:</th><td class="fw-bold fs-6 text-primary"><?= esc($quotation['quotation_number']) ?></td></tr>
                        <tr><th class="text-muted text-uppercase small">Customer:</th><td class="fw-bold"><?= esc($quotation['customer_name']) ?></td></tr>
                        <?php if (!empty($quotation['customer_gstin'])): ?>
                            <tr><th class="text-muted text-uppercase small">GSTIN:</th><td><span class="badge bg-light text-dark border"><?= esc($quotation['customer_gstin']) ?></span></td></tr>
                        <?php endif; ?>
                        <?php if (!empty($quotation['customer_phone'])): ?>
                            <tr><th class="text-muted text-uppercase small">Phone / WA:</th><td><?= esc($quotation['customer_phone']) ?> <?= !empty($quotation['customer_whatsapp']) ? ' / ' . esc($quotation['customer_whatsapp']) : '' ?></td></tr>
                        <?php endif; ?>
                        <tr><th class="text-muted text-uppercase small">P.O / Ref #:</th><td><?= esc($quotation['reference_number']) ?: '-' ?></td></tr>
                        <tr><th class="text-muted text-uppercase small">Transport:</th><td><?= esc($quotation['transport_name']) ?: '-' ?></td></tr>
                        <tr><th class="text-muted text-uppercase small">Waybill / LR:</th><td><?= esc($quotation['waybill_number']) ?: '-' ?></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th width="40%" class="text-muted text-uppercase small">Quotation Date:</th><td class="fw-bold"><?= date('d/m/Y', strtotime($quotation['quotation_date'])) ?></td></tr>
                        <tr><th class="text-muted text-uppercase small">Valid Until (Expiry):</th><td><?= $quotation['expiry_date'] ? date('d/m/Y', strtotime($quotation['expiry_date'])) : '-' ?></td></tr>
                        <tr>
                            <th class="text-muted text-uppercase small">Status:</th>
                            <td>
                                <?php
                                $colors = ['Draft'=>'secondary','Sent'=>'info','Accepted'=>'success','Declined'=>'danger','Invoiced'=>'primary','Expired'=>'warning'];
                                $color = $colors[$quotation['status']] ?? 'secondary';
                                ?>
                                <span class="badge text-bg-<?= $color ?> px-2 py-1"><?= $quotation['status'] ?></span>
                                <?php if ($isConvertedToSO): ?>
                                    <a href="<?= site_url('sales_orders/view/' . $soTargetId) ?>" class="badge bg-primary text-decoration-none ms-1">
                                        <i class="fas fa-shopping-cart me-1"></i> <?= esc($quotation['converted_so_number'] ?? 'SO #' . $soTargetId) ?>
                                    </a>
                                <?php elseif ($isConvertedToInv): ?>
                                    <a href="<?= site_url('invoices/view/' . $invTargetId) ?>" class="badge bg-success text-decoration-none ms-1">
                                        <i class="fas fa-file-invoice-dollar me-1"></i> <?= esc($quotation['converted_invoice_number'] ?? 'INV #' . $invTargetId) ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted text-uppercase small">Tax Mode:</th>
                            <td>
                                <span class="badge <?= !empty($quotation['is_inter_state']) ? 'bg-info' : 'bg-primary' ?>">
                                    <?= !empty($quotation['is_inter_state']) ? 'Inter-State (IGST)' : 'Intra-State (CGST + SGST)' ?>
                                </span>
                            </td>
                        </tr>
                        <?php if ($quotation['agent_id']): ?>
                            <tr><th class="text-muted text-uppercase small">Agent:</th><td><?= esc($quotation['agent_name']) ?> (<?= number_format($quotation['agent_commission_percent'] ?? 0, 2) ?>%)</td></tr>
                            <tr><th class="text-muted text-uppercase small">Commission:</th><td>₹<?= number_format($quotation['agent_commission_amount'] ?? 0, 2) ?></td></tr>
                        <?php endif; ?>
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
                        <?php foreach ($quotation['items'] as $index => $item): ?>
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
                                <?php if (empty($quotation['is_inter_state'])): ?>
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
                            $subtotal = (float) ($quotation['subtotal'] ?? 0);
                            $discAmt = (float) ($quotation['discount_amount'] ?? 0);
                            $discType = $quotation['discount_type'] ?? 'Fixed';
                            $totalDiscount = ($discType == 'Percentage') ? ($subtotal * $discAmt / 100) : $discAmt;
                            $isInterState = !empty($quotation['is_inter_state']);

                            $taxSlabs = [];
                            foreach ($quotation['items'] as $it) {
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
                            <td class="text-end pe-3 py-2 fw-bold">₹<?= number_format($quotation['subtotal'], 2) ?></td>
                        </tr>
                        <?php if (!empty($quotation['discount_amount']) && $quotation['discount_amount'] > 0): ?>
                            <tr>
                                <td class="ps-3 py-2 text-danger fw-semibold">Discount (<?= ($quotation['discount_type'] ?? '') == 'Percentage' ? ($quotation['discount_amount'] + 0) . '%' : 'Fixed' ?>):</td>
                                <td class="text-end pe-3 py-2 text-danger fw-bold">-₹<?= number_format($totalDiscount, 2) ?></td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <td class="ps-3 py-2 fw-semibold">Total GST:</td>
                            <td class="text-end pe-3 py-2">₹<?= number_format($quotation['tax_amount'] ?? 0, 2) ?></td>
                        </tr>
                        <?php if (!empty($quotation['shipping_charge']) && $quotation['shipping_charge'] > 0): ?>
                            <tr>
                                <td class="ps-3 py-2 fw-semibold">Shipping / Freight:</td>
                                <td class="text-end pe-3 py-2">₹<?= number_format($quotation['shipping_charge'], 2) ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if (!empty($quotation['roundoff_amount']) && $quotation['roundoff_amount'] != 0): ?>
                            <tr>
                                <td class="ps-3 py-2 fw-semibold">Round Off:</td>
                                <td class="text-end pe-3 py-2"><?= $quotation['roundoff_amount'] > 0 ? '+' : '' ?>₹<?= number_format($quotation['roundoff_amount'], 2) ?></td>
                            </tr>
                        <?php endif; ?>
                        <tr class="table-light border-top border-2">
                            <td class="ps-3 py-3 fw-bold text-uppercase fs-6">Grand Total:</td>
                            <td class="text-end pe-3 py-3 fw-bolder fs-5 text-primary">₹<?= number_format($quotation['total_amount'], 2) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Notes & Terms -->
    <?php if (!empty($quotation['notes']) || !empty($quotation['terms'])): ?>
        <div class="row">
            <?php if (!empty($quotation['notes'])): ?>
                <div class="col-md-6 mb-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-light">
                            <h6 class="card-title fw-bold mb-0"><i class="fas fa-sticky-note me-1"></i> Customer Notes</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0 text-muted" style="white-space: pre-wrap;"><?= esc($quotation['notes']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (!empty($quotation['terms'])): ?>
                <div class="col-md-6 mb-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-light">
                            <h6 class="card-title fw-bold mb-0"><i class="fas fa-gavel me-1"></i> Terms & Conditions</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0 text-muted" style="white-space: pre-wrap;"><?= esc($quotation['terms']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>
<?= $this->endSection() ?>
