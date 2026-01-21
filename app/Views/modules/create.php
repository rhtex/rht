<?= $this->extend('layouts/master') ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Add Module</h1>
    </div>
    <div class="col-sm-6">
        <a href="<?= site_url('modules') ?>" class="btn btn-secondary float-sm-end">Back</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <form action="<?= site_url('modules/store') ?>" method="post">
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
                <label class="form-label">Module Name</label>
                <input type="text" name="module_name" class="form-control" value="<?= old('module_name') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Module Slug</label>
                <input type="text" name="module_slug" class="form-control" value="<?= old('module_slug') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Save Module</button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
