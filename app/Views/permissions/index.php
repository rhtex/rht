<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Permissions<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Permission Management</h1>
    </div>
    <div class="col-sm-6">
        <a href="<?= site_url('permissions/create') ?>" class="btn btn-primary float-sm-end">
            <i class="fas fa-plus"></i> Add New Permission
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">List of Permissions</h3>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped" id="permsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Key</th>
                    <th>Module</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($permissions)): ?>
                    <?php foreach($permissions as $perm): ?>
                    <tr>
                        <td><?= $perm['id'] ?></td>
                        <td><?= $perm['permission_name'] ?></td>
                        <td><code><?= $perm['permission_key'] ?></code></td>
                        <td><?= $perm['module_name'] ?></td>
                        <td>
                            <a href="<?= site_url('permissions/edit/'.$perm['id']) ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="<?= site_url('permissions/delete/'.$perm['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No permissions found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
