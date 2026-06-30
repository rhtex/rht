<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Job Work Vendors<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">Job Work Vendor Management</h1>
    </div>
    <div class="col-sm-6 text-end">
        <?php if(in_array('weaver.create', session('permissions') ?? [])): ?>
        <a href="<?= site_url('production/vendors/create') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Vendor</a>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary mb-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter me-1"></i> Filters</h3>
    </div>
    <div class="card-body">
        <form action="<?= site_url('production/vendors') ?>" method="get" class="row g-3">
            <div class="col-md-6">
                <label for="search" class="form-label">Search</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Search by name, GST, PAN, or phone..." value="<?= esc($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" <?= ($filters['status'] ?? '') == 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($filters['status'] ?? '') == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <div class="btn-group w-100">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                    <a href="<?= site_url('production/vendors') ?>" class="btn btn-secondary"><i class="fas fa-undo"></i> Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">List of Job Work Vendors</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="vendorsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Business Type</th>
                        <th>GST Number</th>
                        <th>PAN Number</th>
                        <th>Phone</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($vendors)): ?>
                        <?php foreach($vendors as $vendor): ?>
                        <tr>
                            <td><?= $vendor['id'] ?></td>
                            <td><strong><?= esc($vendor['name']) ?></strong></td>
                            <td><?= esc($vendor['business_type'] ?: '-') ?></td>
                            <td><?= esc($vendor['gst_number'] ?: '-') ?></td>
                            <td><?= esc($vendor['pan_number'] ?: '-') ?></td>
                            <td><?= esc($vendor['phone'] ?: '-') ?></td>
                            <td>
                                <?php if(!empty($vendor['location'])): ?>
                                    <a href="<?= esc($vendor['location']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-map-marker-alt"></i> Open Link
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $vendor['status'] === 'active' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($vendor['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= site_url('production/vendors/view/'.$vendor['id']) ?>" class="btn btn-sm btn-info" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if(in_array('weaver.edit', session('permissions') ?? [])): ?>
                                <a href="<?= site_url('production/vendors/edit/'.$vendor['id']) ?>" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                                <?php if(in_array('weaver.delete', session('permissions') ?? [])): ?>
                                <a href="<?= site_url('production/vendors/delete/'.$vendor['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $('#vendorsTable').DataTable();
    });
</script>
<?= $this->endSection() ?>
