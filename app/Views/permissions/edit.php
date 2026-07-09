<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= lang("App.edit") ?> Permission<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1><?= lang("App.edit") ?> Permission</h1>
    </div>
    <div class="col-sm-6">
        <a href="<?= site_url('permissions') ?>" class="btn btn-secondary float-sm-end"><?= lang("App.back") ?></a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-warning">
    <form action="<?= site_url('permissions/update/'.$permission['id']) ?>" method="post">
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
                <label class="form-label">Permission Name</label>
                <input type="text" name="permission_name" class="form-control" value="<?= old('permission_name', $permission['permission_name']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Permission Key</label>
                <input type="text" name="permission_key" class="form-control" value="<?= old('permission_key', $permission['permission_key']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Module</label>
                <select name="module_id" class="form-control" required>
                    <option value="">Select Module</option>
                    <?php foreach($modules as $module): ?>
                        <option value="<?= $module['id'] ?>" <?= (old('module_id') ?? $permission['module_id']) == $module['id'] ? 'selected' : '' ?>>
                            <?= $module['module_name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-warning">Update Permission</button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
