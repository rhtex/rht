<?php include __DIR__ . '/../layouts/head.php'; ?>
<?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>

    
        <h4>Edit Employee</h4>
        <form action="/update_employee" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $employee['id'] ?>">

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name"
                            value="<?= $employee['first_name'] ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name"
                            value="<?= $employee['last_name'] ?>" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="guardian_name" class="form-label">Guardian Name</label>
                        <input type="text" class="form-control" id="guardian_name" name="guardian_name"
                            value="<?= $employee['guardian_name'] ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="guardian_type" class="form-label">Guardian Type</label>
                        <input type="text" class="form-control" id="guardian_type" name="guardian_type"
                            value="<?= $employee['guardian_type'] ?>" required>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label for="designation" class="form-label">Designation</label>
                <input type="text" class="form-control" id="designation" name="designation"
                    value="<?= $employee['designation'] ?>" required>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="guardian_mobile" class="form-label">Guardian Mobile Number</label>
                        <input type="text" class="form-control" id="guardian_mobile" name="guardian_mobile"
                            value="<?= $employee['guardian_mobile'] ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="employee_mobile" class="form-label">Employee Mobile Number</label>
                        <input type="text" class="form-control" id="employee_mobile" name="employee_mobile"
                            value="<?= $employee['employee_mobile'] ?>" required>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= $employee['email'] ?>"
                    required>
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control" id="address" name="address" rows="3"
                    required><?= $employee['address'] ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="address_proof_no" class="form-label">Address Proof Number</label>
                        <input type="text" class="form-control" id="address_proof_no" name="address_proof_no"
                            value="<?= $employee['address_proof_no'] ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="address_proof_type" class="form-label">Address Proof Type</label>
                        <input type="text" class="form-control" id="address_proof_type" name="address_proof_type"
                            value="<?= $employee['address_proof_type'] ?>" required>
                    </div>
                </div>
            </div>

            <input type="hidden" name="existing_address_proof" value="<?= $employee['address_proof_upload'] ?>">
            <input type="hidden" name="existing_employee_photo" value="<?= $employee['employee_photo_upload'] ?>">

            <div class="mb-3">
                <label for="address_proof_upload" class="form-label">Address Proof Upload</label>
                <input type="file" class="form-control" id="address_proof_upload" name="address_proof_upload">
            </div>

            <div class="mb-3">
                <label for="employee_photo_upload" class="form-label">Employee Photo Upload</label>
                <input type="file" class="form-control" id="employee_photo_upload" name="employee_photo_upload">
            </div>
            <div class="row">
                <div class="col-md-6">
                    <!-- Inactive Date -->
                    <div class="mb-3">
                        <label for="inactive_date" class="form-label">Inactive Date</label>
                        <input type="date" class="form-control" id="inactive_date" name="inactive_date"
                            value="<?= $employee['inactive_date'] ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <!-- Status -->
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="active" <?= $employee['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $employee['status'] == 'inactive' ? 'selected' : '' ?>>Inactive
                            </option>
                        </select>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>