<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>View Weaver Details<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>View Weaver Details</h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('production/weavers') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-info-circle me-1"></i> Weaver Information</h3>
            <div class="card-tools">
                <?php if(in_array('weaver.edit', session('permissions') ?? [])): ?>
                <a href="<?= site_url('production/weavers/edit/' . $weaver['id']) ?>" class="btn btn-sm btn-warning">
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
                            <th width="35%">Weaver Name</th>
                            <td><strong><?= esc($weaver['name']) ?></strong></td>
                        </tr>
                        <tr>
                            <th>Weaver Code</th>
                            <td><?= esc($weaver['code'] ?: '-') ?></td>
                        </tr>
                        <tr>
                            <th>Phone Number</th>
                            <td><?= esc($weaver['phone'] ?: '-') ?></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge bg-<?= $weaver['status'] === 'active' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($weaver['status']) ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th width="35%">Location</th>
                            <td>
                                <?php if (!empty($weaver['location'])) : ?>
                                    <a href="<?= esc($weaver['location']) ?>" target="_blank" class="btn btn-sm btn-primary">
                                        <i class="fas fa-map-marker-alt"></i> Open Location Link
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Address Proof</th>
                            <td>
                                <?php if (!empty($weaver['address_proof'])) : ?>
                                    <a href="<?= base_url($weaver['address_proof']) ?>" target="_blank" class="btn btn-sm btn-info">
                                        <i class="fas fa-file-alt"></i> View Address Proof
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">No proof uploaded</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Registered On</th>
                            <td><?= esc($weaver['created_at']) ?></td>
                        </tr>
                        <tr>
                            <th>Last Updated</th>
                            <td><?= esc($weaver['updated_at']) ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-12 mt-3">
                    <div class="card card-outline card-secondary">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Address</h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-0"><?= nl2br(esc($weaver['address'] ?: 'No address specified.')) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
