<?= $this->extend('layouts/master') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark"><?= $title ?></h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('settings') ?>">Settings</a></li>
                <li class="breadcrumb-item active">Taxes</li>
            </ol>
        </div>
    </div>

    <!-- Main content -->
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Manage Taxes</h3>
            <div class="card-tools">
                <?php if(in_array('tax.create', session('permissions') ?? [])): ?>
                <a href="<?= base_url('settings/taxes/new') ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> New Tax
                </a>
                <?php endif; ?>
            </div>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <table class="table table-bordered table-striped" id="taxesTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Percentage</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($taxes as $tax): ?>
                        <tr>
                            <td><?= esc($tax['name']) ?></td>
                            <td><?= esc($tax['percentage']) ?>%</td>
                            <td>
                                <?php if ($tax['status'] == 'Active'): ?>
                                    <span class="badge text-bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if(in_array('tax.edit', session('permissions') ?? [])): ?>
                                <a href="<?= base_url('settings/taxes/edit/' . $tax['id']) ?>" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                                <?php if(in_array('tax.delete', session('permissions') ?? [])): ?>
                                <a href="<?= base_url('settings/taxes/delete/' . $tax['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <!-- /.card-body -->
    </div>
</div>
<script>
    $(function () {
        $("#taxesTable").DataTable({
            "responsive": true,
            "autoWidth": false,
        });
    });
</script>
<?= $this->endSection() ?>
