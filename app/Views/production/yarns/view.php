<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>View Yarn Details<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>View Yarn Details</h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('production/yarns') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-info-circle me-1"></i> Yarn Stock Information</h3>
            <div class="card-tools">
                <?php if(in_array('weaver.edit', session('permissions') ?? [])): ?>
                <a href="<?= site_url('production/yarns/edit/' . $yarn['id']) ?>" class="btn btn-sm btn-warning">
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
                            <th width="35%">Yarn Name</th>
                            <td><strong><?= esc($yarn['name']) ?></strong></td>
                        </tr>
                        <tr>
                            <th>Yarn Count</th>
                            <td><span class="badge bg-secondary"><?= esc($yarn['yarn_count']) ?></span></td>
                        </tr>
                        <tr>
                            <th>Yarn Type</th>
                            <td>
                                <span class="badge bg-<?= $yarn['yarn_type'] === 'Dyed' ? 'info' : 'light text-dark border' ?>">
                                    <?= esc($yarn['yarn_type']) ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Color</th>
                            <td><?= esc($yarn['color'] ?: '-') ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th width="35%">Brand / Mill</th>
                            <td><?= esc($yarn['brand'] ?: '-') ?></td>
                        </tr>
                        <tr>
                            <th>Total Stock (KGs)</th>
                            <td><strong class="text-primary text-lg"><?= number_format($yarn['stock_kg'], 2) ?> KGs</strong></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge bg-<?= $yarn['status'] === 'active' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($yarn['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Last Updated</th>
                            <td><?= esc($yarn['updated_at']) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
