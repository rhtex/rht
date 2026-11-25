<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Predefined Yarns<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">Manage Predefined Yarns</h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="btn-group">
            <a href="<?= site_url('production/yarns') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Yarns Stock</a>
            <?php if (can('yarn_master.create')): ?>
            <a href="<?= site_url('production/yarns/master/create') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Add Predefined Yarn</a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">List of Predefined Yarns (Yarn Master)</h3>
    </div>
    <div class="card-body p-0">
        <div class="erp-mobile-list p-2 bg-light">
            <?php if(!empty($masterYarns)): ?>
                <?php foreach($masterYarns as $my): ?>
                    <a href="<?= can('yarn_master.edit') ? site_url('production/yarns/master/edit/'.$my['id']) : '#' ?>" class="erp-mobile-card">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="fw-bold text-dark" style="white-space: normal; word-break: break-word; line-height: 1.35; min-width: 0; flex: 1 1 auto;">
                                <?= esc($my['name']) ?>
                            </div>
                            <span class="badge bg-<?= $my['yarn_type'] === 'Dyed' ? 'info' : 'light text-dark border' ?>" style="flex: 0 0 auto;">
                                <?= esc($my['yarn_type']) ?>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-start gap-2 mt-1">
                            <span class="text-muted small">Count: <?= esc($my['yarn_count']) ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-4 text-muted">No predefined yarns found.</div>
            <?php endif; ?>
        </div>
        <div class="table-responsive erp-desktop-wrap">
            <table class="table table-bordered table-striped mb-0 w-100" id="masterTable">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 48px;">#</th>
                        <th>ID</th>
                        <th>Yarn Name</th>
                        <th>Yarn Count</th>
                        <th>Yarn Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($masterYarns)): ?>
                        <?php foreach($masterYarns as $idx => $my): ?>
                        <tr>
                            <td class="text-center text-muted fw-semibold"><?= $idx + 1 ?></td>
                            <td><?= $my['id'] ?></td>
                            <td><strong><?= esc($my['name']) ?></strong></td>
                            <td><span class="badge bg-secondary"><?= esc($my['yarn_count']) ?></span></td>
                            <td>
                                <span class="badge bg-<?= $my['yarn_type'] === 'Dyed' ? 'info' : 'light text-dark border' ?>">
                                    <?= esc($my['yarn_type']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if (can('yarn_master.edit')): ?>
                                <a href="<?= site_url('production/yarns/master/edit/'.$my['id']) ?>" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (can('yarn_master.delete')): ?>
                                <a href="<?= site_url('production/yarns/master/delete/'.$my['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No predefined yarns found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
