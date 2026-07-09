<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= isset($vendor) ? '<?= lang("App.edit") ?> Vendor' : '<?= lang("App.add_new") ?> Vendor' ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1><?= isset($vendor) ? '<?= lang("App.edit") ?> Vendor' : '<?= lang("App.add_new") ?> Vendor' ?></h1>
    </div>
    <div class="col-sm-6">
        <a href="<?= site_url('vendors') ?>" class="btn btn-secondary float-sm-end"><?= lang("App.back") ?></a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <form action="<?= isset($vendor) ? site_url('vendors/update/'.$vendor['id']) : site_url('vendors/store') ?>" method="post">
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
                    <label class="form-label">Vendor/Company Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="<?= old('name', $vendor['name'] ?? '') ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Contact Person</label>
                    <input type="text" name="contact_person" class="form-control" value="<?= old('contact_person', $vendor['contact_person'] ?? '') ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= (old('status', $vendor['status'] ?? '') === 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= (old('status', $vendor['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Phone <span class="text-danger">*</span></label>
                    <input type="text" name="phone" class="form-control" value="<?= old('phone', $vendor['phone'] ?? '') ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">WhatsApp Number</label>
                    <input type="text" name="whatsapp_number" class="form-control" value="<?= old('whatsapp_number', $vendor['whatsapp_number'] ?? '') ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= old('email', $vendor['email'] ?? '') ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Website</label>
                    <input type="url" name="website" class="form-control" value="<?= old('website', $vendor['website'] ?? '') ?>" placeholder="https://">
                </div>

                <!-- GST & Tax Details -->
                <div class="col-md-12 mt-3 mb-2"><h5 class="text-primary"><i class="fas fa-file-invoice-dollar"></i> GST & Tax Details</h5><hr></div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">GST Registration Type <span class="text-danger">*</span></label>
                    <select name="gst_type" class="form-select" required>
                        <?php foreach(['Regular', 'Composition', 'Unregistered'] as $type): ?>
                            <option value="<?= $type ?>" <?= (old('gst_type', $vendor['gst_type'] ?? '') === $type) ? 'selected' : '' ?>><?= $type ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">GSTIN</label>
                    <input type="text" name="gstin" class="form-control" value="<?= old('gstin', $vendor['gstin'] ?? '') ?>" maxlength="15">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">PAN Number</label>
                    <input type="text" name="pan_number" class="form-control" value="<?= old('pan_number', $vendor['pan_number'] ?? '') ?>" maxlength="10">
                </div>

                <!-- Financial Details -->
                <div class="col-md-12 mt-3 mb-2"><h5 class="text-primary"><i class="fas fa-coins"></i> Financial Information</h5><hr></div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Opening Balance</label>
                    <input type="number" step="0.01" name="opening_balance" class="form-control" value="<?= old('opening_balance', $vendor['opening_balance'] ?? '0.00') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Balance Type</label>
                    <select name="balance_type" class="form-select">
                        <option value="Cr" <?= (old('balance_type', $vendor['balance_type'] ?? '') === 'Cr') ? 'selected' : '' ?>>Credit (Payable)</option>
                        <option value="Dr" <?= (old('balance_type', $vendor['balance_type'] ?? '') === 'Dr') ? 'selected' : '' ?>>Debit (Receivable/Advance)</option>
                    </select>
                </div>

                <!-- Bank Account Details -->
                <div class="col-md-12 mt-3 mb-2"><h5 class="text-primary"><i class="fas fa-university"></i> Bank Account Details (For Payments)</h5><hr></div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Bank Name</label>
                    <input type="text" name="bank_name" class="form-control" value="<?= old('bank_name', $vendor['bank_name'] ?? '') ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Account Number</label>
                    <input type="text" name="bank_account_no" class="form-control" value="<?= old('bank_account_no', $vendor['bank_account_no'] ?? '') ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">IFSC Code</label>
                    <input type="text" name="bank_ifsc" class="form-control" value="<?= old('bank_ifsc', $vendor['bank_ifsc'] ?? '') ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Branch Name</label>
                    <input type="text" name="bank_branch" class="form-control" value="<?= old('bank_branch', $vendor['bank_branch'] ?? '') ?>">
                </div>

                <!-- Address Information -->
                <div class="col-md-12 mt-3 mb-2"><h5 class="text-primary"><i class="fas fa-map-marker-alt"></i> Address Information</h5><hr></div>

                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Address Line 1 <span class="text-danger">*</span></label>
                            <input type="text" name="address_line1" class="form-control" value="<?= old('address_line1', $billing_address['address_line1'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Address Line 2</label>
                            <input type="text" name="address_line2" class="form-control" value="<?= old('address_line2', $billing_address['address_line2'] ?? '') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" name="address_city" class="form-control" value="<?= old('address_city', $billing_address['city'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Pincode <span class="text-danger">*</span></label>
                            <input type="text" name="address_pincode" class="form-control" value="<?= old('address_pincode', $billing_address['pincode'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">State <span class="text-danger">*</span></label>
                            <select name="address_state_id" class="form-select" required>
                                <option value="">Select State</option>
                                <?php foreach($states as $state): ?>
                                    <option value="<?= $state['id'] ?>" <?= (old('address_state_id', $billing_address['state_id'] ?? '') == $state['id']) ? 'selected' : '' ?>>
                                        <?= esc($state['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Additional Notes</label>
                    <textarea name="notes" class="form-control" rows="2"><?= old('notes', $vendor['notes'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Save Vendor Record</button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
