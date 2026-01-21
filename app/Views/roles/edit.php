<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Edit Role<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Edit Role</h1>
    </div>
    <div class="col-sm-6">
        <a href="<?= site_url('roles') ?>" class="btn btn-secondary float-sm-end">Back to List</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-warning card-outline">
    <form action="<?= site_url('roles/update/'.$role['id']) ?>" method="post">
        <?= csrf_field() ?>
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

            <div class="mb-3">
                <label for="role_name" class="form-label">Role Name</label>
                <input type="text" class="form-control" name="role_name" value="<?= old('role_name', $role['role_name']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" name="description" rows="3"><?= old('description', $role['description']) ?></textarea>
            </div>

        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-warning">Update Role</button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
