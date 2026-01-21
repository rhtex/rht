<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= isset($country) ? 'Edit Country' : 'Add New Country' ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><?= $title ?></h1>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <?php $isEdit = isset($country); ?>
                <form action="<?= $isEdit ? site_url('countries/update/'.$country['id']) : site_url('countries/store') ?>" method="post">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Country Name</label>
                            <input type="text" name="name" class="form-control" value="<?= old('name', $isEdit ? $country['name'] : '') ?>" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">ISO Code 2</label>
                            <input type="text" name="iso_code_2" class="form-control" value="<?= old('iso_code_2', $isEdit ? $country['iso_code_2'] : '') ?>" maxlength="2">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">ISO Code 3</label>
                            <input type="text" name="iso_code_3" class="form-control" value="<?= old('iso_code_3', $isEdit ? $country['iso_code_3'] : '') ?>" maxlength="3">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Phone Code</label>
                            <input type="text" name="phone_code" class="form-control" value="<?= old('phone_code', $isEdit ? $country['phone_code'] : '') ?>">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Save' ?></button>
                        <a href="<?= site_url('countries') ?>" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
