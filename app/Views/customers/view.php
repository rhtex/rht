<?= $this->extend('layouts/master') ?>
<?= $this->section('title') ?><?= esc($customer['name']) ?> - Customer Details<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0 fw-bold"><?= esc($customer['name']) ?></h1>
        <span class="badge bg-<?= $customer['status'] == 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($customer['status']) ?></span>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('customers') ?>">Customers</a></li>
            <li class="breadcrumb-item active">View</li>
        </ol>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <!-- Customer Info Card -->
    <div class="col-md-4">
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center mb-3">
                    <div class="avatar-circle mx-auto bg-primary text-white d-flex align-items-center justify-content-center rounded-circle" style="width: 80px; height: 80px; font-size: 2rem;">
                        <?= strtoupper(substr($customer['name'], 0, 1)) ?>
                    </div>
                </div>
                <h3 class="profile-username text-center"><?= esc($customer['name']) ?></h3>
                <p class="text-muted text-center"><?= esc($customer['email']) ?></p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Phone</b> <a class="float-end text-decoration-none"><?= esc($customer['phone']) ?></a>
                    </li>
                    <li class="list-group-item">
                        <b>GSTIN</b> <a class="float-end text-decoration-none"><?= esc($customer['gstin'] ?: 'N/A') ?></a>
                    </li>
                    <li class="list-group-item">
                        <b>PAN</b> <a class="float-end text-decoration-none"><?= esc($customer['pan_number'] ?: 'N/A') ?></a>
                    </li>
                </ul>
                
                <div class="d-grid gap-2">
                    <a href="<?= site_url('customers/edit/'.$customer['id']) ?>" class="btn btn-primary btn-block"><b>Edit Profile</b></a>
                </div>
            </div>
        </div>

        <!-- Address Card -->
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">Addresses</h3>
            </div>
            <div class="card-body">
                <strong><i class="fas fa-map-marker-alt mr-1"></i> Billing Address</strong>
                <p class="text-muted">
                    <?php if($billing_address): ?>
                        <?= esc($billing_address['address_line1']) ?><br>
                        <?= $billing_address['address_line2'] ? esc($billing_address['address_line2']) . '<br>' : '' ?>
                        <?= esc($billing_address['city']) ?>, <?= isset($billing_state) ? esc($billing_state['name']) : '' ?> - <?= esc($billing_address['pincode']) ?>
                    <?php else: ?>
                        <span class="text-danger">Not provided</span>
                    <?php endif; ?>
                </p>

                <hr>

                <strong><i class="fas fa-truck mr-1"></i> Shipping Address</strong>
                <p class="text-muted">
                    <?php if($shipping_address): ?>
                        <?= esc($shipping_address['address_line1']) ?><br>
                        <?= $shipping_address['address_line2'] ? esc($shipping_address['address_line2']) . '<br>' : '' ?>
                        <?= esc($shipping_address['city']) ?>, <?= isset($shipping_state) ? esc($shipping_state['name']) : '' ?> - <?= esc($shipping_address['pincode']) ?>
                    <?php else: ?>
                        <span class="text-muted">Same as Billing</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Transactions Tabs -->
    <div class="col-md-8">
        <div class="card card-primary card-tabs">
            <div class="card-header p-0 pt-1">
                <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tabs-invoices-tab" data-bs-toggle="pill" href="#tabs-invoices" role="tab" aria-controls="tabs-invoices" aria-selected="true">Invoices</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tabs-orders-tab" data-bs-toggle="pill" href="#tabs-orders" role="tab" aria-controls="tabs-orders" aria-selected="false">Sales Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tabs-quotes-tab" data-bs-toggle="pill" href="#tabs-quotes" role="tab" aria-controls="tabs-quotes" aria-selected="false">Quotations</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tabs-payments-tab" data-bs-toggle="pill" href="#tabs-payments" role="tab" aria-controls="tabs-payments" aria-selected="false">Payments</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="custom-tabs-one-tabContent">
                    
                    <!-- Invoices Tab -->
                    <div class="tab-pane fade show active" id="tabs-invoices" role="tabpanel" aria-labelledby="tabs-invoices-tab">
                        <?php if(empty($invoices)): ?>
                            <div class="text-center py-4 text-muted">No invoices found for this customer.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead>
                                        <tr>
                                            <th>Invoice #</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($invoices as $inv): ?>
                                            <tr>
                                                <td><a href="<?= site_url('invoices/view/'.$inv['id']) ?>" class="fw-bold text-dark"><?= $inv['invoice_number'] ?></a></td>
                                                <td><?= date('d M Y', strtotime($inv['invoice_date'])) ?></td>
                                                <td class="fw-bold">₹<?= number_format($inv['total_amount'], 2) ?></td>
                                                <td>
                                                    <?php 
                                                        $badge = 'secondary';
                                                        if($inv['status'] == 'Paid') $badge = 'success';
                                                        elseif($inv['status'] == 'Partially Paid') $badge = 'warning';
                                                        elseif($inv['status'] == 'Overdue') $badge = 'danger';
                                                        elseif($inv['status'] == 'Sent') $badge = 'info';
                                                    ?>
                                                    <span class="badge bg-<?= $badge ?>"><?= $inv['status'] ?></span>
                                                </td>
                                                <td>
                                                    <a href="<?= site_url('invoices/view/'.$inv['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Sales Orders Tab -->
                    <div class="tab-pane fade" id="tabs-orders" role="tabpanel" aria-labelledby="tabs-orders-tab">
                         <?php if(empty($sales_orders)): ?>
                            <div class="text-center py-4 text-muted">No sales orders found.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead>
                                        <tr>
                                            <th>Order #</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($sales_orders as $order): ?>
                                            <tr>
                                                <td><a href="<?= site_url('sales_orders/view/'.$order['id']) ?>" class="fw-bold text-dark"><?= $order['order_number'] ?></a></td>
                                                <td><?= date('d M Y', strtotime($order['order_date'])) ?></td>
                                                <td class="fw-bold">₹<?= number_format($order['total_amount'], 2) ?></td>
                                                <td><span class="badge bg-info"><?= $order['status'] ?></span></td>
                                                <td>
                                                    <a href="<?= site_url('sales_orders/view/'.$order['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Quotations Tab -->
                    <div class="tab-pane fade" id="tabs-quotes" role="tabpanel" aria-labelledby="tabs-quotes-tab">
                        <?php if(empty($quotations)): ?>
                            <div class="text-center py-4 text-muted">No quotations found.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead>
                                        <tr>
                                            <th>Quote #</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($quotations as $quote): ?>
                                            <tr>
                                                <td><a href="<?= site_url('quotations/view/'.$quote['id']) ?>" class="fw-bold text-dark"><?= $quote['quotation_number'] ?></a></td>
                                                <td><?= date('d M Y', strtotime($quote['quotation_date'])) ?></td>
                                                <td class="fw-bold">₹<?= number_format($quote['total_amount'], 2) ?></td>
                                                <td><span class="badge bg-secondary"><?= $quote['status'] ?></span></td>
                                                <td>
                                                    <a href="<?= site_url('quotations/view/'.$quote['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Payments Tab -->
                    <div class="tab-pane fade" id="tabs-payments" role="tabpanel" aria-labelledby="tabs-payments-tab">
                        <?php if(empty($payments)): ?>
                            <div class="text-center py-4 text-muted">No payments recorded.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead>
                                        <tr>
                                            <th>Ref #</th>
                                            <th>Invoice #</th>
                                            <th>Date</th>
                                            <th>Mode</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($payments as $pay): ?>
                                            <tr>
                                                <td><?= $pay['reference_number'] ?: '-' ?></td>
                                                <td><a href="<?= site_url('invoices/view/'.$pay['invoice_id']) ?>"><?= $pay['invoice_number'] ?></a></td>
                                                <td><?= date('d M Y', strtotime($pay['payment_date'])) ?></td>
                                                <td><?= ucfirst($pay['payment_mode']) ?></td>
                                                <td class="fw-bold text-success">₹<?= number_format($pay['amount'], 2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
            <!-- /.card -->
        </div>
    </div>
</div>
<?= $this->endSection() ?>
