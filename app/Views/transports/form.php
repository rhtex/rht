<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= isset($transport) ? 'Edit Transport' : 'Add New Transport' ?><?= $this->endSection() ?>

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
                <?php $isEdit = isset($transport); ?>
                <form action="<?= $isEdit ? site_url('transports/update/'.$transport['id']) : site_url('transports/store') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Transport Name</label>
                            <input type="text" name="transport_name" class="form-control" value="<?= old('transport_name', $isEdit ? $transport['transport_name'] : '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Transport Code</label>
                            <input type="text" name="transport_code" class="form-control" value="<?= old('transport_code', $isEdit ? $transport['transport_code'] : '') ?>">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Branch Name</label>
                            <input type="text" name="branch" class="form-control" value="<?= old('branch', $isEdit ? $transport['branch'] : '') ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Branch Phone</label>
                            <input type="text" name="branch_phone_number" class="form-control" value="<?= old('branch_phone_number', $isEdit ? $transport['branch_phone_number'] : '') ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Branch GST</label>
                            <input type="text" name="branch_gst_number" class="form-control" value="<?= old('branch_gst_number', $isEdit ? $transport['branch_gst_number'] : '') ?>">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Branch Address</label>
                            <textarea name="branch_address" class="form-control" rows="3" required><?= old('branch_address', $isEdit ? $transport['branch_address'] : '') ?></textarea>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">State</label>
                            <select name="state_id" id="state_id" class="form-control select2">
                                <option value="">Select State</option>
                                <?php foreach($states as $state): ?>
                                <option value="<?= $state['id'] ?>" <?= old('state_id', ($isEdit ? $transport['state_id'] : '')) == $state['id'] ? 'selected' : '' ?>>
                                    <?= $state['name'] ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Country</label>
                            <select name="country_id" id="country_id" class="form-control select2">
                                <option value="">Select Country</option>
                                <?php foreach($countries as $country): ?>
                                <option value="<?= $country['id'] ?>" <?= old('country_id', ($isEdit ? $transport['country_id'] : ($default_country_id ?? ''))) == $country['id'] ? 'selected' : '' ?>>
                                    <?= $country['name'] ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Save' ?></button>
                        <a href="<?= site_url('transports') ?>" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    $('#country_id').change(function() {
        const countryId = $(this).val();
        if (countryId) {
            $.ajax({
                url: '<?= site_url('master-data/states/') ?>' + countryId,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    $('#state_id').empty();
                    $('#state_id').append('<option value="">Select State</option>');
                    $.each(data, function(key, value) {
                        $('#state_id').append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                }
            });
        } else {
            $('#state_id').empty();
            $('#state_id').append('<option value="">Select State</option>');
        }
    });
});
</script>
<?= $this->endSection() ?>
