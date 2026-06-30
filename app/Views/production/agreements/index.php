<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Agreements<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">Production Agreements</h1>
    </div>
    <div class="col-sm-6 text-end">
        <?php if(in_array('weaver.create', session('permissions') ?? [])): ?>
        <a href="<?= site_url('production/agreements/create') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Agreement</a>
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
        <form action="<?= site_url('production/agreements') ?>" method="get" class="row g-3">
            <div class="col-md-6">
                <label for="search" class="form-label">Search</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Search by title, party name..." value="<?= esc($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" <?= ($filters['status'] ?? '') == 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="expired" <?= ($filters['status'] ?? '') == 'expired' ? 'selected' : '' ?>>Expired</option>
                    <option value="terminated" <?= ($filters['status'] ?? '') == 'terminated' ? 'selected' : '' ?>>Terminated</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <div class="btn-group w-100">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                    <a href="<?= site_url('production/agreements') ?>" class="btn btn-secondary"><i class="fas fa-undo"></i> Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">List of Agreements</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="agreementsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Party Name</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($agreements)): ?>
                        <?php foreach($agreements as $agreement): ?>
                        <tr>
                            <td><?= $agreement['id'] ?></td>
                            <td><strong><?= esc($agreement['title']) ?></strong></td>
                            <td><?= esc($agreement['party_name']) ?></td>
                            <td><?= esc($agreement['start_date'] ?: '-') ?></td>
                            <td><?= esc($agreement['end_date'] ?: '-') ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    if ($agreement['status'] === 'active') echo 'success';
                                    elseif ($agreement['status'] === 'expired') echo 'warning';
                                    else echo 'danger';
                                ?>">
                                    <?= ucfirst($agreement['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= site_url('production/agreements/view/'.$agreement['id']) ?>" class="btn btn-sm btn-info" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if(in_array('weaver.edit', session('permissions') ?? [])): ?>
                                <a href="<?= site_url('production/agreements/edit/'.$agreement['id']) ?>" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                                <?php if(in_array('weaver.delete', session('permissions') ?? [])): ?>
                                <a href="<?= site_url('production/agreements/delete/'.$agreement['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')" title="Delete">
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
        $('#agreementsTable').DataTable();
    });
</script>
<?= $this->endSection() ?>
