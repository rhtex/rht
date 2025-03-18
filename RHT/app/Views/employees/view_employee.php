<?php include __DIR__ . '/../layouts/head.php'; ?>

<?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>





<div class="row mb-3">

    <div class="col-md-6">

        <h4> View Employee </h4>

    </div>

    <div class="col-md-3 text-right">

        <a href="/edit_employee/<?= $employee['id'] ?>" class="btn btn-primary mr-2">Edit</a>

        <a href="<?= site_url('employee/createUserFromEmployee/' . $employee['id']); ?>"
            class="btn btn-primary" style="<?= $userExists ? 'display: none' : ''; ?>">
            Create Login
        </a>


    </div>

</div>

<?php if (session()->getFlashdata('success')): ?>

    <div class="alert alert-success">

        <?= session()->getFlashdata('success'); ?>

    </div>

<?php endif; ?>



<?php if (session()->getFlashdata('error')): ?>

    <div class="alert alert-danger">

        <?= session()->getFlashdata('error'); ?>

    </div>

<?php endif; ?>

<div class="card mb-3">

    <div class="card-body">

        <h5 class="card-title">Personal Information</h5>

        <table id="employee-table" class="display table table-bordered" style="width:100%">

            <tbody>

                <tr>

                    <th>Name:</th>

                    <td>

                        <?= $employee['first_name'] ?>

                        <?= $employee['last_name'] ?>

                    </td>

                </tr>

                <tr>

                    <th>Designation:</th>

                    <td>

                        <?= $employee['designation'] ?>

                    </td>

                </tr>

                <tr>

                    <th>Guardian Name:</th>

                    <td>

                        <?= $employee['guardian_name'] ?>

                    </td>

                </tr>

                <tr>

                    <th>Guardian Type:</th>

                    <td>

                        <?= $employee['guardian_type'] ?>

                    </td>

                </tr>

                <tr>

                    <th>Employee Mobile:</th>

                    <td>

                        <?= $employee['employee_mobile'] ?>

                    </td>

                </tr>

                <tr>

                    <th>Guardian Mobile:</th>

                    <td>

                        <?= $employee['guardian_mobile'] ?>

                    </td>

                </tr>

                <tr>

                    <th>Status:</th>

                    <td>

                        <?= $employee['status'] ?>

                    </td>

                </tr>

                <tr>

                    <th>Joining Date:</th>

                    <td>

                        <?= $employee['created_at'] ?>

                    </td>

                </tr>

                <tr>

                    <th>Inactive Date:</th>

                    <td>

                        <?= $employee['inactive_date'] ?>

                    </td>

                </tr>

                <tr>

                    <th>Email:</th>

                    <td>

                        <?= $employee['email'] ?>

                    </td>

                </tr>

                <tr>

                    <th>Address:</th>

                    <td>

                        <?= nl2br($employee['address']) ?>

                    </td>

                </tr>

            </tbody>

        </table>

    </div>

</div>



<div class="card mb-3">

    <div class="card-body">

        <h5 class="card-title">Additional Information</h5>

        <p><strong>Address Proof Type:</strong>

            <?= $employee['address_proof_type'] ?>

        </p>

        <p><strong>Address Proof Number:</strong>

            <?= $employee['address_proof_no'] ?>

        </p>



        <?php if (!empty($employee['address_proof_upload'])): ?>

            <p><strong>Address Proof Image:</strong></p>

            <img width="30%" src="<?= base_url($employee['address_proof_upload']) ?>" class="img-thumbnail"

                alt="Address Proof Image">

        <?php endif; ?>



        <?php if (!empty($employee['employee_photo_upload'])): ?>

            <p><strong>Employee Photo:</strong></p>

            <img width="30%" src="<?= base_url($employee['employee_photo_upload']) ?>" class="img-thumbnail"

                alt="Employee Photo">

        <?php endif; ?>

    </div>

</div>



<a href="/list_employees" class="btn btn-primary">Back to List</a>

</div>

</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>