<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= isset($vendor) ? 'Edit Job Work Vendor' : 'Add New Job Work Vendor' ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1><?= isset($vendor) ? 'Edit Job Work Vendor' : 'Add New Job Work Vendor' ?></h1>
    </div>
    <div class="col-sm-6">
        <a href="<?= site_url('production/vendors') ?>" class="btn btn-secondary float-sm-end">Back</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <form action="<?= isset($vendor) ? site_url('production/vendors/update/'.$vendor['id']) : site_url('production/vendors/store') ?>" method="post">
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
                    <label class="form-label">Vendor Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="<?= old('name', $vendor['name'] ?? '') ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Business Type</label>
                    <select name="business_type" class="form-select">
                        <option value="">Select Business Type</option>
                        <option value="Proprietorship" <?= (old('business_type', $vendor['business_type'] ?? '') === 'Proprietorship') ? 'selected' : '' ?>>Proprietorship</option>
                        <option value="Partnership" <?= (old('business_type', $vendor['business_type'] ?? '') === 'Partnership') ? 'selected' : '' ?>>Partnership</option>
                        <option value="LLP" <?= (old('business_type', $vendor['business_type'] ?? '') === 'LLP') ? 'selected' : '' ?>>LLP</option>
                        <option value="Private Limited" <?= (old('business_type', $vendor['business_type'] ?? '') === 'Private Limited') ? 'selected' : '' ?>>Private Limited</option>
                        <option value="Individual" <?= (old('business_type', $vendor['business_type'] ?? '') === 'Individual') ? 'selected' : '' ?>>Individual</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3" id="gst_container">
                    <label class="form-label">GST Number</label>
                    <input type="text" name="gst_number" class="form-control" placeholder="15-digit GSTIN" maxlength="15" value="<?= old('gst_number', $vendor['gst_number'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3" id="pan_container">
                    <label class="form-label">PAN Number</label>
                    <input type="text" name="pan_number" class="form-control" placeholder="10-digit PAN" maxlength="10" value="<?= old('pan_number', $vendor['pan_number'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?= old('phone', $vendor['phone'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= old('email', $vendor['email'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">WhatsApp No</label>
                    <input type="text" name="whatsapp_number" class="form-control" value="<?= old('whatsapp_number', $vendor['whatsapp_number'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= (old('status', $vendor['status'] ?? '') === 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= (old('status', $vendor['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Location (Optional)</label>
                    <input type="text" name="location" class="form-control" value="<?= old('location', $vendor['location'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Type of Job Work</label>
                    <div class="card p-2" style="max-height: 150px; overflow-y: auto;">
                        <?php 
                        $selected_types = isset($vendor['job_work_type']) ? explode(',', $vendor['job_work_type']) : [];
                        $selected_types = array_map('trim', $selected_types);
                        $job_types = ['Warping', 'Sizing', 'Dyeing', 'Twisting', 'Weaving'];
                        foreach ($job_types as $type): 
                            $checked = in_array($type, $selected_types) ? 'checked' : '';
                        ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="job_work_types[]" value="<?= $type ?>" id="job_type_<?= strtolower($type) ?>" <?= $checked ?>>
                                <label class="form-check-label" for="job_type_<?= strtolower($type) ?>">
                                    <?= $type ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="3"><?= old('address', $vendor['address'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        var $businessType = $('select[name="business_type"]');
        var $gstContainer = $('#gst_container');
        var $gstInput = $('input[name="gst_number"]');
        var $panInput = $('input[name="pan_number"]');

        function toggleGstField() {
            if ($businessType.val() === 'Individual') {
                $gstInput.val('');
                $gstContainer.hide();
            } else {
                $gstContainer.show();
            }
        }

        $businessType.on('change', toggleGstField);
        
        // Initial call on page load
        toggleGstField();

        // Auto-populate PAN from GST
        $gstInput.on('input', function() {
            var gstVal = $(this).val().trim();
            if (gstVal.length >= 12) {
                var panVal = gstVal.substring(2, 12).toUpperCase();
                if (/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/.test(panVal)) {
                    $panInput.val(panVal);
                }
            }
        });
    });
</script>
<?= $this->endSection() ?>
