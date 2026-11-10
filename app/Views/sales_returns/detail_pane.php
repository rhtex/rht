<!-- Sales Return Detail Pane Content -->
<div class="return-detail-content">
    <!-- Action Bar -->
    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <h4 class="mb-0 fw-bold text-dark"><i class="fas fa-undo text-primary me-2"></i><?= esc($return['return_number']) ?></h4>
            <span class="badge bg-<?= $return['status'] == 'Open' ? 'warning' : ($return['status'] == 'Void' ? 'danger' : 'success') ?>">
                <?= esc($return['status']) ?>
            </span>
            <?= function_exists('zoho_sync_badge') ? zoho_sync_badge('sales_returns', (int) $return['id']) : '' ?>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <a href="<?= site_url('sales_returns/view/' . $return['id']) ?>?mode=single" class="btn btn-sm btn-outline-dark" title="Open Full Standalone Page">
                <i class="fas fa-external-link-alt me-1"></i> Full Page View
            </a>
            <?php if (!empty($return['invoice_id'])): ?>
                <a href="<?= site_url('invoices/view/' . $return['invoice_id']) ?>" class="btn btn-sm btn-info text-white">
                    <i class="fas fa-file-invoice me-1"></i> View Invoice
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card card-outline card-primary h-100 shadow-none border">
                <div class="card-header py-2 bg-light">
                    <h6 class="card-title m-0 fw-bold"><i class="fas fa-user text-primary me-1"></i> Customer & Invoice Details</h6>
                </div>
                <div class="card-body p-2">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th class="text-muted" width="40%">Customer:</th><td class="fw-bold text-dark"><?= esc($return['customer_name']) ?></td></tr>
                        <?php if (!empty($return['customer_gstin'])): ?>
                            <tr><th class="text-muted">GSTIN:</th><td><span class="badge bg-light text-dark border"><?= esc($return['customer_gstin']) ?></span> (<?= esc($return['customer_gst_type'] ?? 'Regular') ?>)</td></tr>
                        <?php endif; ?>
                        <?php if (!empty($return['customer_phone'])): ?>
                            <tr><th class="text-muted">Phone:</th><td><?= esc($return['customer_phone']) ?></td></tr>
                        <?php endif; ?>
                        <?php if (!empty($return['customer_city'])): ?>
                            <tr><th class="text-muted">City/State:</th><td><?= esc($return['customer_city']) ?><?= !empty($return['customer_state']) ? ', ' . esc($return['customer_state']) : '' ?></td></tr>
                        <?php endif; ?>
                        <?php if (!empty($return['invoice_number'])): ?>
                            <tr><th class="text-muted">Invoice Ref:</th><td><a href="<?= site_url('invoices/view/' . $return['invoice_id']) ?>" class="badge bg-primary text-decoration-none"><?= esc($return['invoice_number']) ?></a></td></tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-outline card-info h-100 shadow-none border">
                <div class="card-header py-2 bg-light">
                    <h6 class="card-title m-0 fw-bold"><i class="fas fa-calendar-alt text-info me-1"></i> Return Information</h6>
                </div>
                <div class="card-body p-2">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th class="text-muted" width="40%">Return Date:</th><td class="fw-semibold"><?= date('d/m/Y', strtotime($return['return_date'])) ?></td></tr>
                        <tr><th class="text-muted">Status:</th><td><span class="badge bg-<?= $return['status'] == 'Open' ? 'warning' : 'success' ?>"><?= esc($return['status']) ?></span></td></tr>
                        <tr>
                            <th class="text-muted">Tax Mode:</th>
                            <td>
                                <span class="badge <?= !empty($return['is_inter_state']) ? 'bg-info' : 'bg-primary' ?>">
                                    <?= !empty($return['is_inter_state']) ? 'Inter-State (IGST)' : 'Intra-State (CGST + SGST)' ?>
                                </span>
                            </td>
                        </tr>
                        <tr><th class="text-muted">Total:</th><td class="fw-bold text-primary fs-6">₹<?= number_format($return['total_amount'], 2) ?></td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Line Items Table -->
    <div class="card card-outline card-secondary mb-3 shadow-none border">
        <div class="card-header py-2 bg-light">
            <h6 class="card-title m-0 fw-bold"><i class="fas fa-boxes text-secondary me-1"></i> Returned Items (<?= count($items ?? []) ?>)</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th>Description</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Rate</th>
                            <th class="text-end">GST%</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($items)): ?>
                            <?php foreach ($items as $index => $item): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= esc(!empty($item['product_name']) ? $item['product_name'] : ($item['description'] ?? '-')) ?></div>
                                        <?php if (!empty($item['description']) && !empty($item['product_name']) && $item['description'] !== $item['product_name']): ?>
                                            <small class="text-muted"><?= esc($item['description']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end fw-semibold"><?= number_format($item['quantity'], 2) ?></td>
                                    <td class="text-end">₹<?= number_format($item['rate'], 2) ?></td>
                                    <td class="text-end"><?= number_format($item['tax_percentage'], 2) ?>%</td>
                                    <td class="text-end fw-bold">₹<?= number_format($item['amount'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr><td colspan="5" class="text-end fw-semibold">Subtotal (Taxable):</td><td class="text-end">₹<?= number_format($return['subtotal'], 2) ?></td></tr>
                        <?php if (empty($return['is_inter_state'])): ?>
                            <tr><td colspan="5" class="text-end text-muted">CGST Amount:</td><td class="text-end">₹<?= number_format($return['tax_amount'] / 2, 2) ?></td></tr>
                            <tr><td colspan="5" class="text-end text-muted">SGST Amount:</td><td class="text-end">₹<?= number_format($return['tax_amount'] / 2, 2) ?></td></tr>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-end text-muted">IGST Amount:</td><td class="text-end">₹<?= number_format($return['tax_amount'], 2) ?></td></tr>
                        <?php endif; ?>
                        <tr><td colspan="5" class="text-end fw-semibold">Total GST:</td><td class="text-end">₹<?= number_format($return['tax_amount'], 2) ?></td></tr>
                        <tr class="table-primary"><td colspan="5" class="text-end fw-bold fs-5">Total Credit Amount:</td><td class="text-end fw-bold fs-5 text-primary">₹<?= number_format($return['total_amount'], 2) ?></td></tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Reason -->
    <?php if (!empty($return['reason'])): ?>
        <div class="p-3 bg-light rounded border mb-3">
            <strong class="small text-muted text-uppercase d-block mb-1">Reason for Return:</strong>
            <div><?= nl2br(esc((string) $return['reason'])) ?></div>
        </div>
    <?php endif; ?>
</div>

