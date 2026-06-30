<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>View Vendor Details<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>View Vendor Details</h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('production/vendors') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-info-circle me-1"></i> Vendor Information</h3>
            <div class="card-tools">
                <?php if(in_array('weaver.edit', session('permissions') ?? [])): ?>
                <a href="<?= site_url('production/vendors/edit/' . $vendor['id']) ?>" class="btn btn-sm btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th width="35%">Vendor Name</th>
                            <td><strong><?= esc($vendor['name']) ?></strong></td>
                        </tr>
                        <tr>
                            <th>Business Type</th>
                            <td><?= esc($vendor['business_type'] ?: '-') ?></td>
                        </tr>
                        <tr>
                            <th>GST Number</th>
                            <td><?= esc($vendor['gst_number'] ?: '-') ?></td>
                        </tr>
                        <tr>
                            <th>PAN Number</th>
                            <td><?= esc($vendor['pan_number'] ?: '-') ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th width="35%">Phone Number</th>
                            <td><?= esc($vendor['phone'] ?: '-') ?></td>
                        </tr>
                        <tr>
                            <th>Location</th>
                            <td>
                                <?php if (!empty($vendor['location'])) : ?>
                                    <a href="<?= esc($vendor['location']) ?>" target="_blank" class="btn btn-sm btn-primary">
                                        <i class="fas fa-map-marker-alt"></i> Open Location Link
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge bg-<?= $vendor['status'] === 'active' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($vendor['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Registered On</th>
                            <td><?= esc($vendor['created_at']) ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-12 mt-3">
                    <div class="card card-outline card-secondary">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Address</h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-0"><?= nl2br(esc($vendor['address'] ?: 'No address specified.')) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
