<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Convert Employee to User<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Convert Employee to User</h1>
    </div>
    <div class="col-sm-6">
        <a href="<?= site_url('employees') ?>" class="btn btn-secondary float-sm-end">Back to Employees</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-info card-outline">
    <form action="<?= site_url('users/store-conversion') ?>" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="employee_id" value="<?= $employee['id'] ?>">
        
        <div class="card-body">
            
            <?php if (session()->has('errors')) : ?>
                <div class="alert alert-danger">
                    <ul>
                    <?php foreach (session('errors') as $error) : ?>
                        <li><?= $error ?></li>
                    <?php endforeach ?>
                    </ul>
                </div>
            <?php endif ?>

            <div class="alert alert-light border">
                <h5><i class="fas fa-info-circle text-info"></i> <?= lang('App.employee_details') ?></h5>
                <p class="mb-0">
                    <strong>Name:</strong> <?= $employee['first_name'] . ' ' . $employee['last_name'] ?><br>
                    <strong>Email:</strong> <?= $employee['email'] ?: 'No email set' ?>
                </p>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">User Name</label>
                        <input type="text" class="form-control" name="name" value="<?= old('name', $employee['first_name'] . ' ' . $employee['last_name']) ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="email" class="form-label">Login Email</label>
                        <input type="email" class="form-control" name="email" value="<?= old('email', $employee['email']) ?>" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="role_id" class="form-label">Role</label>
                        <select name="role_id" class="form-select" required>
                            <option value="">Select Role</option>
                            <?php foreach($roles as $role): ?>
                                <option value="<?= $role['id'] ?>" <?= old('role_id') == $role['id'] ? 'selected' : '' ?>>
                                    <?= ucfirst($role['role_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-info">Convert to User</button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
