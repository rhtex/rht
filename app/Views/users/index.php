<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Users<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>User Management</h1>
    </div>
    <div class="col-sm-6">
        <a href="<?= site_url('users/create') ?>" class="btn btn-primary float-sm-end">
            <i class="fas fa-plus"></i> Add New User
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">List of Users</h3>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($users)): ?>
                    <?php foreach($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= $user['name'] ?></td>
                        <td><?= $user['email'] ?></td>
                        <td><span class="badge text-bg-info"><?= $user['role_name'] ?></span></td>
                        <td>
                            <span class="badge text-bg-<?= $user['status'] == 'active' ? 'success' : 'danger' ?>">
                                <?= ucfirst($user['status']) ?>
                            </span>
                        </td>
                        <td><?= $user['last_login_at'] ?></td>
                        <td>
                            <a href="<?= site_url('users/edit/'.$user['id']) ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="<?= site_url('users/delete/'.$user['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No users found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
