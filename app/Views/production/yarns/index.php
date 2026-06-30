<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Yarns Stock<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">Yarn Stock Management</h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="btn-group">
            <a href="<?= site_url('production/yarns/master') ?>" class="btn btn-outline-primary"><i class="fas fa-cog"></i> Manage Predefined Yarns</a>
            <?php if(in_array('weaver.create', session('permissions') ?? [])): ?>
            <a href="<?= site_url('production/yarns/create') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Yarn Stock</a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary mb-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter me-1"></i> Filters</h3>
    </div>
    <div class="card-body">
        <form action="<?= site_url('production/yarns') ?>" method="get" class="row g-3">
            <div class="col-md-5">
                <label for="search" class="form-label">Search</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Search by name, count, color, brand..." value="<?= esc($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label for="yarn_type" class="form-label">Yarn Type</label>
                <select name="yarn_type" id="yarn_type" class="form-select">
                    <option value="">All Types</option>
                    <option value="Dyed" <?= ($filters['yarn_type'] ?? '') == 'Dyed' ? 'selected' : '' ?>>Dyed</option>
                    <option value="Raw" <?= ($filters['yarn_type'] ?? '') == 'Raw' ? 'selected' : '' ?>>Raw</option>
                </select>
            </div>
            <div class="col-md-2">
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
                    <a href="<?= site_url('production/yarns') ?>" class="btn btn-secondary"><i class="fas fa-undo"></i> Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Yarn Stock Records</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="yarnsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Yarn Name</th>
                        <th>Count</th>
                        <th>Type</th>
                        <th>Color</th>
                        <th>Brand / Mill</th>
                        <th>Stock (KGs)</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($yarns)): ?>
                        <?php foreach($yarns as $yarn): ?>
                        <tr>
                            <td><?= $yarn['id'] ?></td>
                            <td><strong><?= esc($yarn['name']) ?></strong></td>
                            <td><span class="badge bg-secondary"><?= esc($yarn['yarn_count']) ?></span></td>
                            <td>
                                <span class="badge bg-<?= $yarn['yarn_type'] === 'Dyed' ? 'info' : 'light text-dark border' ?>">
                                    <?= esc($yarn['yarn_type']) ?>
                                </span>
                            </td>
                            <td><?= esc($yarn['color'] ?: '-') ?></td>
                            <td><?= esc($yarn['brand'] ?: '-') ?></td>
                            <td><strong class="text-primary"><?= number_format($yarn['stock_kg'], 2) ?> KGs</strong></td>
                            <td>
                                <span class="badge bg-<?= $yarn['status'] === 'active' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($yarn['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= site_url('production/yarns/view/'.$yarn['id']) ?>" class="btn btn-sm btn-info" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if(in_array('weaver.edit', session('permissions') ?? [])): ?>
                                <a href="<?= site_url('production/yarns/edit/'.$yarn['id']) ?>" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                                <?php if(in_array('weaver.delete', session('permissions') ?? [])): ?>
                                <a href="<?= site_url('production/yarns/delete/'.$yarn['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')" title="Delete">
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
        $('#yarnsTable').DataTable();
    });
</script>
<?= $this->endSection() ?>
