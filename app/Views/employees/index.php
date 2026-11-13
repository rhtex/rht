<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Employees<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-user-tie text-primary me-2"></i>Employee Management</h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex gap-2 align-items-center">
            <a href="<?= site_url('employees') ?>" class="btn btn-sm btn-outline-secondary" title="Refresh List">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </a>
            <?php if (can('employee.create')): ?>
            <a href="<?= site_url('employees/create') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Add New Employee
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Summary Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('employees') ?>" class="bt-stat bt-g-blue text-decoration-none <?= empty($filters['status']) ? 'active' : '' ?>">
            <i class="fas fa-users bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['total'] ?? count($employees)) ?></div>
            <div class="bt-stat-label">Total Employees</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('employees?status=active') ?>" class="bt-stat bt-g-green text-decoration-none <?= ($filters['status'] ?? '') === 'active' ? 'active' : '' ?>">
            <i class="fas fa-user-check bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['active'] ?? 0) ?></div>
            <div class="bt-stat-label">Active Staff</div>
            <div class="progress"><div class="progress-bar" style="width:<?= (!empty($stats['total']) && $stats['total'] > 0) ? min(100, round(($stats['active'] / $stats['total']) * 100)) : 0 ?>%"></div></div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('employees?status=inactive') ?>" class="bt-stat bt-g-slate text-decoration-none <?= ($filters['status'] ?? '') === 'inactive' ? 'active' : '' ?>">
            <i class="fas fa-user-slash bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['inactive'] ?? 0) ?></div>
            <div class="bt-stat-label">Inactive Staff</div>
            <div class="progress"><div class="progress-bar" style="width:<?= (!empty($stats['total']) && $stats['total'] > 0) ? min(100, round(($stats['inactive'] / $stats['total']) * 100)) : 0 ?>%"></div></div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-purple" style="cursor:default;">
            <i class="fas fa-briefcase bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['permanent'] ?? 0) ?></div>
            <div class="bt-stat-label">Permanent Employees</div>
            <div class="progress"><div class="progress-bar" style="width:<?= (!empty($stats['total']) && $stats['total'] > 0) ? min(100, round(($stats['permanent'] / $stats['total']) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card card-outline card-primary mb-3 shadow-sm">
    <div class="card-header py-2">
        <h3 class="card-title fw-bold"><i class="fas fa-filter me-1"></i> Filter Employees</h3>
    </div>
    <div class="card-body">
        <form action="<?= site_url('employees') ?>" method="get" class="row g-2 align-items-end">
            <div class="col-md-5 col-sm-12">
                <label for="search" class="form-label">Search</label>
                <input type="text" name="search" id="search" class="form-control"
                    placeholder="Search by name, phone, or email..." value="<?= esc($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-2 col-sm-4">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" <?= ($filters['status'] ?? '') == 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($filters['status'] ?? '') == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-4">
                <label for="employment_type" class="form-label">Type</label>
                <select name="employment_type" id="employment_type" class="form-select">
                    <option value="">All Types</option>
                    <option value="Permanent" <?= ($filters['employment_type'] ?? '') == 'Permanent' ? 'selected' : '' ?>>Permanent</option>
                    <option value="Temporary" <?= ($filters['employment_type'] ?? '') == 'Temporary' ? 'selected' : '' ?>>Temporary</option>
                </select>
            </div>
            <div class="col-md-3 col-sm-4 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100" title="Apply Filter"><i class="fas fa-search"></i> Search</button>
                <a href="<?= site_url('employees') ?>" class="btn btn-outline-secondary" title="Reset Filters"><i class="fas fa-undo"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Employees Table -->
<div class="card card-outline card-secondary shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th style="width: 60px;">Photo</th>
                        <th style="min-width: 180px;">Employee Name</th>
                        <th style="min-width: 130px;">Contact</th>
                        <th style="min-width: 150px;">Location</th>
                        <th style="min-width: 110px;">Type</th>
                        <th style="min-width: 110px;">Joining Date</th>
                        <th class="text-center" style="min-width: 90px;">Status</th>
                        <th class="text-end" style="min-width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($employees)): ?>
                        <?php foreach ($employees as $employee): ?>
                            <tr>
                                <td class="text-muted fw-semibold"><?= $employee['id'] ?></td>
                                <td>
                                    <?php if (!empty($employee['photo'])): ?>
                                        <img src="<?= base_url($employee['photo']) ?>" alt="Photo" class="rounded-circle shadow-sm" style="width:38px;height:38px;object-fit:cover;">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center shadow-sm" style="width:38px;height:38px;font-size:0.9rem;">
                                            <?= strtoupper(substr($employee['first_name'] ?? 'E', 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= site_url('employees/view/' . $employee['id']) ?>" class="fw-bold text-dark text-decoration-none">
                                        <?= esc($employee['first_name'] . ' ' . $employee['last_name']) ?>
                                    </a>
                                    <?php if (!empty($employee['email'])): ?>
                                        <br><small class="text-muted"><?= esc($employee['email']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($employee['mobile_number'])): ?>
                                        <a href="tel:<?= esc($employee['mobile_number']) ?>" class="text-decoration-none text-dark fw-semibold">
                                            <i class="fas fa-phone-alt text-success me-1 small"></i><?= esc($employee['mobile_number']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($employee['city']) || !empty($employee['state_name'])): ?>
                                        <span><?= esc($employee['city'] ?? '') ?><?= (!empty($employee['city']) && !empty($employee['state_name'])) ? ', ' : '' ?><?= esc($employee['state_name'] ?? '') ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php $type = $employee['employment_type'] ?? 'Permanent'; ?>
                                    <span class="badge <?= $type === 'Permanent' ? 'text-bg-primary' : 'text-bg-warning' ?> px-2 py-1">
                                        <?= esc($type) ?>
                                    </span>
                                </td>
                                <td><?= !empty($employee['joining_date']) ? date('d/m/Y', strtotime($employee['joining_date'])) : '-' ?></td>
                                <td class="text-center">
                                    <span class="badge text-bg-<?= ($employee['status'] ?? '') == 'active' ? 'success' : 'secondary' ?>">
                                        <?= ucfirst($employee['status'] ?? 'inactive') ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= site_url('employees/view/' . $employee['id']) ?>" class="btn btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if (can('employee.edit')): ?>
                                        <a href="<?= site_url('employees/edit/' . $employee['id']) ?>" class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php endif; ?>
                                        <?php if (empty($employee['user_id'])): ?>
                                            <?php if (can('user.create')): ?>
                                            <a href="<?= site_url('users/convert/' . $employee['id']) ?>" class="btn btn-outline-info" title="<?= lang('App.convert_to_user') ?>">
                                                <i class="fas fa-user-plus"></i>
                                            </a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <button class="btn btn-outline-secondary" disabled title="<?= lang('App.already_a_user') ?>">
                                                <i class="fas fa-user-check"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if (can('employee.delete')): ?>
                                        <a href="<?= site_url('employees/delete/' . $employee['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this employee?')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fas fa-user-tie fa-2x text-muted mb-2 d-block"></i>
                                No employees found matching the filters.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
