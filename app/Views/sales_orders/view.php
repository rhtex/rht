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
                <li class="breadcrumb-item"><a href="<?= base_url('sales_orders') ?>">Sales Orders</a></li>
                <li class="breadcrumb-item active">View</li>
            </ol>
        </div>
    </div>

    <!-- Order Header Card -->
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Order Information</h3>
            <div class="card-tools">
                <?php if (!empty($order['converted_to_invoice_id'])): ?>
                    <a href="<?= site_url('invoices/view/' . $order['converted_to_invoice_id']) ?>" class="btn btn-sm btn-success">
                        <i class="fas fa-file-invoice"></i> View Invoice <?= !empty($order['converted_invoice_number']) ? '(' . esc($order['converted_invoice_number']) . ')' : '' ?>
                    </a>
                <?php else: ?>
                    <a href="<?= site_url('sales_orders/convert/' . $order['id']) ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-file-invoice-dollar"></i> Convert to Invoice
                    </a>
                    <a href="<?= site_url('sales_orders/edit/' . $order['id']) ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>
                <?php endif; ?>
                <a href="<?= site_url('sales_orders/print/' . $order['id']) ?>" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-print"></i> Print</a>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($order['converted_to_invoice_id'])): ?>
                <div class="alert alert-danger d-flex align-items-center py-2 mb-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <span>This Sales Order has <strong>not yet been converted</strong> to an Invoice.</span>
                    <a href="<?= site_url('sales_orders/convert/' . $order['id']) ?>" class="btn btn-sm btn-primary ms-auto">
                        <i class="fas fa-file-invoice-dollar"></i> Convert Now
                    </a>
                </div>
            <?php else: ?>
                <div class="alert alert-success d-flex align-items-center py-2 mb-3">
                    <i class="fas fa-check-circle me-2"></i>
                    <span>This Sales Order has been converted to <strong>Invoice <?= esc($order['converted_invoice_number'] ?? '#' . $order['converted_to_invoice_id']) ?></strong>. Once converted, it cannot be converted again.</span>
                    <a href="<?= site_url('invoices/view/' . $order['converted_to_invoice_id']) ?>" class="btn btn-sm btn-success ms-auto">
                        <i class="fas fa-file-invoice me-1"></i> View Invoice &rarr;
                    </a>
                </div>
            <?php endif; ?>
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr><th width="40%">Order Number:</th><td class="fw-bold"><?= esc($order['sales_order_number']) ?></td></tr>
                        <tr><th>Customer:</th><td><?= esc($order['customer_name']) ?></td></tr>
                        <tr><th>P.O number:</th><td><?= esc($order['reference_number']) ?: '-' ?></td></tr>
                        <?php if (!empty($order['quotation_id'])): ?>
                            <tr><th>Source Quotation:</th><td><a href="<?= site_url('quotations/view/' . $order['quotation_id']) ?>" class="badge bg-info text-decoration-none"><i class="fas fa-file-invoice me-1"></i> <?= esc($order['source_quotation_number'] ?? 'QTN #' . $order['quotation_id']) ?></a></td></tr>
                        <?php endif; ?>
                        <?php if (!empty($order['selection_type'])): ?>
                            <tr><th>Selection Type:</th><td><span class="badge bg-secondary"><?= esc($order['selection_type']) ?></span></td></tr>
                        <?php endif; ?>
                        <tr><th>Status:</th>
                            <td>
                                <?php
                                $colors = ['Draft'=>'secondary','Confirmed'=>'primary','Closed'=>'success','Void'=>'dark'];
                                $color = $colors[$order['status']] ?? 'secondary';
                                ?>
                                <span class="badge text-bg-<?= $color ?>"><?= $order['status'] ?></span>
                            </td>
                        </tr>
                        <tr><th>Transport:</th><td><?= esc($order['transport_name']) ?: '-' ?></td></tr>
                        <tr><th>Waybill / LR:</th><td><?= esc($order['waybill_number']) ?: '-' ?></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr><th width="40%">Order Date:</th><td><?= date('d/m/Y', strtotime($order['order_date'])) ?></td></tr>
                        <tr><th>Expected Shipment:</th><td><?= $order['shipment_date'] ? date('d/m/Y', strtotime($order['shipment_date'])) : '-' ?></td></tr>
                        <?php if ($order['agent_id']): ?>
                            <tr><th>Agent:</th><td><?= esc($order['agent_name'] ?? 'Assigned') ?></td></tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Agent Commission Information -->
    <?php if (!empty($order['agent_id']) && !empty($agent)): ?>
    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-tie"></i> Agent Commission</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr><th width="40%">Agent Name:</th><td><?= esc($agent['agent_name']) ?></td></tr>
                        <tr><th>Phone:</th><td><?= esc($agent['phone_number']) ?></td></tr>
                        <tr><th>Commission Rate:</th><td><?= number_format($order['agent_commission_percent'] ?? 0, 2) ?>%</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr><th width="40%">Commission Amount:</th><td class="fw-bold">₹<?= number_format($order['agent_commission_amount'] ?? 0, 2) ?></td></tr>
                        <tr><th>Status:</th>
                            <td>
                                <span class="badge text-bg-<?= ($order['agent_commission_status'] ?? '') == 'Paid' ? 'success' : 'warning' ?>">
                                    <?= esc($order['agent_commission_status'] ?? 'Unpaid') ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Line Items -->
    <div class="card card-outline card-secondary">
        <div class="card-header"><h3 class="card-title">Line Items</h3></div>
        <div class="card-body p-0">
            <table class="table table-striped">
                <thead class="table-light">
                    <tr>
                        <th width="5%">#</th>
                        <th width="40%">Description</th>
                        <th>HSN Code</th>
                        <th>Quantity</th>
                        <th>Rate</th>
                        <th>GST %</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order['items'] as $index => $item): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= esc($item['description']) ?></td>
                            <td><?= esc($item['hsn_code']) ?: '-' ?></td>
                            <td><?= $item['quantity'] + 0 ?></td>
                            <td>₹<?= number_format($item['rate'], 2) ?></td>
                            <td><?= $item['tax_percentage'] + 0 ?>%</td>
                            <td class="text-end">₹<?= number_format($item['amount'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-secondary">
                    <tr><td colspan="6" class="text-end fw-bold">Subtotal:</td><td class="text-end">₹<?= number_format($order['subtotal'], 2) ?></td></tr>
                    <?php if ($order['discount_amount'] > 0): ?>
                        <?php $actualDisc = ($order['discount_type'] == 'Percentage') ? ($order['subtotal'] * $order['discount_amount'] / 100) : $order['discount_amount']; ?>
                        <tr><td colspan="6" class="text-end text-danger">Discount (<?= $order['discount_type'] == 'Percentage' ? esc($order['discount_amount']).'%' : 'Fixed' ?>):</td><td class="text-end text-danger">-₹<?= number_format($actualDisc, 2) ?></td></tr>
                    <?php endif; ?>
                    
                    <?php
                    // Tax breakdown by slab
                    $taxGroups = [];
                    $subtotal = $order['subtotal'];
                    $discountAmt = ($order['discount_type'] == 'Percentage') ? ($subtotal * $order['discount_amount'] / 100) : $order['discount_amount'];

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

    <!-- Notes & Terms -->
    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-info">
                <div class="card-header"><h3 class="card-title">Notes</h3></div>
                <div class="card-body"><?= nl2br(esc($order['notes'])) ?: 'No notes provided.' ?></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-outline card-info">
                <div class="card-header"><h3 class="card-title">Terms & Conditions</h3></div>
                <div class="card-body"><?= nl2br(esc($order['terms'])) ?: 'Standard terms apply.' ?></div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
