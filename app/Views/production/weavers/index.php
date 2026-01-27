<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Weavers<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">Weaver Management</h1>
    </div>
    <div class="col-sm-6 text-end">
        <?php if(in_array('weaver.create', session('permissions') ?? [])): ?>
        <a href="<?= site_url('production/weavers/create') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Weaver</a>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">List of Weavers</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="weaversTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($weavers)): ?>
                        <?php foreach($weavers as $weaver): ?>
                        <tr>
                            <td><?= $weaver['id'] ?></td>
                            <td><strong><?= esc($weaver['name']) ?></strong></td>
                            <td><?= esc($weaver['code']) ?></td>
                            <td><?= esc($weaver['phone']) ?></td>
                            <td>
                                <span class="badge bg-<?= $weaver['status'] === 'active' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($weaver['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if(in_array('weaver.edit', session('permissions') ?? [])): ?>
                                <a href="<?= site_url('production/weavers/edit/'.$weaver['id']) ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                                <?php if(in_array('weaver.delete', session('permissions') ?? [])): ?>
                                <a href="<?= site_url('production/weavers/delete/'.$weaver['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No weavers found</td>
                        </tr>
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
        $('#weaversTable').DataTable();
    });
</script>
<?= $this->endSection() ?>
