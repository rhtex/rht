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

                    <hr class="my-4">
                    <h5 class="text-primary mb-3">Organization Address & Contact Info</h5>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Address Line 1 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="settings[company_address_1]" value="<?= esc($settings['company_address_1'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Address Line 2</label>
                            <input type="text" class="form-control" name="settings[company_address_2]" value="<?= esc($settings['company_address_2'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" class="form-control" name="settings[company_city]" value="<?= esc($settings['company_city'] ?? '') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Country <span class="text-danger">*</span></label>
                            <select class="form-select select2" name="settings[company_country]" id="countrySelect" required>
                                <option value="">Select Country</option>
                                <?php foreach($countries as $country): ?>
                                    <option value="<?= $country['id'] ?>" <?= ($settings['company_country'] ?? '') == $country['id'] ? 'selected' : '' ?>>
                                        <?= esc($country['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">State <span class="text-danger">*</span></label>
                            <select class="form-select select2" name="settings[company_state]" id="stateSelect" required>
                                <option value="">Select State</option>
                                <!-- States loaded via JS -->
                            </select>
                            <input type="hidden" id="selectedStateId" value="<?= esc($settings['company_state'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Pincode</label>
                            <input type="text" class="form-control" name="settings[company_pincode]" value="<?= esc($settings['company_pincode'] ?? '') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">GSTIN</label>
                            <input type="text" class="form-control" name="settings[company_gstin]" value="<?= esc($settings['company_gstin'] ?? '') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">GST Type</label>
                            <select class="form-select" name="settings[company_gst_type]">
                                <option value="">Select GST Type</option>
                                <option value="Regular" <?= ($settings['company_gst_type'] ?? '') == 'Regular' ? 'selected' : '' ?>>Regular</option>
                                <option value="Composition" <?= ($settings['company_gst_type'] ?? '') == 'Composition' ? 'selected' : '' ?>>Composition</option>
                                <option value="Unregistered" <?= ($settings['company_gst_type'] ?? '') == 'Unregistered' ? 'selected' : '' ?>>Unregistered</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h5 class="text-primary mb-3">Contact Details</h5>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="settings[company_email]" value="<?= esc($settings['company_email'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" class="form-control" name="settings[company_phone]" value="<?= esc($settings['company_phone'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="settings[company_mobile]" value="<?= esc($settings['company_mobile'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">WhatsApp Number</label>
                            <input type="text" class="form-control" name="settings[company_whatsapp]" value="<?= esc($settings['company_whatsapp'] ?? '') ?>">
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><?= lang("App.save") ?> Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Initialize Select2 Elements
    $('.select2').select2({
        theme: 'bootstrap-5'
    });

    const countrySelect = $('#countrySelect');
    const stateSelect = $('#stateSelect');
    const selectedStateId = $('#selectedStateId').val();

    function loadStates(countryId, selectedId = null) {
        if (!countryId) {
            stateSelect.html('<option value="">Select State</option>');
            return;
        }

        console.log('Loading states for country:', countryId);
        
        $.ajax({
            url: '<?= site_url('master-data/states') ?>/' + countryId,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                console.log('States loaded:', data);
                let options = '<option value="">Select State</option>';
                if (Array.isArray(data)) {
                    data.forEach(function(state) {
                        const isSelected = (selectedId && state.id == selectedId) ? 'selected' : '';
                        options += `<option value="${state.id}" ${isSelected}>${state.name}</option>`;
                    });
                }
                stateSelect.html(options);
                // Trigger change to update Select2 if needed, though html() updates underlying select
                // stateSelect.trigger('change'); 
            },
            error: function(xhr, status, error) {
                console.error('Error loading states:', error);
                console.error('Response:', xhr.responseText);
                alert('Failed to load states. Please try again.');
            }
        });
    }

    countrySelect.change(function() {
        loadStates($(this).val());
    });

    // Initial load if country is selected
    if (countrySelect.val()) {
        loadStates(countrySelect.val(), selectedStateId);
    }
});
</script>
<?= $this->endSection() ?>
