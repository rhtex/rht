<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1><i class="fas fa-undo text-primary me-2"></i>Sales Return / Credit Note - <?= esc($return['return_number']) ?></h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex gap-2 align-items-center">
            <a href="<?= site_url('sales_returns') ?>" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left me-1"></i> Back to List</a>
            <?php if (!empty($return['invoice_id'])): ?>
                <a href="<?= site_url('invoices/view/' . $return['invoice_id']) ?>" class="btn btn-sm btn-info text-white">
                    <i class="fas fa-file-invoice me-1"></i> View Invoice
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-12">
        <!-- Return Header & Customer Info -->
        <div class="card card-outline card-primary shadow-sm mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h3 class="card-title fw-bold mb-0">Return Information</h3>
                <div class="card-tools d-flex gap-2 align-items-center ms-auto">
                    <?php if (empty($is_single)): ?>
                        <a href="<?= site_url('sales_returns/view/' . $return['id']) ?>?mode=single" class="btn btn-sm btn-light border fw-semibold shadow-sm text-dark" title="View Full Standalone Page">
                            <i class="fas fa-expand me-1 text-primary"></i> Full Page View
                        </a>
                    <?php else: ?>
                        <a href="<?= site_url('sales_returns') ?>" class="btn btn-sm btn-light border fw-semibold shadow-sm text-dark" title="Back to Split Sales Returns View">
                            <i class="fas fa-columns me-1 text-primary"></i> Split View
                        </a>
                    <?php endif; ?>
                    <span class="badge bg-<?= $return['status'] == 'Open' ? 'warning' : ($return['status'] == 'Void' ? 'danger' : 'success') ?> fs-6">
                        <?= esc($return['status']) ?>
                    </span>
                    <?= function_exists('zoho_sync_badge') ? zoho_sync_badge('sales_returns', (int) $return['id']) : '' ?>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <!-- Left: Customer Details -->
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <th width="38%" class="text-muted text-uppercase small">Customer Name:</th>
                                <td class="fw-bold fs-6 text-dark"><?= esc($return['customer_name']) ?></td>
                            </tr>
                            <?php if (!empty($return['customer_contact_person']) && $return['customer_contact_person'] !== $return['customer_name']): ?>
                                <tr>
                                    <th class="text-muted text-uppercase small">Contact Person:</th>
                                    <td><?= esc($return['customer_contact_person']) ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if (!empty($return['customer_gstin'])): ?>
                                <tr>
                                    <th class="text-muted text-uppercase small">GSTIN:</th>
                                    <td><span class="badge bg-light text-dark border"><?= esc($return['customer_gstin']) ?></span> (<?= esc($return['customer_gst_type'] ?? 'Regular') ?>)</td>
                                </tr>
                            <?php endif; ?>
                            <?php if (!empty($return['customer_phone'])): ?>
                                <tr>
                                    <th class="text-muted text-uppercase small">Phone:</th>
                                    <td><?= esc($return['customer_phone']) ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if (!empty($return['customer_email'])): ?>
                                <tr>
                                    <th class="text-muted text-uppercase small">Email:</th>
                                    <td><?= esc($return['customer_email']) ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php 
                            $addrParts = array_filter([
                                $return['customer_address1'] ?? null,
                                $return['customer_address2'] ?? null,
                                $return['customer_city'] ?? null,
                                (!empty($return['customer_state']) ? $return['customer_state'] : '') . (!empty($return['customer_pincode']) ? ' - ' . $return['customer_pincode'] : '')
                            ]);
                            ?>
                            <?php if (!empty($addrParts)): ?>
                                <tr>
                                    <th class="text-muted text-uppercase small">Address:</th>
                                    <td><?= nl2br(esc(implode(', ', $addrParts))) ?></td>
                                </tr>
                            <?php endif; ?>
                        </table>
                    </div>

                    <!-- Right: Return / Credit Note Meta -->
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <th width="38%" class="text-muted text-uppercase small">Credit Note #:</th>
                                <td class="fw-bold fs-6 text-primary"><?= esc($return['return_number']) ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted text-uppercase small">Return Date:</th>
                                <td class="fw-bold"><?= date('d/m/Y', strtotime($return['return_date'])) ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted text-uppercase small">Against Invoice:</th>
                                <td>
                                    <?php if (!empty($return['invoice_number'])): ?>
                                        <a href="<?= site_url('invoices/view/' . $return['invoice_id']) ?>?mode=single" class="badge bg-primary text-decoration-none">
                                            <i class="fas fa-file-invoice me-1"></i> <?= esc($return['invoice_number']) ?>
                                        </a>
                                        <?php if (!empty($return['invoice_date'])): ?>
                                            <span class="text-muted small ms-1">(Dated: <?= date('d/m/Y', strtotime($return['invoice_date'])) ?>)</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">Direct Return (No invoice link)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted text-uppercase small">Place of Supply:</th>
                                <td><?= esc($return['customer_state']) ?: 'Tamil Nadu' ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted text-uppercase small">Tax Mode:</th>
                                <td>
                                    <span class="badge <?= !empty($return['is_inter_state']) ? 'bg-info' : 'bg-primary' ?>">
                                        <?= !empty($return['is_inter_state']) ? 'Inter-State (IGST)' : 'Intra-State (CGST + SGST)' ?>
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
                <h3 class="card-title fw-bold"><i class="fas fa-boxes me-1"></i> Returned Line Items (<?= count($items ?? []) ?>)</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="4%" class="text-center">#</th>
                                <th width="40%">Item & Description</th>
                                <th width="12%">HSN / SAC</th>
                                <th width="10%" class="text-center">Quantity</th>
                                <th width="12%" class="text-end">Rate (₹)</th>
                                <th width="8%" class="text-center">GST %</th>
                                <th width="14%" class="text-end">Amount (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $index => $item): ?>
                                <tr>
                                    <td class="text-center fw-bold text-muted"><?= $index + 1 ?></td>
                                    <td>
                                        <strong><?= esc(!empty($item['product_name']) ? $item['product_name'] : ($item['description'] ?? '-')) ?></strong>
                                        <?php if (!empty($item['description']) && !empty($item['product_name']) && $item['description'] !== $item['product_name']): ?>
                                            <br><small class="text-muted"><?= esc($item['description']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc(!empty($item['product_hsn']) ? $item['product_hsn'] : '-') ?></td>
                                    <td class="text-center fw-bold"><?= number_format($item['quantity'], 2) ?></td>
                                    <td class="text-end">₹<?= number_format($item['rate'], 2) ?></td>
                                    <td class="text-center"><span class="badge bg-light text-dark border"><?= number_format($item['tax_percentage'], 2) ?>%</span></td>
                                    <td class="text-end fw-bold">₹<?= number_format($item['amount'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tax Breakdown & Amount Summary -->
        <div class="row">
            <!-- Tax Slabs Breakdown (CGST, SGST, IGST) -->
            <div class="col-md-6 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-light">
                        <h6 class="card-title fw-bold mb-0"><i class="fas fa-percentage me-1"></i> GST Breakdown</h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-bordered mb-0 align-middle">
                            <thead class="table-light text-center small text-uppercase">
                                <tr>
                                    <th>Rate</th>
                                    <th>Taxable Amt</th>
                                    <?php if (empty($return['is_inter_state'])): ?>
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
                                $isInterState = !empty($return['is_inter_state']);
                                $taxSlabs = [];
                                foreach ($items as $it) {
                                    $rate = (float) $it['tax_percentage'];
                                    $lineAmt = (float) $it['amount'];
                                    if (!isset($taxSlabs["$rate"])) {
                                        $taxSlabs["$rate"] = ['taxable' => 0, 'tax' => 0];
                                    }
                                    $taxSlabs["$rate"]['taxable'] += $lineAmt;
                                    $taxSlabs["$rate"]['tax'] += ($lineAmt * $rate / 100);
                                }
                                ksort($taxSlabs);
                                ?>
                                <?php if (empty($taxSlabs)): ?>
                                    <tr><td colspan="5" class="text-center text-muted py-2">No taxable items</td></tr>
                                <?php else: ?>
                                    <?php foreach ($taxSlabs as $rate => $slab): ?>
                                        <tr class="text-center">
                                            <td class="fw-bold"><?= $rate + 0 ?>%</td>
                                            <td class="text-end">₹<?= number_format($slab['taxable'], 2) ?></td>
                                            <?php if (!$isInterState): ?>
                                                <td class="text-end">₹<?= number_format($slab['tax'] / 2, 2) ?> <small class="text-muted">(<?= ($rate / 2) + 0 ?>%)</small></td>
                                                <td class="text-end">₹<?= number_format($slab['tax'] / 2, 2) ?> <small class="text-muted">(<?= ($rate / 2) + 0 ?>%)</small></td>
                                            <?php else: ?>
                                                <td class="text-end">₹<?= number_format($slab['tax'], 2) ?> <small class="text-muted">(<?= $rate + 0 ?>%)</small></td>
                                            <?php endif; ?>
                                            <td class="text-end fw-bold">₹<?= number_format($slab['tax'], 2) ?></td>
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
                                <td class="ps-3 py-2 fw-semibold">Subtotal (Taxable):</td>
                                <td class="text-end pe-3 py-2 fw-bold">₹<?= number_format($return['subtotal'], 2) ?></td>
                            </tr>
                            <?php if (empty($return['is_inter_state'])): ?>
                                <tr>
                                    <td class="ps-3 py-2 text-muted">CGST Amount:</td>
                                    <td class="text-end pe-3 py-2">₹<?= number_format($return['tax_amount'] / 2, 2) ?></td>
                                </tr>
                                <tr>
                                    <td class="ps-3 py-2 text-muted">SGST Amount:</td>
                                    <td class="text-end pe-3 py-2">₹<?= number_format($return['tax_amount'] / 2, 2) ?></td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td class="ps-3 py-2 text-muted">IGST Amount:</td>
                                    <td class="text-end pe-3 py-2">₹<?= number_format($return['tax_amount'], 2) ?></td>
                                </tr>
                            <?php endif; ?>
                            <tr>
                                <td class="ps-3 py-2 fw-semibold">Total GST:</td>
                                <td class="text-end pe-3 py-2">₹<?= number_format($return['tax_amount'], 2) ?></td>
                            </tr>
                            <tr class="table-primary border-top border-2">
                                <td class="ps-3 py-2 fw-bold text-uppercase fs-6">Total Credit Amount:</td>
                                <td class="text-end pe-3 py-2 fw-bolder fs-5 text-primary">₹<?= number_format($return['total_amount'], 2) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($return['reason'])): ?>
            <div class="card card-outline card-info mt-2">
                <div class="card-header">
                    <h3 class="card-title fw-bold mb-0"><i class="fas fa-comment-alt me-1"></i> Reason for Return</h3>
                </div>
                <div class="card-body">
                    <p class="mb-0"><?= nl2br(esc($return['reason'])) ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
