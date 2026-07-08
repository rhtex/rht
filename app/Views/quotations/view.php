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
                <li class="breadcrumb-item"><a href="<?= base_url('quotations') ?>">Quotations</a></li>
                <li class="breadcrumb-item active">View</li>
            </ol>
        </div>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Quotation Summary</h3>
            <div class="card-tools">
                <a href="<?= site_url('quotations/edit/' . $quotation['id']) ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>
                <a href="<?= site_url('quotations/print/' . $quotation['id']) ?>" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-print"></i> Print</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr><th width="40%">Quotation #:</th><td class="fw-bold"><?= esc($quotation['quotation_number']) ?></td></tr>
                        <tr><th>Customer:</th><td><?= esc($quotation['customer_name']) ?></td></tr>
                        <tr><th>P.O number:</th><td><?= esc($quotation['reference_number']) ?: '-' ?></td></tr>
                        <tr><th>Status:</th>
                            <td>
                                <?php
                                $colors = ['Draft'=>'secondary','Sent'=>'info','Accepted'=>'success','Declined'=>'danger','Invoiced'=>'primary'];
                                $color = $colors[$quotation['status']] ?? 'secondary';
                                ?>
                                <span class="badge text-bg-<?= $color ?>"><?= $quotation['status'] ?></span>
                            </td>
                        </tr>
                        <tr><th>Transport:</th><td><?= esc($quotation['transport_name']) ?: '-' ?></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr><th width="40%">Date:</th><td><?= date('d/m/Y', strtotime($quotation['quotation_date'])) ?></td></tr>
                        <?php if ($quotation['agent_id']): ?>
                            <tr><th>Agent:</th><td><?= esc($quotation['agent_name']) ?></td></tr>
                        <?php endif; ?>
                        <tr><th>Expiry Date:</th><td><?= $quotation['expiry_date'] ? date('d/m/Y', strtotime($quotation['expiry_date'])) : '-' ?></td></tr>
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
                    <?php foreach ($quotation['items'] as $index => $item): ?>
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
                    <tr><td colspan="5" class="text-end">Subtotal:</td><td class="text-end">₹<?= number_format($quotation['subtotal'], 2) ?></td></tr>
                    <?php if ($quotation['discount_amount'] > 0): ?>
                        <?php $actualDisc = ($quotation['discount_type'] == 'Percentage') ? ($quotation['subtotal'] * $quotation['discount_amount'] / 100) : $quotation['discount_amount']; ?>
                        <tr><td colspan="5" class="text-end text-danger">Discount (<?= $quotation['discount_type'] ?>):</td><td class="text-end text-danger">-₹<?= number_format($actualDisc, 2) ?></td></tr>
                    <?php endif; ?>
                    <tr><td colspan="5" class="text-end">Tax:</td><td class="text-end">₹<?= number_format($quotation['tax_amount'], 2) ?></td></tr>
                    <?php if ($quotation['shipping_charge'] > 0): ?>
                        <tr><td colspan="5" class="text-end">Shipping:</td><td class="text-end">₹<?= number_format($quotation['shipping_charge'], 2) ?></td></tr>
                    <?php endif; ?>
                    <tr class="table-primary"><td colspan="5" class="text-end fw-bold fs-5">Total:</td><td class="text-end fw-bold fs-5">₹<?= number_format($quotation['total_amount'], 2) ?></td></tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-info">
                <div class="card-header"><h3 class="card-title">Notes</h3></div>
                <div class="card-body"><?= nl2br(esc($quotation['notes'])) ?: 'None' ?></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-outline card-info">
                <div class="card-header"><h3 class="card-title">Terms</h3></div>
                <div class="card-body"><?= nl2br(esc($quotation['terms'])) ?: 'None' ?></div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
