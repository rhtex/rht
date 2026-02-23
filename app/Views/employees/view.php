<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>View Employee: <?= $employee['first_name'] . ' ' . $employee['last_name'] ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Employee Profile</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
            <li class="breadcrumb-item"><a href="<?= site_url('employees') ?>">Employees</a></li>
            <li class="breadcrumb-item active">View</li>
        </ol>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-3">
        <!-- Profile Image -->
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center mb-3">
                    <?php if ($employee['photo']): ?>
                        <img class="profile-user-img img-fluid img-circle" src="<?= base_url($employee['photo']) ?>" alt="User profile picture">
                    <?php else: ?>
                        <img class="profile-user-img img-fluid img-circle" src="<?= base_url('assets/img/default-avatar.png') ?>" alt="Default avatar">
                    <?php endif; ?>
                </div>

                <h3 class="profile-username text-center"><?= $employee['first_name'] . ' ' . $employee['last_name'] ?></h3>
                <p class="text-muted text-center"><?= $employee['employment_type'] ?></p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Status</b> <a class="float-end"><span class="badge text-bg-<?= $employee['status'] == 'active' ? 'success' : 'danger' ?>"><?= ucfirst($employee['status']) ?></span></a>
                    </li>
                    <li class="list-group-item">
                        <b>Joining Date</b> <a class="float-end"><?= $employee['joining_date'] ?></a>
                    </li>
                    <li class="list-group-item">
                        <b>Basic Salary</b> <a class="float-end">₹<?= number_format($employee['basic_salary'], 2) ?></a>
                    </li>
                </ul>

                <a href="<?= site_url('employees/edit/' . $employee['id']) ?>" class="btn btn-primary d-block"><b>Edit Profile</b></a>
            </div>
        </div>

        <!-- About Me Box -->
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Contact Info</h3>
            </div>
            <div class="card-body">
                <strong><i class="fas fa-phone mr-1"></i> Mobile</strong>
                <p class="text-muted"><?= $employee['mobile_number'] ?></p>
                <hr>
                <strong><i class="fas fa-envelope mr-1"></i> Email</strong>
                <p class="text-muted"><?= $employee['email'] ?: 'N/A' ?></p>
                <hr>
                <strong><i class="fas fa-map-marker-alt mr-1"></i> Address</strong>
                <p class="text-muted">
                    <?= $employee['address_line_1'] ?><br>
                    <?= $employee['address_line_2'] ? ($employee['address_line_2'] . '<br>') : '' ?>
                    <?= $employee['city'] ?>, <?= $employee['state_name'] ?> - <?= $employee['pincode'] ?><br>
                    <?= $employee['country_name'] ?>
                </p>
            </div>
        </div>
    </div>

    <div class="col-md-9">
        <div class="card card-outline card-primary">
            <div class="card-header p-2">
                <ul class="nav nav-pills" id="employeeTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab" aria-controls="details" aria-selected="true">Details</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="salaries-tab" data-bs-toggle="tab" data-bs-target="#salaries" type="button" role="tab" aria-controls="salaries" aria-selected="false">Payslips</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="loans-tab" data-bs-toggle="tab" data-bs-target="#loans" type="button" role="tab" aria-controls="loans" aria-selected="false">Loans</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="increments-tab" data-bs-toggle="tab" data-bs-target="#increments" type="button" role="tab" aria-controls="increments" aria-selected="false">Salary History</button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="employeeTabsContent">
                    <!-- Details Tab -->
                    <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Guardian Information</h5>
                                <table class="table table-sm">
                                    <tr><th>Relation</th><td><?= $employee['guardian_type'] ?></td></tr>
                                    <tr><th>Name</th><td><?= $employee['guardian_first_name'] . ' ' . $employee['guardian_last_name'] ?></td></tr>
                                    <tr><th>Mobile</th><td><?= $employee['guardian_mobile_number'] ?></td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h5>KYC Documents</h5>
                                <table class="table table-sm">
                                    <tr><th>Proof Type</th><td><?= $employee['address_proof_type'] ?></td></tr>
                                    <tr><th>Proof Number</th><td><?= $employee['address_proof_number'] ?></td></tr>
                                </table>
                                <div class="row">
                                    <?php if ($employee['address_proof_front_image']): ?>
                                    <div class="col-6 text-center">
                                        <small>Front</small><br>
                                        <a href="<?= base_url($employee['address_proof_front_image']) ?>" target="_blank">
                                            <img src="<?= base_url($employee['address_proof_front_image']) ?>" class="img-thumbnail" style="max-height: 100px;">
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($employee['address_proof_back_image']): ?>
                                    <div class="col-6 text-center">
                                        <small>Back</small><br>
                                        <a href="<?= base_url($employee['address_proof_back_image']) ?>" target="_blank">
                                            <img src="<?= base_url($employee['address_proof_back_image']) ?>" class="img-thumbnail" style="max-height: 100px;">
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payslips Tab -->
                    <div class="tab-pane fade" id="salaries" role="tabpanel" aria-labelledby="salaries-tab">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Month</th>
                                        <th>Basic</th>
                                        <th>Allowances</th>
                                        <th>Deductions</th>
                                        <th>Net Salary</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($salaries)): ?>
                                        <?php foreach ($salaries as $salary): ?>
                                        <tr>
                                            <td><?= date('F Y', strtotime($salary['salary_month'])) ?></td>
                                            <td>₹<?= number_format($salary['basic_salary'], 2) ?></td>
                                            <td>₹<?= number_format($salary['allowances'], 2) ?></td>
                                            <td>₹<?= number_format($salary['deductions'], 2) ?></td>
                                            <td>₹<?= number_format($salary['net_salary'], 2) ?></td>
                                            <td>
                                                <span class="badge text-bg-<?= $salary['is_paid'] ? 'success' : 'warning' ?>">
                                                    <?= $salary['is_paid'] ? 'Paid (' . $salary['paid_date'] . ')' : 'Unpaid' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?= site_url('salaries/payslip/' . $salary['id']) ?>" class="btn btn-xs btn-info" target="_blank">
                                                    <i class="fas fa-file-invoice-dollar"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="7" class="text-center">No salary records found</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Loans Tab -->
                    <div class="tab-pane fade" id="loans" role="tabpanel" aria-labelledby="loans-tab">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Deduction/Mo</th>
                                        <th>Remaining</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($loans)): ?>
                                        <?php foreach ($loans as $loan): ?>
                                        <tr>
                                            <td><?= $loan['loan_date'] ?></td>
                                            <td>₹<?= number_format($loan['loan_amount'], 2) ?></td>
                                            <td>₹<?= number_format($loan['monthly_deduction'], 2) ?></td>
                                            <td>₹<?= number_format($loan['remaining_amount'], 2) ?></td>
                                            <td>
                                                <span class="badge text-bg-<?= ($loan['status'] == 'Approved' || $loan['status'] == 'Completed') ? 'success' : ($loan['status'] == 'Pending' ? 'warning' : 'danger') ?>">
                                                    <?= $loan['status'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?= site_url('loans/view/' . $loan['id']) ?>" class="btn btn-xs btn-info">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center">No loan records found</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Salary History Tab -->
                    <div class="tab-pane fade" id="increments" role="tabpanel" aria-labelledby="increments-tab">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Old Salary</th>
                                        <th>New Salary</th>
                                        <th>Increment</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($increments)): ?>
                                        <?php foreach ($increments as $inc): ?>
                                        <tr>
                                            <td><?= $inc['effective_date'] ?></td>
                                            <td>₹<?= number_format($inc['old_salary'], 2) ?></td>
                                            <td>₹<?= number_format($inc['new_salary'], 2) ?></td>
                                            <td class="text-success font-weight-bold">+₹<?= number_format($inc['new_salary'] - $inc['old_salary'], 2) ?></td>
                                            <td><?= $inc['remarks'] ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" class="text-center">No salary history records found</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
