<?= $this->extend('layouts/master') ?>
<?= $this->section('title') ?><?= esc($transport['transport_name']) ?> - Transport Details<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0"><?= esc($transport['transport_name']) ?></h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('transports') ?>">Transports</a></li>
            <li class="breadcrumb-item active">View</li>
        </ol>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <!-- Transport Info Card -->
    <div class="col-md-4">
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <h3 class="profile-username text-center"><?= esc($transport['transport_name']) ?></h3>
                <p class="text-muted text-center"><?= esc($transport['transport_code']) ?></p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Branch</b> <a class="float-end text-decoration-none"><?= esc($transport['branch']) ?></a>
                    </li>
                    <li class="list-group-item">
                        <b>Phone</b> <a class="float-end text-decoration-none"><?= esc($transport['branch_phone_number']) ?></a>
                    </li>
                    <li class="list-group-item">
                        <b>GST</b> <a class="float-end text-decoration-none"><?= esc($transport['branch_gst_number'] ?: 'N/A') ?></a>
                    </li>
                </ul>

                <div class="d-grid gap-2">
                    <a href="<?= site_url('transports/edit/' . $transport['id']) ?>" class="btn btn-primary"><b>Edit Transport</b></a>
                </div>
            </div>
        </div>

        <!-- Address Card -->
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">Address</h3>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    <?= nl2br(esc($transport['branch_address'])) ?><br>
                    <?= isset($state) ? esc($state['name']) : '' ?><br>
                    <?= isset($country) ? esc($country['name']) : '' ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Bookings Tabs -->
    <div class="col-md-8">
        <div class="card card-primary card-tabs">
            <div class="card-header p-0 pt-1">
                <ul class="nav nav-tabs" id="transport-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tabs-invoices-tab" data-bs-toggle="pill" href="#tabs-invoices" role="tab" aria-controls="tabs-invoices" aria-selected="true">Invoices (Sales)</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tabs-returns-tab" data-bs-toggle="pill" href="#tabs-returns" role="tab" aria-controls="tabs-returns" aria-selected="false">Return Shipments</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="transport-tabs-content">
                    
                    <!-- Invoices Tab -->
                    <div class="tab-pane fade show active" id="tabs-invoices" role="tabpanel" aria-labelledby="tabs-invoices-tab">
                        <?php if (empty($invoices)): ?>
                            <div class="text-center py-4 text-muted">No invoices found for this transport.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead>
                                        <tr>
                                            <th>Invoice #</th>
                                            <th>Date</th>
                                            <th>Customer</th>
                                            <th>Waybill #</th>
                                            <th>Packages</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($invoices as $inv): ?>
                                            <tr>
                                                <td><a href="<?= site_url('invoices/view/' . $inv['id']) ?>"><?= $inv['invoice_number'] ?></a></td>
                                                <td><?= date('d M Y', strtotime($inv['invoice_date'])) ?></td>
                                                <td><?= esc($inv['customer_name'] ?? 'N/A') ?></td>
                                                <td><?= esc($inv['waybill_number'] ?: '-') ?></td>
                                                <td><?= esc($inv['packages_count'] ?: '-') ?></td>
                                                <td class="fw-bold">₹<?= number_format($inv['total_amount'], 2) ?></td>
                                                <td><span class="badge bg-secondary"><?= $inv['status'] ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Return Shipments Tab -->
                    <div class="tab-pane fade" id="tabs-returns" role="tabpanel" aria-labelledby="tabs-returns-tab">
                        <?php if (empty($return_shipments)): ?>
                            <div class="text-center py-4 text-muted">No return shipments found for this transport.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead>
                                        <tr>
                                            <th>Ref #</th>
                                            <th>Date</th>
                                            <th>Vendor</th>
                                            <th>Waybill #</th>
                                            <th>Items</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($return_shipments as $ret): ?>
                                            <tr>
                                                <td><?= $ret['reference_no'] ?></td>
                                                <td><?= date('d M Y', strtotime($ret['return_date'])) ?></td>
                                                <td><?= esc($ret['vendor_name'] ?? 'N/A') ?></td>
                                                <td><?= esc($ret['waybill_number'] ?: '-') ?></td>
                                                <td><?= $ret['item_count'] ?></td>
                                                <td><span class="badge bg-info"><?= $ret['status'] ?></span></td>
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
