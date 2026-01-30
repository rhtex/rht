<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= esc($vendor['name']) ?> - Vendor Details<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row align-items-center mb-4">
    <div class="col-sm-6">
        <h1 class="m-0 text-dark fw-bold"><?= esc($vendor['name']) ?></h1>
        <p class="text-muted mb-0">Vendor ID: #<?= str_pad($vendor['id'], 5, '0', STR_PAD_LEFT) ?></p>
    </div>
    <div class="col-sm-6 text-end">
        <div class="btn-group">
            <a href="<?= site_url('vendors/edit/'.$vendor['id']) ?>" class="btn btn-warning shadow-sm">
                <i class="fas fa-edit me-1"></i> Edit Profile
            </a>
            <a href="<?= site_url('vendors') ?>" class="btn btn-secondary shadow-sm">
                <i class="fas fa-list me-1"></i> Back to List
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <!-- Left Column: Quick Stats & Contact Info -->
    <div class="col-md-4">
        <!-- Balance Card -->
        <div class="card border-0 shadow-sm mb-4 overflow-hidden" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
            <div class="card-body text-white py-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-uppercase small opacity-75 fw-bold">Current Payable Balance</span>
                    <i class="fas fa-wallet fa-2x opacity-25"></i>
                </div>
                <h2 class="fw-bold mb-1">₹<?= number_format(abs($pending_balance), 2) ?></h2>
                <div class="d-flex align-items-center">
                    <?php if ($pending_balance >= 0): ?>
                        <span class="badge bg-danger bg-opacity-75 me-2">PAYABLE (Dr)</span>
                    <?php else: ?>
                        <span class="badge bg-success bg-opacity-75 me-2">ADVANCE (Cr)</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Contact Info Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0 fw-bold"><i class="fas fa-info-circle me-2 text-primary"></i> Contact Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small text-uppercase fw-bold mb-1 d-block">Contact Person</label>
                    <p class="mb-0 fw-bold text-dark"><?= esc($vendor['contact_person'] ?: 'N/A') ?></p>
                </div>
                <div class="mb-3">
                    <label class="text-muted small text-uppercase fw-bold mb-1 d-block">Phone / Mobile</label>
                    <p class="mb-0 text-dark">
                        <i class="fas fa-phone-alt me-2 text-muted small"></i> <?= esc($vendor['phone'] ?: 'N/A') ?>
                    </p>
                    <?php if($vendor['whatsapp_number']): ?>
                    <p class="mb-0 text-dark mt-1">
                        <i class="fab fa-whatsapp me-2 text-success small"></i> <?= esc($vendor['whatsapp_number']) ?>
                    </p>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label class="text-muted small text-uppercase fw-bold mb-1 d-block">Email Address</label>
                    <p class="mb-0 text-dark">
                        <i class="fas fa-envelope me-2 text-muted small"></i> <?= esc($vendor['email'] ?: 'N/A') ?>
                    </p>
                </div>
                <div class="mb-0">
                    <label class="text-muted small text-uppercase fw-bold mb-1 d-block">Opening Balance</label>
                    <p class="mb-0 text-dark">₹<?= number_format($vendor['opening_balance'], 2) ?> (<?= $vendor['balance_type'] ?>)</p>
                </div>
            </div>
        </div>

        <!-- Address & Tax Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="card-title mb-0 fw-bold"><i class="fas fa-map-marker-alt me-2 text-primary"></i> Business Details</h5>
            </div>
            <div class="card-body">
                <div class="mb-3 pb-3 border-bottom">
                    <label class="text-muted small text-uppercase fw-bold mb-1 d-block">Billing Address</label>
                    <?php if($billing_address): ?>
                        <address class="mb-0 text-dark lead" style="font-size: 0.95rem; line-height: 1.6;">
                            <?= esc($billing_address['address_line1']) ?><br>
                            <?= $billing_address['address_line2'] ? esc($billing_address['address_line2']) . '<br>' : '' ?>
                            <?= esc($billing_address['city']) ?>, <?= $billing_state ? esc($billing_state['name']) : '' ?> - <?= esc($billing_address['pincode']) ?>
                        </address>
                    <?php else: ?>
                        <p class="text-muted mb-0">No address recorded.</p>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label class="text-muted small text-uppercase fw-bold mb-1 d-block">GST Details</label>
                    <p class="mb-0 text-dark">
                        <span class="badge bg-light text-dark border me-1"><?= esc($vendor['gst_type']) ?></span>
                        <code class="fw-bold"><?= esc($vendor['gstin'] ?: 'N/A') ?></code>
                    </p>
                </div>
                <div class="mb-0">
                    <label class="text-muted small text-uppercase fw-bold mb-1 d-block">PAN Number</label>
                    <p class="mb-0 fw-bold text-dark"><?= esc($vendor['pan_number'] ?: 'N/A') ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Tabs for Transactions -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white p-0 border-0">
                <ul class="nav nav-pills nav-fill bg-light p-1" id="vendorTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active py-3 fw-bold" id="bills-tab" data-bs-toggle="pill" data-bs-target="#bills" type="button" role="tab">
                            <i class="fas fa-file-invoice me-2"></i> Bills
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-3 fw-bold" id="payments-tab" data-bs-toggle="pill" data-bs-target="#payments" type="button" role="tab">
                            <i class="fas fa-receipt me-2"></i> Payments
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-3 fw-bold" id="credits-tab" data-bs-toggle="pill" data-bs-target="#credits" type="button" role="tab">
                            <i class="fas fa-coins me-2"></i> Credits 
                            <?php if($available_credit > 0): ?>
                                <span class="badge bg-info ms-1">₹<?= number_format($available_credit, 0) ?></span>
                            <?php endif; ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-3 fw-bold" id="returns-tab" data-bs-toggle="pill" data-bs-target="#returns" type="button" role="tab">
                            <i class="fas fa-undo-alt me-2"></i> Returns
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body p-0">
                <div class="tab-content" id="vendorTabsContent">
                    <!-- Bills Tab -->
                    <div class="tab-pane fade show active p-3" id="bills" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Bill #</th>
                                        <th>Date</th>
                                        <th class="text-end">Total Amount</th>
                                        <th class="text-end">Balance</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($bills)): ?>
                                        <tr><td colspan="6" class="text-center py-4 text-muted">No bills found for this vendor.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($bills as $bill): ?>
                                        <tr>
                                            <td><span class="fw-bold"><?= esc($bill['bill_number']) ?></span></td>
                                            <td><?= date('d M, Y', strtotime($bill['bill_date'])) ?></td>
                                            <td class="text-end fw-bold">₹<?= number_format($bill['total_amount'], 2) ?></td>
                                            <td class="text-end text-danger fw-bold">₹<?= number_format($bill['balance'], 2) ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-<?= $bill['status'] == 'Paid' ? 'success' : ($bill['status'] == 'Overdue' ? 'danger' : 'info') ?>">
                                                    <?= esc($bill['status']) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="<?= site_url('bills/view/'.$bill['id']) ?>" class="btn btn-sm btn-outline-primary rounded-circle" title="View Bill"><i class="fas fa-eye"></i></a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Payments Tab -->
                    <div class="tab-pane fade p-3" id="payments" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Payment #</th>
                                        <th>Bill #</th>
                                        <th>Date</th>
                                        <th>Mode</th>
                                        <th class="text-end">Amount Paid</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($payments)): ?>
                                        <tr><td colspan="6" class="text-center py-4 text-muted">No payment history found.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($payments as $payment): ?>
                                        <tr>
                                            <td><span class="fw-bold"><?= esc($payment['payment_number']) ?></span></td>
                                            <td><small class="text-muted"><?= esc($payment['bill_number']) ?></small></td>
                                            <td><?= date('d M, Y', strtotime($payment['payment_date'])) ?></td>
                                            <td><span class="badge bg-light text-dark border"><?= esc($payment['payment_mode']) ?></span></td>
                                            <td class="text-end fw-bold text-success">₹<?= number_format($payment['amount'], 2) ?></td>
                                            <td class="text-center">
                                                <a href="<?= site_url('purchases/payments/view/'.$payment['id']) ?>" class="btn btn-sm btn-outline-info rounded-circle" title="View Receipt"><i class="fas fa-file-invoice-dollar"></i></a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Credits Tab -->
                    <div class="tab-pane fade p-3" id="credits" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Ref #</th>
                                        <th>Date</th>
                                        <th class="text-end">Credit Amount</th>
                                        <th class="text-end">Used</th>
                                        <th class="text-end">Available</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($credits)): ?>
                                        <tr><td colspan="6" class="text-center py-4 text-muted">No credits available for this vendor.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($credits as $credit): ?>
                                        <tr>
                                            <td><span class="fw-bold"><?= esc($credit['reference_no']) ?></span></td>
                                            <td><?= date('d M, Y', strtotime($credit['created_at'])) ?></td>
                                            <td class="text-end fw-bold">₹<?= number_format($credit['amount'], 2) ?></td>
                                            <td class="text-end text-muted">₹<?= number_format($credit['used_amount'], 2) ?></td>
                                            <td class="text-end text-info fw-bold">₹<?= number_format($credit['amount'] - $credit['used_amount'], 2) ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-<?= $credit['status'] == 'Used' ? 'secondary' : 'info' ?>">
                                                    <?= esc($credit['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Returns Tab -->
                    <div class="tab-pane fade p-3" id="returns" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Return #</th>
                                        <th>Date</th>
                                        <th class="text-center">Items</th>
                                        <th>Transport</th>
                                        <th>Waybill #</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($shipments)): ?>
                                        <tr><td colspan="6" class="text-center py-4 text-muted">No return shipments recorded.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($shipments as $shipment): ?>
                                        <tr>
                                            <td><span class="fw-bold"><?= esc($shipment['reference_no']) ?></span></td>
                                            <td><?= date('d M, Y', strtotime($shipment['return_date'])) ?></td>
                                            <td class="text-center badge-pill"><span class="badge bg-light text-dark border"><?= $shipment['item_count'] ?> Products</span></td>
                                            <td><?= esc($shipment['transport_name'] ?: '-') ?></td>
                                            <td><small class="text-muted"><?= esc($shipment['waybill_number'] ?: '-') ?></small></td>
                                            <td class="text-center">
                                                <span class="badge bg-<?= $shipment['status'] == 'Delivered' ? 'success' : 'warning' ?>">
                                                    <?= esc($shipment['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .nav-pills .nav-link {
        color: #6c757d;
        border-radius: 0;
        transition: all 0.3s ease;
    }
    .nav-pills .nav-link.active {
        background-color: #fff;
        color: #1e3c72;
        border-bottom: 3px solid #1e3c72;
    }
    .nav-pills .nav-link:hover:not(.active) {
        background-color: #f8f9fa;
        color: #1e3c72;
    }
    .card {
        border-radius: 12px;
    }
</style>
<?= $this->endSection() ?>
