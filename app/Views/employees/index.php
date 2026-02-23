<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Employees<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Employee Management</h1>
    </div>
    <div class="col-sm-6">
        <a href="<?= site_url('employees/create') ?>" class="btn btn-primary float-sm-end">
            <i class="fas fa-plus"></i> Add New Employee
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary mb-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter me-1"></i> Filters</h3>
    </div>
    <div class="card-body">
        <form action="<?= site_url('employees') ?>" method="get" class="row g-3">
            <div class="col-md-5">
                <label for="search" class="form-label">Search</label>
                <input type="text" name="search" id="search" class="form-control"
                    placeholder="Search by name, phone, or email..." value="<?= $filters['search'] ?>">
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" <?= $filters['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $filters['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="employment_type" class="form-label">Type</label>
                <select name="employment_type" id="employment_type" class="form-select">
                    <option value="">All Types</option>
                    <option value="Permanent" <?= $filters['employment_type'] == 'Permanent' ? 'selected' : '' ?>>Permanent
                    </option>
                    <option value="Temporary" <?= $filters['employment_type'] == 'Temporary' ? 'selected' : '' ?>>Temporary
                    </option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <div class="btn-group w-100">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                    <a href="<?= site_url('employees') ?>" class="btn btn-secondary"><i class="fas fa-undo"></i>
                        Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">List of Employees</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Mobile</th>
                        <th>Joining Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($employees)): ?>
                        <?php foreach ($employees as $employee): ?>
                            <tr>
                                <td><?= $employee['id'] ?></td>
                                <td>
                                    <?php if ($employee['photo']): ?>
                                        <img src="<?= base_url($employee['photo']) ?>" alt="Photo" class="img-thumbnail" width="50">
                                    <?php else: ?>
                                        <span class="text-muted">No Photo</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $employee['first_name'] . ' ' . $employee['last_name'] ?></td>
                                <td>
                                    <span class="badge text-bg-info">
                                        <?= lang('App.' . strtolower($employee['employment_type'] ?? 'permanent')) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= $employee['city'] ?>, <?= $employee['state_name'] ?><br>
                                    <small class="text-muted"><?= $employee['country_name'] ?></small>
                                </td>
                                <td><?= $employee['mobile_number'] ?></td>
                                <td><?= $employee['joining_date'] ?></td>
                                <td>
                                    <span class="badge text-bg-<?= $employee['status'] == 'active' ? 'success' : 'danger' ?>">
                                        <?= ucfirst($employee['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= site_url('employees/view/' . $employee['id']) ?>" class="btn btn-sm btn-info"
                                        title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= site_url('employees/edit/' . $employee['id']) ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if (!$employee['user_id']): ?>
                                        <a href="<?= site_url('users/convert/' . $employee['id']) ?>" class="btn btn-sm btn-info"
                                            title="<?= lang('App.convert_to_user') ?>">
                                            <i class="fas fa-user-plus"></i>
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-secondary" disabled title="<?= lang('App.already_a_user') ?>">
                                            <i class="fas fa-user-check"></i>
                                        </button>
                                    <?php endif; ?>
                                    <a href="<?= site_url('employees/delete/' . $employee['id']) ?>" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No employees found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>