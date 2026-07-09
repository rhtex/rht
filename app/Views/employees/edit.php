<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= lang("App.edit") ?> Employee<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1><?= lang("App.edit") ?> Employee</h1>
    </div>
    <div class="col-sm-6">
        <a href="<?= site_url('employees') ?>" class="btn btn-secondary float-sm-end">Back to List</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-warning card-outline">
    <form action="<?= site_url('employees/update/'.$employee['id']) ?>" method="post" enctype="multipart/form-data">
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

            <!-- Personal Info -->
            <h5 class="text-primary mb-3">Personal Information</h5>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="first_name" value="<?= old('first_name', $employee['first_name']) ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="last_name" value="<?= old('last_name', $employee['last_name']) ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="<?= old('email', $employee['email']) ?>">
                </div>
            
                <div class="col-md-4 mb-3">
                    <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="mobile_number" value="<?= old('mobile_number', $employee['mobile_number']) ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Joining Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="joining_date" value="<?= old('joining_date', date('Y-m-d', strtotime($employee['joining_date']))) ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= old('status', $employee['status']) == 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= old('status', $employee['status']) == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label"><?= lang('App.employment_type') ?></label>
                    <select name="employment_type" class="form-select">
                        <option value="Permanent" <?= old('employment_type', $employee['employment_type']) == 'Permanent' ? 'selected' : '' ?>><?= lang('App.permanent') ?></option>
                        <option value="Temporary" <?= old('employment_type', $employee['employment_type']) == 'Temporary' ? 'selected' : '' ?>><?= lang('App.temporary') ?></option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Basic Salary <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control" name="basic_salary" value="<?= old('basic_salary', $employee['basic_salary'] ?? '') ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Daily Working Hours <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control" name="daily_working_hours" value="<?= old('daily_working_hours', $employee['daily_working_hours'] ?? '8.00') ?>" required>
                </div>
            </div>

            <!-- Guardian Info -->
            <h5 class="text-primary mb-3 mt-4">Guardian Information</h5>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">First Name</label>
                    <input type="text" class="form-control" name="guardian_first_name" value="<?= old('guardian_first_name', $employee['guardian_first_name']) ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" class="form-control" name="guardian_last_name" value="<?= old('guardian_last_name', $employee['guardian_last_name']) ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Relationship</label>
                    <select name="guardian_type" class="form-select">
                        <option value="Father" <?= old('guardian_type', $employee['guardian_type']) == 'Father' ? 'selected' : '' ?>>Father</option>
                        <option value="Mother" <?= old('guardian_type', $employee['guardian_type']) == 'Mother' ? 'selected' : '' ?>>Mother</option>
                        <option value="Husband" <?= old('guardian_type', $employee['guardian_type']) == 'Husband' ? 'selected' : '' ?>>Husband</option>
                        <option value="Other" <?= old('guardian_type', $employee['guardian_type']) == 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Guardian Mobile</label>
                    <input type="text" class="form-control" name="guardian_mobile_number" value="<?= old('guardian_mobile_number', $employee['guardian_mobile_number']) ?>">
                </div>
            </div>
            
            <!-- Address Info -->
            <h5 class="text-primary mb-3 mt-4">Address Details</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Address Line 1</label>
                    <input type="text" class="form-control" name="address_line_1" value="<?= old('address_line_1', $employee['address_line_1']) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Address Line 2</label>
                    <input type="text" class="form-control" name="address_line_2" value="<?= old('address_line_2', $employee['address_line_2']) ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Village</label>
                    <input type="text" class="form-control" name="village" value="<?= old('village', $employee['village']) ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">City</label>
                    <input type="text" class="form-control" name="city" value="<?= old('city', $employee['city']) ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">State</label>
                    <select name="state_id" id="state_id" class="form-select select2">
                        <option value="">Select State</option>
                        <?php foreach($states as $state): ?>
                            <option value="<?= $state['id'] ?>" <?= old('state_id', $employee['state_id']) == $state['id'] ? 'selected' : '' ?>><?= $state['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Country</label>
                    <select name="country_id" id="country_id" class="form-select select2">
                        <option value="">Select Country</option>
                        <?php foreach($countries as $country): ?>
                            <option value="<?= $country['id'] ?>" <?= old('country_id', $employee['country_id']) == $country['id'] ? 'selected' : '' ?>><?= $country['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Documents -->
            <h5 class="text-primary mb-3 mt-4">Documents & Photos</h5>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Employee Photo</label>
                    <?php if($employee['photo']): ?>
                        <div class="mb-2"><img src="<?= base_url($employee['photo']) ?>" width="100"></div>
                    <?php endif; ?>
                    <input type="file" class="form-control" name="photo" accept="image/*">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Address Proof Type</label>
                    <select name="address_proof_type" class="form-select">
                        <option value="Aadhar" <?= old('address_proof_type', $employee['address_proof_type']) == 'Aadhar' ? 'selected' : '' ?>>Aadhar Card</option>
                        <option value="Voter ID" <?= old('address_proof_type', $employee['address_proof_type']) == 'Voter ID' ? 'selected' : '' ?>>Voter ID</option>
                        <option value="Driving License" <?= old('address_proof_type', $employee['address_proof_type']) == 'Driving License' ? 'selected' : '' ?>>Driving License</option>
                        <option value="Passport" <?= old('address_proof_type', $employee['address_proof_type']) == 'Passport' ? 'selected' : '' ?>>Passport</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Proof Number</label>
                    <input type="text" class="form-control" name="address_proof_number" value="<?= old('address_proof_number', $employee['address_proof_number']) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Proof Front Image</label>
                    <?php if($employee['address_proof_front_image']): ?>
                        <div class="mb-2"><img src="<?= base_url($employee['address_proof_front_image']) ?>" width="100"></div>
                    <?php endif; ?>
                    <input type="file" class="form-control" name="address_proof_front_image" accept="image/*">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Proof Back Image</label>
                    <?php if($employee['address_proof_back_image']): ?>
                        <div class="mb-2"><img src="<?= base_url($employee['address_proof_back_image']) ?>" width="100"></div>
                    <?php endif; ?>
                    <input type="file" class="form-control" name="address_proof_back_image" accept="image/*">
                </div>
            </div>

        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-warning">Update Employee</button>
        </div>
    </form>
</div>

<!-- Salary Increment History -->
<?php if (!empty($increments)): ?>
<div class="card card-info card-outline mt-3">
    <div class="card-header">
        <h3 class="card-title">Salary Increment History</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Effective Date</th>
                    <th>Old Salary</th>
                    <th>New Salary</th>
                    <th>Increment</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($increments as $inc): ?>
                <tr>
                    <td><?= date('d M Y', strtotime($inc['effective_date'])) ?></td>
                    <td>₹<?= number_format($inc['old_salary'], 2) ?></td>
                    <td>₹<?= number_format($inc['new_salary'], 2) ?></td>
                    <td class="<?= ($inc['new_salary'] > $inc['old_salary']) ? 'text-success' : 'text-danger' ?>">
                        <?= ($inc['new_salary'] > $inc['old_salary']) ? '+' : '' ?><?= number_format($inc['new_salary'] - $inc['old_salary'], 2) ?>
                    </td>
                    <td><?= esc($inc['remarks']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
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
