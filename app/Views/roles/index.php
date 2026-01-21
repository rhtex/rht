<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Roles<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Role Management</h1>
    </div>
    <div class="col-sm-6">
        <a href="<?= site_url('roles/create') ?>" class="btn btn-primary float-sm-end">
            <i class="fas fa-plus"></i> Add New Role
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">List of Roles</h3>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Role Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($roles)): ?>
                    <?php foreach($roles as $role): ?>
                    <tr>
                        <td><?= $role['id'] ?></td>
                        <td><?= ucfirst($role['role_name']) ?></td>
                        <td><?= $role['description'] ?></td>
                        <td>
                            <a href="<?= site_url('roles/edit/'.$role['id']) ?>" class="btn btn-sm btn-warning" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="<?= site_url('roles/permissions/'.$role['id']) ?>" class="btn btn-sm btn-info" title="Manage Permissions">
                                <i class="fas fa-key"></i>
                            </a>
                            <?php if($role['role_name'] !== 'admin'): ?>
                            <a href="<?= site_url('roles/delete/'.$role['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')" title="Delete">
                                <i class="fas fa-trash"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">No roles found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
