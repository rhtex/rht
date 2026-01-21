<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Add Permission<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Add Permission</h1>
    </div>
    <div class="col-sm-6">
        <a href="<?= site_url('permissions') ?>" class="btn btn-secondary float-sm-end">Back</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <form action="<?= site_url('permissions/store') ?>" method="post">
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
                <input type="text" name="permission_name" class="form-control" value="<?= old('permission_name') ?>" placeholder="e.g. View Users" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Permission Key</label>
                <input type="text" name="permission_key" class="form-control" value="<?= old('permission_key') ?>" placeholder="e.g. user.view" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Module</label>
                <select name="module_id" class="form-control" required>
                    <option value="">Select Module</option>
                    <?php foreach($modules as $module): ?>
                        <option value="<?= $module['id'] ?>" <?= old('module_id') == $module['id'] ? 'selected' : '' ?>>
                            <?= $module['module_name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Save Permission</button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
