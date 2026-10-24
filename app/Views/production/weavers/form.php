<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= isset($weaver) ? lang("App.edit") . ' Weaver' : lang("App.add_new") . ' Weaver' ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1><?= isset($weaver) ? lang("App.edit") . ' Weaver' : lang("App.add_new") . ' Weaver' ?></h1>
    </div>
    <div class="col-sm-6">
        <a href="<?= site_url('production/weavers') ?>" class="btn btn-secondary float-sm-end"><?= lang("App.back") ?></a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <form action="<?= isset($weaver) ? site_url('production/weavers/update/'.$weaver['id']) : site_url('production/weavers/store') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <?php if (isset($weaver['id'])): ?>
            <input type="hidden" name="id" value="<?= esc($weaver['id']) ?>">
        <?php endif; ?>
        <div class="card-body">
            <?php if (session()->has('errors')) : ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
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
                <div class="col-md-6 mb-3">
                    <label class="form-label">Location / Google Maps Link (Optional)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-map-marker-alt text-danger"></i></span>
                        <input type="text" name="location" id="locationInput" class="form-control" placeholder="https://maps.google.com/... or Location Address" value="<?= old('location', $weaver['location'] ?? '') ?>">
                        <a href="<?= esc(old('location', $weaver['location'] ?? '#')) ?>" id="openLocationBtn" target="_blank"
                            class="btn btn-outline-primary <?= empty(old('location', $weaver['location'] ?? '')) ? 'd-none' : '' ?>" title="Open Google Maps">
                            <i class="fas fa-external-link-alt"></i> Test Link
                        </a>
                    </div>
                    <small class="text-muted">Paste full Google Maps URL or enter address/city.</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Address Proof</label>
                    <input type="file" name="address_proof" class="form-control">
                    <?php if (!empty($weaver['address_proof'])) : ?>
                        <div class="mt-2">
                            <a href="<?= base_url($weaver['address_proof']) ?>" target="_blank" class="btn btn-xs btn-outline-info"><i class="fas fa-eye"></i> View Current Proof</a>
                        </div>
                    <?php endif; ?>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const locationInput = document.getElementById('locationInput');
    const openBtn = document.getElementById('openLocationBtn');
    if (locationInput && openBtn) {
        locationInput.addEventListener('input', function () {
            const val = this.value.trim();
            if (val.startsWith('http://') || val.startsWith('https://')) {
                openBtn.href = val;
                openBtn.classList.remove('d-none');
            } else if (val.length > 0) {
                openBtn.href = 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(val);
                openBtn.classList.remove('d-none');
            } else {
                openBtn.classList.add('d-none');
            }
        });
    }
});
</script>
<?= $this->endSection() ?>
