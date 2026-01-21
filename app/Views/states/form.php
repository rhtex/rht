<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= isset($state) ? 'Edit State' : 'Add New State' ?><?= $this->endSection() ?>

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
                <?php $isEdit = isset($state); ?>
                <form action="<?= $isEdit ? site_url('states/update/'.$state['id']) : site_url('states/store') ?>" method="post">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">State Name</label>
                            <input type="text" name="name" class="form-control" value="<?= old('name', $isEdit ? $state['name'] : '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Country</label>
                            <select name="country_id" class="form-control select2" required>
                                <option value="">Select Country</option>
                                <?php foreach($countries as $country): ?>
                                <option value="<?= $country['id'] ?>" <?= old('country_id', ($isEdit ? $state['country_id'] : '')) == $country['id'] ? 'selected' : '' ?>>
                                    <?= $country['name'] ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">State Code</label>
                            <input type="text" name="state_code" class="form-control" value="<?= old('state_code', $isEdit ? $state['state_code'] : '') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">GST State Code</label>
                            <input type="text" name="gst_state_code" class="form-control" value="<?= old('gst_state_code', $isEdit ? $state['gst_state_code'] : '') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="active" <?= old('status', ($isEdit ? $state['status'] : '')) == 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= old('status', ($isEdit ? $state['status'] : '')) == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Save' ?></button>
                        <a href="<?= site_url('states') ?>" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
