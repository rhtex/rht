<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= isset($weaver) ? 'Edit Weaver' : 'Add New Weaver' ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1><?= isset($weaver) ? 'Edit Weaver' : 'Add New Weaver' ?></h1>
    </div>
    <div class="col-sm-6">
        <a href="<?= site_url('production/weavers') ?>" class="btn btn-secondary float-sm-end">Back</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <form action="<?= isset($weaver) ? site_url('production/weavers/update/'.$weaver['id']) : site_url('production/weavers/store') ?>" method="post">
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

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Weaver Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="<?= old('name', $weaver['name'] ?? '') ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Code</label>
                    <input type="text" name="code" class="form-control" value="<?= old('code', $weaver['code'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?= old('phone', $weaver['phone'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= (old('status', $weaver['status'] ?? '') === 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= (old('status', $weaver['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="3"><?= old('address', $weaver['address'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
