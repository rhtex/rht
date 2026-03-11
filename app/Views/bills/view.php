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
                <li class="breadcrumb-item"><a href="<?= base_url('bills') ?>">Bills</a></li>
                <li class="breadcrumb-item active">View</li>
            </ol>
        </div>
    </div>

    <!-- Bill Header -->
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Bill Information</h3>
            <div class="card-tools">
                <?php if ($bill['status'] == 'Draft'): ?>
                    <a href="<?= site_url('bills/mark-open/' . $bill['id']) ?>" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to mark this bill as Open?');">
                        <i class="fas fa-check-circle"></i> Mark as Open
                    </a>
                <?php endif; ?>
                <?php if ($bill['status'] != 'Void' && $bill['status'] != 'Paid'): ?>
                    <a href="<?= site_url('bills/edit/' . $bill['id']) ?>" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                <?php endif; ?>
                <?php if ($bill['balance'] > 0 && $bill['status'] != 'Void'): ?>
                    <a href="<?= site_url('bills/payment/' . $bill['id']) ?>" class="btn btn-sm btn-success">
                        <i class="fas fa-money-bill"></i> Record Payment
                    </a>
                <?php endif; ?>
                <a href="<?= site_url('bills/print/' . $bill['id']) ?>" target="_blank" class="btn btn-sm btn-info">
                    <i class="fas fa-print"></i> Print
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr><th width="40%">Bill Number:</th><td><?= esc($bill['bill_number']) ?></td></tr>
                        <tr><th>Vendor:</th><td><?= esc($bill['vendor_name']) ?></td></tr>
                        <tr><th>Reference Number:</th><td><?= esc($bill['reference_number']) ?: '-' ?></td></tr>
                        <tr><th>Status:</th><td><span class="badge text-bg-primary"><?= $bill['status'] ?></span></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr><th width="40%">Bill Date:</th><td><?= date('d/m/Y', strtotime($bill['bill_date'])) ?></td></tr>
                        <tr><th>Due Date:</th><td><?= date('d/m/Y', strtotime($bill['due_date'])) ?></td></tr>
                        <tr><th>Zoho Sync:</th>
                            <td>
                                <?php if ($bill['zoho_sync_status'] == 'Synced'): ?>
                                    <span class="badge text-bg-success"><i class="fas fa-check"></i> Synced</span>
                                <?php else: ?>
                                    <span class="badge text-bg-warning"><i class="fas fa-clock"></i> <?= $bill['zoho_sync_status'] ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Line Items -->
    <div class="card card-outline card-secondary">
        <div class="card-header">
            <h3 class="card-title">Line Items</h3>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Description</th>
                        <th>HSN Code</th>
                        <th>Quantity</th>
                        <th>Rate</th>
                        <th>GST %</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bill['items'] as $item): ?>
                        <tr>
                            <td><?= esc($item['description']) ?></td>
                            <td><?= esc($item['hsn_code']) ?: '-' ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td>₹<?= number_format($item['rate'], 2) ?></td>
                            <td><?= $item['tax_percentage'] ?>%</td>
                            <td>₹<?= number_format($item['amount'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-secondary">
                    <tr><td colspan="5" class="text-end fw-bold">Subtotal:</td><td>₹<?= number_format($bill['subtotal'], 2) ?></td></tr>
                    <?php if (($bill['discount_amount'] ?? 0) > 0): ?>
                        <tr><td colspan="5" class="text-end text-danger">Discount (<?= ($bill['discount_type'] ?? 'Amount') == 'Percentage' ? ($bill['discount_amount'] ?? 0) . '%' : 'Fixed' ?>):</td><td class="text-danger">-₹<?= number_format(($bill['discount_type'] ?? 'Amount') == 'Percentage' ? ($bill['subtotal'] * ($bill['discount_amount'] ?? 0) / 100) : ($bill['discount_amount'] ?? 0), 2) ?></td></tr>
                    <?php endif; ?>
                    <?php
                    // Group taxes by percentage (calculated after discount)
                    $subtotal = $bill['subtotal'];
                    $discountAmount = $bill['discount_amount'] ?? 0;
                    $discountType = $bill['discount_type'] ?? 'Amount';
                    $totalDiscount = ($discountType == 'Percentage') ? ($subtotal * $discountAmount / 100) : $discountAmount;

                    $taxGroups = [];
                    foreach ($bill['items'] as $item) {
                        $rate = (float)$item['tax_percentage'];
                        if ($rate > 0) {
                            $itemAmount = $item['quantity'] * $item['rate'];
                            
                            // Apportion discount proportional to item value
                            $itemDiscount = ($subtotal > 0) ? ($itemAmount / $subtotal * $totalDiscount) : 0;
                            $taxableValue = $itemAmount - $itemDiscount;
                            $taxAmount = ($taxableValue * $rate) / 100;
                            
                            if (!isset($taxGroups[$rate])) {
                                $taxGroups[$rate] = 0;
                            }
                            $taxGroups[$rate] += $taxAmount;
                        }
                    }
                    ksort($taxGroups);
                    
                    $isInterState = ($bill['igst_amount'] ?? 0) > 0;
                    ?>

                    <?php foreach ($taxGroups as $rate => $totalTax): ?>
                        <?php if ($isInterState): ?>
                            <tr><td colspan="5" class="text-end">IGST (<?= $rate + 0 ?>%):</td><td>₹<?= number_format($totalTax, 2) ?></td></tr>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-end">CGST (<?= ($rate / 2) + 0 ?>%):</td><td>₹<?= number_format($totalTax / 2, 2) ?></td></tr>
                            <tr><td colspan="5" class="text-end">SGST (<?= ($rate / 2) + 0 ?>%):</td><td>₹<?= number_format($totalTax / 2, 2) ?></td></tr>
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <?php if (($bill['shipping_charge'] ?? 0) > 0): ?>
                        <tr><td colspan="5" class="text-end">Shipping Charges:</td><td>₹<?= number_format($bill['shipping_charge'] ?? 0, 2) ?></td></tr>
                    <?php endif; ?>

                    <?php if (($bill['roundoff_amount'] ?? 0) != 0): ?>
                        <tr><td colspan="5" class="text-end">Roundoff:</td><td>₹<?= number_format($bill['roundoff_amount'] ?? 0, 2) ?></td></tr>
                    <?php endif; ?>
                    <tr class="table-primary"><td colspan="5" class="text-end fw-bold fs-5">Total:</td><td class="fw-bold fs-5">₹<?= number_format($bill['total_amount'], 2) ?></td></tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Payment History -->
    <?php if (!empty($bill['payments'])): ?>
    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title">Payment History</h3>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Payment #</th>
                        <th>Date</th>
                        <th>Mode</th>
                        <th>Paid Amount</th>
                        <th>Deductions</th>
                        <th>Total Settlement</th>
                        <th>Reference</th>
                        <th>Zoho Sync</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bill['payments'] as $payment): ?>
                        <tr>
                            <td><?= esc($payment['payment_number']) ?></td>
                            <td><?= date('d/m/Y', strtotime($payment['payment_date'])) ?></td>
                            <td><?= $payment['payment_mode'] ?></td>
                            <td>₹<?= number_format($payment['amount'], 2) ?></td>
                            <td class="text-danger">
                                <?php 
                                $deductions = ($payment['discount_amount'] ?? 0) + ($payment['mahimai_amount'] ?? 0) + ($payment['postal_charges'] ?? 0);
                                if ($deductions > 0): 
                                ?>
                                    ₹<?= number_format($deductions, 2) ?>
                                    <small class="d-block text-muted">
                                        (D: <?= number_format($payment['discount_amount'] ?? 0, 2) ?>, 
                                        M: <?= number_format($payment['mahimai_amount'] ?? 0, 2) ?>, 
                                        P: <?= number_format($payment['postal_charges'] ?? 0, 2) ?>)
                                    </small>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold">₹<?= number_format($payment['amount'] + ($deductions ?? 0), 2) ?></td>
                            <td><?= esc($payment['reference_number']) ?: '-' ?></td>
                            <td>
                                <?php if ($payment['zoho_sync_status'] == 'Synced'): ?>
                                    <span class="badge text-bg-success"><i class="fas fa-check"></i></span>
                                <?php else: ?>
                                    <span class="badge text-bg-warning"><?= $payment['zoho_sync_status'] ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
