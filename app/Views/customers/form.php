<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= isset($customer) ? '<?= lang("App.edit") ?> Customer' : '<?= lang("App.add_new") ?> Customer' ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1><?= isset($customer) ? '<?= lang("App.edit") ?> Customer' : '<?= lang("App.add_new") ?> Customer' ?></h1>
    </div>
    <div class="col-sm-6">
        <a href="<?= site_url('customers') ?>" class="btn btn-secondary float-sm-end"><?= lang("App.back") ?></a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <form action="<?= isset($customer) ? site_url('customers/update/'.$customer['id']) : site_url('customers/store') ?>" method="post">
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
                <!-- Basic Information -->
                <div class="col-md-12 mb-2"><h5 class="text-primary"><i class="fas fa-info-circle"></i> Basic Information</h5><hr></div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Customer/Company Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="<?= old('name', $customer['name'] ?? '') ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Contact Person</label>
                    <input type="text" name="contact_person" class="form-control" value="<?= old('contact_person', $customer['contact_person'] ?? '') ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= (old('status', $customer['status'] ?? '') === 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= (old('status', $customer['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Assigned Agent</label>
                    <select name="agent_id" class="form-select select2">
                        <option value="">-- Select Agent --</option>
                        <?php if (isset($agents)): ?>
                            <?php foreach($agents as $agent): ?>
                                <option value="<?= $agent['id'] ?>" <?= (old('agent_id', $customer['agent_id'] ?? '') == $agent['id']) ? 'selected' : '' ?>>
                                    <?= esc($agent['agent_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Phone <span class="text-danger">*</span></label>
                    <input type="text" name="phone" class="form-control" value="<?= old('phone', $customer['phone'] ?? '') ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">WhatsApp Number</label>
                    <input type="text" name="whatsapp_number" class="form-control" value="<?= old('whatsapp_number', $customer['whatsapp_number'] ?? '') ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= old('email', $customer['email'] ?? '') ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Website</label>
                    <input type="url" name="website" class="form-control" value="<?= old('website', $customer['website'] ?? '') ?>" placeholder="https://">
                </div>

                <!-- GST & Tax Details -->
                <div class="col-md-12 mt-3 mb-2"><h5 class="text-primary"><i class="fas fa-file-invoice-dollar"></i> GST & Tax Details</h5><hr></div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">GST Registration Type <span class="text-danger">*</span></label>
                    <select name="gst_type" class="form-select" required>
                        <?php foreach(['Regular', 'Composition', 'Unregistered', 'Consumer'] as $type): ?>
                            <option value="<?= $type ?>" <?= (old('gst_type', $customer['gst_type'] ?? '') === $type) ? 'selected' : '' ?>><?= $type ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">GSTIN</label>
                    <input type="text" name="gstin" class="form-control" value="<?= old('gstin', $customer['gstin'] ?? '') ?>" maxlength="15">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">PAN Number</label>
                    <input type="text" name="pan_number" class="form-control" value="<?= old('pan_number', $customer['pan_number'] ?? '') ?>" maxlength="10">
                </div>

                <!-- Financial Details -->
                <div class="col-md-12 mt-3 mb-2"><h5 class="text-primary"><i class="fas fa-coins"></i> Financial Information</h5><hr></div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Opening Balance</label>
                    <input type="number" step="0.01" name="opening_balance" class="form-control" value="<?= old('opening_balance', $customer['opening_balance'] ?? '0.00') ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Balance Type</label>
                    <select name="balance_type" class="form-select">
                        <option value="Dr" <?= (old('balance_type', $customer['balance_type'] ?? '') === 'Dr') ? 'selected' : '' ?>>Debit (Receivable)</option>
                        <option value="Cr" <?= (old('balance_type', $customer['balance_type'] ?? '') === 'Cr') ? 'selected' : '' ?>>Credit (Payable/Advance)</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Credit Limit</label>
                    <input type="number" step="0.01" name="credit_limit" class="form-control" value="<?= old('credit_limit', $customer['credit_limit'] ?? '0.00') ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Credit Period (Days)</label>
                    <input type="number" name="credit_period_days" class="form-control" value="<?= old('credit_period_days', $customer['credit_period_days'] ?? '0') ?>">
                </div>

                <!-- Address Information -->
                <div class="col-md-12 mt-3 mb-2"><h5 class="text-primary"><i class="fas fa-map-marker-alt"></i> Address Information</h5><hr></div>

                <div class="col-md-6 border-end">
                    <h6><strong>Billing Address (Default)</strong></h6>
                    <div class="mb-2">
                        <label class="form-label">Address Line 1 <span class="text-danger">*</span></label>
                        <input type="text" name="billing_address_line1" class="form-control" value="<?= old('billing_address_line1', $billing_address['address_line1'] ?? '') ?>" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Address Line 2</label>
                        <input type="text" name="billing_address_line2" class="form-control" value="<?= old('billing_address_line2', $billing_address['address_line2'] ?? '') ?>">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" name="billing_city" class="form-control" value="<?= old('billing_city', $billing_address['city'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Pincode <span class="text-danger">*</span></label>
                            <input type="text" name="billing_pincode" class="form-control" value="<?= old('billing_pincode', $billing_address['pincode'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Country <span class="text-danger">*</span></label>
                        <select name="billing_country_id" id="billing_country_id" class="form-select select2" required>
                            <option value="">Select Country</option>
                            <?php foreach($countries as $country): ?>
                                <option value="<?= $country['id'] ?>" <?= (old('billing_country_id', $billing_address['country_id'] ?? '') == $country['id']) ? 'selected' : (($country['name'] == 'India' && empty($billing_address)) ? 'selected' : '') ?>>
                                    <?= esc($country['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">State <span class="text-danger">*</span></label>
                        <select name="billing_state_id" id="billing_state_id" class="form-select select2" required>
                            <option value="">Select State</option>
                            <?php foreach($states as $state): ?>
                                <option value="<?= $state['id'] ?>" <?= (old('billing_state_id', $billing_address['state_id'] ?? '') == $state['id']) ? 'selected' : '' ?>>
                                    <?= esc($state['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <h6><strong>Shipping Address</strong> <small class="text-muted">(Leave empty if same as billing)</small></h6>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="copy_billing_address">
                        <label class="form-check-label" for="copy_billing_address">
                            Same as Billing Address
                        </label>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Address Line 1</label>
                        <input type="text" name="shipping_address_line1" class="form-control" value="<?= old('shipping_address_line1', $shipping_address['address_line1'] ?? '') ?>">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Address Line 2</label>
                        <input type="text" name="shipping_address_line2" class="form-control" value="<?= old('shipping_address_line2', $shipping_address['address_line2'] ?? '') ?>">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">City</label>
                            <input type="text" name="shipping_city" class="form-control" value="<?= old('shipping_city', $shipping_address['city'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Pincode</label>
                            <input type="text" name="shipping_pincode" class="form-control" value="<?= old('shipping_pincode', $shipping_address['pincode'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Country</label>
                        <select name="shipping_country_id" id="shipping_country_id" class="form-select select2">
                            <option value="">Select Country</option>
                            <?php foreach($countries as $country): ?>
                                <option value="<?= $country['id'] ?>" <?= (old('shipping_country_id', $shipping_address['country_id'] ?? '') == $country['id']) ? 'selected' : (($country['name'] == 'India' && empty($shipping_address)) ? 'selected' : '') ?>>
                                    <?= esc($country['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">State</label>
                        <select name="shipping_state_id" id="shipping_state_id" class="form-select select2">
                            <option value="">Select State</option>
                            <?php foreach($states as $state): ?>
                                <option value="<?= $state['id'] ?>" <?= (old('shipping_state_id', $shipping_address['state_id'] ?? '') == $state['id']) ? 'selected' : '' ?>>
                                    <?= esc($state['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-12 mt-3 mb-3">
                    <label class="form-label">Additional Notes</label>
                    <textarea name="notes" class="form-control" rows="2"><?= old('notes', $customer['notes'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Save Customer Record</button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Initialize Select2 if not already done globally
        $('.select2').select2({
            theme: 'bootstrap-5'
        });

        // Function to load states based on country
        function loadStates(countryId, stateElementId, selectedStateId = null) {
            if (!countryId) {
                $('#' + stateElementId).html('<option value="">Select State</option>');
                $('#' + stateElementId).trigger('change'); // Notify Select2 of change
                return;
            }

            $.ajax({
                url: '<?= site_url('master-data/states/') ?>' + countryId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    var options = '<option value="">Select State</option>';
                    $.each(response, function(index, state) {
                        var selected = (selectedStateId && selectedStateId == state.id) ? 'selected' : '';
                        options += '<option value="' + state.id + '" ' + selected + '>' + state.name + '</option>';
                    });
                    $('#' + stateElementId).html(options);
                    $('#' + stateElementId).trigger('change'); // Notify Select2 of change
                },
                error: function() {
                    console.error('Failed to fetch states');
                    $('#' + stateElementId).html('<option value="">Select State</option>');
                    $('#' + stateElementId).trigger('change'); // Notify Select2 of change
                }
            });
        }

        // Event listener for Billing Country
        $('#billing_country_id').change(function() {
            var countryId = $(this).val();
            var selectedStateId = '<?= old('billing_state_id', $billing_address['state_id'] ?? '') ?>';
            loadStates(countryId, 'billing_state_id', selectedStateId);
        });

        // Event listener for Shipping Country
        $('#shipping_country_id').change(function() {
            var countryId = $(this).val();
            var selectedStateId = '<?= old('shipping_state_id', $shipping_address['state_id'] ?? '') ?>';
            loadStates(countryId, 'shipping_state_id', selectedStateId);
        });

        // Initial load for states if countries are pre-selected (e.g., on edit or old data)
        if ($('#billing_country_id').val()) {
            $('#billing_country_id').trigger('change');
        }
        if ($('#shipping_country_id').val()) {
            $('#shipping_country_id').trigger('change');
        }


        // Copy Billing Address Logic
        $('#copy_billing_address').change(function() {
            if ($(this).is(':checked')) {
                $('input[name="shipping_address_line1"]').val($('input[name="billing_address_line1"]').val());
                $('input[name="shipping_address_line2"]').val($('input[name="billing_address_line2"]').val());
                $('input[name="shipping_city"]').val($('input[name="billing_city"]').val());
                $('input[name="shipping_pincode"]').val($('input[name="billing_pincode"]').val());
                
                // Copy Country first
                var billingCountry = $('#billing_country_id').val();
                var billingState = $('#billing_state_id').val();
                
                // Set shipping country
                $('#shipping_country_id').val(billingCountry).trigger('change');

                // Copy the options from billing state to shipping state directly
                var billingStateOptions = $('#billing_state_id').html();
                $('#shipping_state_id').html(billingStateOptions);
                $('#shipping_state_id').val(billingState).trigger('change');

            } 
        });

        // Also update if billing fields change while checkbox is checked
        $('input[name^="billing_"], #billing_country_id, #billing_state_id').on('input change', function() {
            if ($('#copy_billing_address').is(':checked')) {
                 $('#copy_billing_address').trigger('change');
            }
        });
        
        // Trigger generic change event to ensure Select2 updates visual display if needed
        // But for copying, direct modification might need explicit trigger.
    });
</script>
<?= $this->endSection() ?>
