<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Manage Role Permissions<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Manage Permissions: <?= ucfirst($role['role_name']) ?></h1>
    </div>
    <div class="col-sm-6">
        <a href="<?= site_url('roles') ?>" class="btn btn-secondary float-sm-end">Back to List</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<form action="<?= site_url('roles/permissions/update/'.$role['id']) ?>" method="post">
    <?= csrf_field() ?>
    
    <div class="row">
        <?php foreach($modules as $module): ?>
        <div class="col-md-4">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title"><?= $module['module_name'] ?></h3>
                </div>
                <div class="card-body">
                    <?php if(!empty($module['permissions'])): ?>
                        <?php foreach($module['permissions'] as $perm): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permissions[]" 
                                   value="<?= $perm['id'] ?>" id="perm_<?= $perm['id'] ?>"
                                   <?= in_array($perm['id'], $assigned_ids) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="perm_<?= $perm['id'] ?>">
                                <?= $perm['permission_name'] ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No permissions found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <button type="submit" class="btn btn-primary"><?= lang("App.save") ?> Permissions</button>
        </div>
    </div>
</form>
<?= $this->endSection() ?>
