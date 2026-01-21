<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= isset($customer) ? 'Edit Customer' : 'Add New Customer' ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1><?= isset($customer) ? 'Edit Customer' : 'Add New Customer' ?></h1>
    </div>
    <div class="col-sm-6">
        <a href="<?= site_url('customers') ?>" class="btn btn-secondary float-sm-end">Back</a>
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
                        <label class="form-label">State <span class="text-danger">*</span></label>
                        <select name="billing_state_id" class="form-select" required>
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
                        <label class="form-label">State</label>
                        <select name="shipping_state_id" class="form-select">
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
