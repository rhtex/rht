<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>App Settings<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Application Settings</h1>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-8">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">General Settings</h3>
            </div>
            <form action="<?= site_url('settings/update') ?>" method="post">
                <?= csrf_field() ?>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="app_name" class="form-label">Application Name</label>
                        <input type="text" class="form-control" id="app_name" name="settings[app_name]" value="<?= esc($settings['app_name'] ?? 'RasiDev HR') ?>" required>
                        <small class="text-muted">Displays in the sidebar and header.</small>
                    </div>

                    <div class="mb-3">
                        <label for="org_name" class="form-label">Organization Name</label>
                        <input type="text" class="form-control" id="org_name" name="settings[org_name]" value="<?= esc($settings['org_name'] ?? 'RasiDev Solutions') ?>" required>
                        <small class="text-muted">Used in payroll and payslips.</small>
                    </div>

                    <div class="mb-3">
                        <label for="org_address" class="form-label">Organization Address</label>
                        <textarea class="form-control" id="org_address" name="settings[org_address]" rows="3" required><?= esc($settings['org_address'] ?? '123 Corporate Blvd, Business City') ?></textarea>
                        <small class="text-muted">Used in generated payslips.</small>
                    </div>

                    <div class="mb-3">
                        <label for="org_contact" class="form-label">Contact Details</label>
                        <input type="text" class="form-control" id="org_contact" name="settings[org_contact]" value="<?= esc($settings['org_contact'] ?? '+91 98765 43210') ?>" required>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
