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

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Order Summary</h3>
            <div class="card-tools">
                <a href="<?= site_url('sales_orders/edit/' . $order['id']) ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>
                <a href="<?= site_url('sales_orders/print/' . $order['id']) ?>" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-print"></i> Print</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr><th width="40%">Order #:</th><td class="fw-bold"><?= esc($order['sales_order_number']) ?></td></tr>
                        <tr><th>Customer:</th><td><?= esc($order['customer_name']) ?></td></tr>
                        <tr><th>Reference:</th><td><?= esc($order['reference_number']) ?: '-' ?></td></tr>
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
                        <tr><th>Waybill:</th><td><?= esc($order['waybill_number']) ?: '-' ?></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr><th width="40%">Order Date:</th><td><?= date('d/m/Y', strtotime($order['order_date'])) ?></td></tr>
                        <?php if ($order['agent_id']): ?>
                            <tr><th>Agent:</th><td><?= esc($order['agent_name']) ?></td></tr>
                        <?php endif; ?>
                        <tr><th>Expected Shipment:</th><td><?= $order['shipment_date'] ? date('d/m/Y', strtotime($order['shipment_date'])) : '-' ?></td></tr>
                        <tr><th>Zoho Sync:</th>
                            <td>
                                <?php if ($order['zoho_sync_status'] == 'Synced'): ?>
                                    <span class="badge text-bg-success"><i class="fas fa-check"></i> Synced</span>
                                <?php else: ?>
                                    <span class="badge text-bg-warning"><?= $order['zoho_sync_status'] ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-secondary">
        <div class="card-header"><h3 class="card-title">Line Items</h3></div>
        <div class="card-body p-0">
            <table class="table table-striped">
                <thead class="table-light">
                    <tr>
                        <th width="5%">#</th>
                        <th width="40%">Description</th>
                        <th>HSN</th>
                        <th>Qty</th>
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
                    <tr><td colspan="5" class="text-end">Subtotal:</td><td class="text-end">₹<?= number_format($order['subtotal'], 2) ?></td></tr>
                    <?php if ($order['discount_amount'] > 0): ?>
                        <?php $actualDisc = ($order['discount_type'] == 'Percentage') ? ($order['subtotal'] * $order['discount_amount'] / 100) : $order['discount_amount']; ?>
                        <tr><td colspan="5" class="text-end text-danger">Discount (<?= $order['discount_type'] ?>):</td><td class="text-end text-danger">-₹<?= number_format($actualDisc, 2) ?></td></tr>
                    <?php endif; ?>
                    <tr><td colspan="5" class="text-end">Tax:</td><td class="text-end">₹<?= number_format($order['tax_amount'], 2) ?></td></tr>
                    <?php if ($order['shipping_charge'] > 0): ?>
                        <tr><td colspan="5" class="text-end">Shipping:</td><td class="text-end">₹<?= number_format($order['shipping_charge'], 2) ?></td></tr>
                    <?php endif; ?>
                    <tr class="table-primary"><td colspan="5" class="text-end fw-bold fs-5">Total:</td><td class="text-end fw-bold fs-5">₹<?= number_format($order['total_amount'], 2) ?></td></tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-info">
                <div class="card-header"><h3 class="card-title">Notes</h3></div>
                <div class="card-body"><?= nl2br(esc($order['notes'])) ?: 'None' ?></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-outline card-info">
                <div class="card-header"><h3 class="card-title">Terms</h3></div>
                <div class="card-body"><?= nl2br(esc($order['terms'])) ?: 'None' ?></div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
