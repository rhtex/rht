<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= isset($yarn) ? 'Edit Predefined Yarn' : 'Add Predefined Yarn' ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1><?= isset($yarn) ? 'Edit Predefined Yarn' : 'Add Predefined Yarn' ?></h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('production/yarns/master') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <form action="<?= isset($yarn) ? site_url('production/yarns/master/update/'.$yarn['id']) : site_url('production/yarns/master/store') ?>" method="post">
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
                    <label class="form-label">Yarn Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="<?= old('name', $yarn['name'] ?? '') ?>" required placeholder="e.g. Cotton 40s, Dyed Polyester">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Yarn Count <span class="text-danger">*</span></label>
                    <input type="text" name="yarn_count" class="form-control" value="<?= old('yarn_count', $yarn['yarn_count'] ?? '') ?>" required placeholder="e.g. 40s, 60s, 2/40s">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Yarn Type <span class="text-danger">*</span></label>
                    <select name="yarn_type" class="form-select" required>
                        <option value="Raw" <?= (old('yarn_type', $yarn['yarn_type'] ?? '') === 'Raw') ? 'selected' : '' ?>>Raw</option>
                        <option value="Dyed" <?= (old('yarn_type', $yarn['yarn_type'] ?? '') === 'Dyed') ? 'selected' : '' ?>>Dyed</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
