<?= $this->extend('layouts/master') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark"><?= $title ?></h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('invoices') ?>">Invoices</a></li>
                <li class="breadcrumb-item active">View</li>
            </ol>
        </div>
    </div>

    <!-- Invoice Header -->
    <div class="card card-outline card-primary">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Invoice Information</h3>
            <div class="card-tools d-flex gap-2 align-items-center ms-auto">
                <?php if (empty($is_single)): ?>
                    <a href="<?= site_url('invoices/view/' . $invoice['id']) ?>?mode=single" class="btn btn-sm btn-light border fw-semibold shadow-sm text-dark" title="View Full Standalone Page">
                        <i class="fas fa-expand me-1 text-primary"></i> Full Page View
                    </a>
                <?php else: ?>
                    <a href="<?= site_url('invoices') ?>" class="btn btn-sm btn-light border fw-semibold shadow-sm text-dark" title="Back to Split Invoices View">
                        <i class="fas fa-columns me-1 text-primary"></i> Split View
                    </a>
                <?php endif; ?>
                <?php if ($invoice['status'] == 'Draft' && can('invoice.edit')): ?>
                    <a href="<?= site_url('invoices/mark-sent/' . $invoice['id']) ?>" class="btn btn-sm btn-success">
                        <i class="fas fa-paper-plane me-1"></i> Mark as Sent
                    </a>
                <?php endif; ?>
                <?php if ($invoice['status'] != 'Void' && $invoice['status'] != 'Paid' && can('invoice.edit')): ?>
                    <a href="<?= site_url('invoices/edit/' . $invoice['id']) ?>" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>
                <?php endif; ?>
                <?php if ($invoice['balance'] > 0 && $invoice['status'] != 'Void' && can('invoice.edit')): ?>
                    <a href="<?= site_url('invoices/payment/' . $invoice['id']) ?>" class="btn btn-sm btn-success">
                        <i class="fas fa-money-bill me-1"></i> Record Payment
                    </a>
                <?php endif; ?>
                <a href="<?= site_url('invoices/print/' . $invoice['id']) ?>" target="_blank" class="btn btn-sm btn-info text-white">
                    <i class="fas fa-print me-1"></i> Print
                </a>
                <?php if (can('eway_bill.create')): ?>
                    <a href="<?= site_url('eway_bills/create?doc_type=invoice&doc_id=' . $invoice['id']) ?>" class="btn btn-sm btn-outline-primary" title="Generate e-Way Bill via Zoho Books">
                        <i class="fas fa-truck me-1"></i> e-Way Bill
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr><th width="40%">Invoice Number:</th><td class="fw-bold"><?= esc($invoice['invoice_number']) ?></td></tr>
                        <tr><th>Customer:</th><td><?= esc($invoice['customer_name']) ?></td></tr>
                        <tr><th>P.O number:</th><td><?= esc($invoice['reference_number']) ?: '-' ?></td></tr>
                        <tr><th>P.O Date:</th><td><?= $invoice['po_date'] ? date('d/m/Y', strtotime($invoice['po_date'])) : '-' ?></td></tr>
                        <tr><th>Status:</th>
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
                                $color = $statusColors[$invoice['status']] ?? 'secondary';
                                ?>
                                <span class="badge text-bg-<?= $color ?>"><?= $invoice['status'] ?></span>
                                <?= function_exists('zoho_sync_badge') ? zoho_sync_badge('invoices', (int) $invoice['id']) : '' ?>
                            </td>
                        </tr>
                        <tr><th>Transport:</th><td><?= esc($invoice['transport_name']) ?: '-' ?></td></tr>
                        <tr><th>Waybill / LR:</th><td><?= esc($invoice['waybill_number']) ?: '-' ?></td></tr>
                        <tr><th>Waybill Date:</th><td><?= $invoice['waybill_date'] ? date('d/m/Y', strtotime($invoice['waybill_date'])) : '-' ?></td></tr>
                        <tr><th>Packages:</th><td><?= esc($invoice['packages_count']) ?: '-' ?></td></tr>
                        <tr><th>No. of Bales:</th><td><?= esc($invoice['bales_count'] ?? '') !== '' ? esc($invoice['bales_count']) : '-' ?></td></tr>
                        <tr>
                            <th>Delivery Status:</th>
                            <td>
                                <?php
                                $delStatusColors = [
                                    'Pending' => 'secondary',
                                    'In Transit' => 'primary',
                                    'Delivered' => 'success',
                                    'Cancelled' => 'danger'
                                ];
                                $delColor = $delStatusColors[$invoice['delivery_status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $delColor ?>"><?= esc($invoice['delivery_status']) ?></span>
                            </td>
                        </tr>
                        <?php if ($invoice['waybill_image']): ?>
                            <tr>
                                <th>Waybill Image:</th>
                                <td>
                                    <a href="<?= base_url('uploads/waybills/' . $invoice['waybill_image']) ?>" target="_blank">
                                        <img src="<?= base_url('uploads/waybills/' . $invoice['waybill_image']) ?>" alt="Waybill" style="max-width: 150px; cursor: pointer;" class="img-thumbnail">
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr><th width="40%">Invoice Date:</th><td><?= date('d/m/Y', strtotime($invoice['invoice_date'])) ?></td></tr>
                        <tr><th>Due Date:</th><td><?= date('d/m/Y', strtotime($invoice['due_date'])) ?></td></tr>
                        <?php if (!empty($invoice['so_number'])): ?>
                            <tr><th>SO Number:</th><td>
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
                            <tr><th>SO Date:</th><td><?= date('d/m/Y', strtotime($invoice['so_date'])) ?></td></tr>
                        <?php endif; ?>
                        <?php if (!empty($linked_quotation)): ?>
                            <tr><th>Source Quotation:</th><td>
                                <a href="<?= site_url('quotations/view/' . $linked_quotation['id']) ?>" class="badge bg-info text-decoration-none">
                                    <i class="fas fa-file-invoice me-1"></i> <?= esc($linked_quotation['quotation_number']) ?>
                                </a>
                            </td></tr>
                        <?php endif; ?>
                        <?php if (!empty($invoice['selection_type'])): ?>
                            <tr><th>Selection Type:</th><td><span class="badge bg-secondary"><?= esc($invoice['selection_type']) ?></span></td></tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Agent Commission Information -->
    <?php if (!empty($invoice['agent_id']) && !empty($agent)): ?>
    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-tie"></i> Agent Commission</h3>
            <?php if ($invoice['agent_commission_status'] == 'Unpaid' && $invoice['agent_commission_amount'] > 0): ?>
                <div class="card-tools">
                    <a href="<?= site_url('agent-payments/create/' . $invoice['agent_id']) ?>" class="btn btn-sm btn-success">
                        <i class="fas fa-hand-holding-usd"></i> Pay Commission
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr><th width="40%">Agent Name:</th><td><?= esc($agent['agent_name']) ?></td></tr>
                        <tr><th>Phone:</th><td><?= esc($agent['phone_number']) ?></td></tr>
                        <tr><th>Commission Rate:</th><td><?= number_format($invoice['agent_commission_percent'], 2) ?>%</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr><th width="40%">Commission Amount:</th><td class="fw-bold">₹<?= number_format($invoice['agent_commission_amount'], 2) ?></td></tr>
                        <tr><th>Status:</th>
                            <td>
                                <?php if ($invoice['agent_commission_status'] == 'Paid'): ?>
                                    <span class="badge text-bg-success"><i class="fas fa-check"></i> Paid</span>
                                <?php else: ?>
                                    <span class="badge text-bg-warning"><i class="fas fa-clock"></i> Unpaid</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if ($invoice['agent_commission_status'] == 'Paid' && $invoice['agent_commission_paid_at']): ?>
                        <tr><th>Paid On:</th><td><?= date('d/m/Y', strtotime($invoice['agent_commission_paid_at'])) ?></td></tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Line Items -->
    <div class="card card-outline card-secondary">
        <div class="card-header">
            <h3 class="card-title">Line Items</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="5%">#</th>
                        <th width="40%">Item & Description</th>
                        <th>HSN Code</th>
                        <th>Quantity</th>
                        <th>Rate</th>
                        <th>GST %</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoice['items'] as $index => $item): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td>
                                <strong><?= esc(!empty($item['product_name']) ? $item['product_name'] : ($item['description'] ?? '-')) ?></strong>
                                <?php if (!empty($item['description']) && !empty($item['product_name']) && $item['description'] !== $item['product_name']): ?>
                                    <br><small class="text-muted"><?= esc($item['description']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= esc(!empty($item['hsn_code']) ? $item['hsn_code'] : ($item['product_hsn'] ?? '-')) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td>₹<?= number_format($item['rate'], 2) ?></td>
                            <td><?= $item['tax_percentage'] ?>%</td>
                            <td class="text-end">₹<?= number_format($item['amount'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-secondary">
                    <tr><td colspan="6" class="text-end fw-bold">Subtotal:</td><td class="text-end">₹<?= number_format($invoice['subtotal'], 2) ?></td></tr>
                    
                    <?php if ($invoice['discount_amount'] > 0): ?>
                        <?php 
                        $actualDiscount = ($invoice['discount_type'] == 'Percentage') ? ($invoice['subtotal'] * $invoice['discount_amount'] / 100) : $invoice['discount_amount']; 
                        ?>
                        <tr><td colspan="6" class="text-end text-danger">Discount (<?= $invoice['discount_type'] == 'Percentage' ? esc($invoice['discount_amount']).'%' : 'Fixed' ?>):</td><td class="text-end text-danger">-₹<?= number_format($actualDiscount, 2) ?></td></tr>
                    <?php endif; ?>

                    <?php
                    // Group taxes by percentage (mirroring BILL logic but more robust)
                    $cgstGroups = [];
                    $sgstGroups = [];
                    $igstGroups = [];
                    
                    $subtotal = $invoice['subtotal'];
                    $discountAmt = ($invoice['discount_type'] == 'Percentage') ? ($subtotal * $invoice['discount_amount'] / 100) : $invoice['discount_amount'];

                    foreach ($invoice['items'] as $item) {
                        $itemAmt = $item['quantity'] * $item['rate'];
                        $itemDiscount = ($subtotal > 0) ? ($itemAmt / $subtotal * $discountAmt) : 0;
                        $taxableVal = $itemAmt - $itemDiscount;
                        
                        // CGST
                        $cr = (float)($item['cgst_rate'] ?? 0);
                        if ($cr > 0) {
                            if (!isset($cgstGroups["$cr"])) $cgstGroups["$cr"] = 0;
                            $cgstGroups["$cr"] += ($taxableVal * $cr) / 100;
                        }
                        
                        // SGST
                        $sr = (float)($item['sgst_rate'] ?? 0);
                        if ($sr > 0) {
                            if (!isset($sgstGroups["$sr"])) $sgstGroups["$sr"] = 0;
                            $sgstGroups["$sr"] += ($taxableVal * $sr) / 100;
                        }
                        
                        // IGST
                        $ir = (float)($item['igst_rate'] ?? 0);
                        if ($ir > 0) {
                            if (!isset($igstGroups["$ir"])) $igstGroups["$ir"] = 0;
                            $igstGroups["$ir"] += ($taxableVal * $ir) / 100;
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
                    <tr class="table-info"><td colspan="6" class="text-end fw-bold">Balance Due:</td><td class="text-end fw-bold">₹<?= number_format($invoice['balance'], 2) ?></td></tr>
                </tfoot>
            </table>
            </div>
        </div>
    </div>

    <!-- Receipts / Payment History -->
    <?php if (!empty($invoice['payments'])): ?>
    <div class="card card-outline card-success mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0"><i class="fas fa-receipt me-2"></i>Receipt History (<?= count($invoice['payments']) ?>)</h3>
            <span class="badge bg-success-subtle text-success border border-success-subtle">
                Total Receipts: ₹<?= number_format(array_sum(array_column($invoice['payments'], 'amount')), 2) ?>
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Receipt #</th>
                            <th>Date</th>
                            <th>Mode</th>
                            <th class="text-end">Cash/Bank Amount</th>
                            <th class="text-end">Deductions</th>
                            <th class="text-end">Total Settlement</th>
                            <th>Reference</th>
                            <th>Notes</th>
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
                                    if ($ded > 0): 
                                    ?>
                                        ₹<?= number_format($ded, 2) ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td class="text-end fw-bold text-success">₹<?= number_format($payment['amount'] + $ded, 2) ?></td>
                                <td><?= esc($payment['reference_number']) ?: '-' ?></td>
                                <td class="small text-muted"><?= esc($payment['notes']) ?: '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Credit Note / Return History -->
    <?php if (!empty($invoice['credit_notes'])): ?>
    <div class="card card-outline card-warning mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0"><i class="fas fa-undo me-2"></i>Credit Note / Sales Return History (<?= count($invoice['credit_notes']) ?>)</h3>
            <span class="badge bg-warning-subtle text-dark border border-warning-subtle">
                Total Credited: ₹<?= number_format(array_sum(array_column($invoice['credit_notes'], 'total_amount')), 2) ?>
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Credit Note #</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Reason</th>
                            <th class="text-end">Subtotal</th>
                            <th class="text-end">Tax Amount</th>
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
                                    <span class="badge bg-<?= $cn['status'] == 'Closed' ? 'success' : ($cn['status'] == 'Void' ? 'danger' : 'warning text-dark') ?>">
                                        <?= esc($cn['status']) ?>
                                    </span>
                                </td>
                                <td><?= esc($cn['reason']) ?: '<span class="text-muted">-</span>' ?></td>
                                <td class="text-end">₹<?= number_format($cn['subtotal'] ?? 0, 2) ?></td>
                                <td class="text-end">₹<?= number_format($cn['tax_amount'] ?? 0, 2) ?></td>
                                <td class="text-end fw-bold text-danger">₹<?= number_format($cn['total_amount'], 2) ?></td>
                                <td class="text-center">
                                    <a href="<?= site_url('sales_returns/view/' . $cn['id']) ?>?mode=single" class="btn btn-xs btn-outline-primary py-0 px-2" title="View Credit Note">
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
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card card-outline card-info h-100">
                <div class="card-header"><h3 class="card-title">Notes</h3></div>
                <div class="card-body"><?= nl2br(esc($invoice['notes'])) ?: 'No notes provided.' ?></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-outline card-info h-100">
                <div class="card-header"><h3 class="card-title">Terms & Conditions</h3></div>
                <div class="card-body"><?= nl2br(esc($invoice['terms'])) ?: 'Standard terms apply.' ?></div>
            </div>
        </div>
    </div>

    <!-- Delivery History Timeline -->
    <div class="card card-outline card-info mb-3">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-history me-1"></i> Delivery History</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="timeline-v2">
                <?php if (empty($history)): ?>
                    <p class="text-muted text-center p-3 mb-0">No tracking history available yet.</p>
                <?php else: ?>
                    <?php foreach ($history as $h): ?>
                        <div class="timeline-item">
                            <div class="timeline-date"><?= date('d/m/Y H:i', strtotime($h['created_at'])) ?> by <?= esc($h['user_name'] ?: 'System') ?></div>
                            <div class="timeline-content">
                                <span class="timeline-status text-primary"><?= esc($h['status']) ?></span>
                                <div class="mt-1"><?= esc($h['description']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <style>
    .timeline-v2 { position: relative; padding: 10px 0; }
    .timeline-item { padding: 10px 0 10px 40px; position: relative; border-left: 2px solid #e9ecef; margin-left: 20px; }
    .timeline-item::before { content: ''; position: absolute; left: -9px; top: 15px; width: 16px; height: 16px; border-radius: 50%; background: #007bff; border: 3px solid #fff; }
    .timeline-date { font-size: 0.85rem; color: #6c757d; margin-bottom: 5px; }
    .timeline-content { background: #f8f9fa; padding: 10px 15px; border-radius: 8px; }
    .timeline-status { font-weight: 600; color: #343a40; }
    </style>
</div>
<?= $this->endSection() ?>
