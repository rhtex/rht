<?= $this->extend('layouts/master') ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Module Management</h1>
    </div>
    <div class="col-sm-6">
        <a href="<?= site_url('modules/create') ?>" class="btn btn-primary float-sm-end">
            <i class="fas fa-plus"></i> Add New Module
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">List of Modules</h3>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($modules)): ?>
                    <?php foreach($modules as $module): ?>
                    <tr>
                        <td><?= $module['id'] ?></td>
                        <td><?= $module['module_name'] ?></td>
                        <td><?= $module['module_slug'] ?></td>
                        <td>
                            <span class="badge bg-<?= $module['status'] === 'active' ? 'success' : 'danger' ?>">
                                <?= ucfirst($module['status']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?= site_url('modules/edit/'.$module['id']) ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="<?= site_url('modules/delete/'.$module['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No modules found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
