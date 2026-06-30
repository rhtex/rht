<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Predefined Yarns<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">Manage Predefined Yarns</h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="btn-group">
            <a href="<?= site_url('production/yarns') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Yarns Stock</a>
            <a href="<?= site_url('production/yarns/master/create') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Add Predefined Yarn</a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">List of Predefined Yarns (Yarn Master)</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="masterTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Yarn Name</th>
                        <th>Yarn Count</th>
                        <th>Yarn Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($masterYarns)): ?>
                        <?php foreach($masterYarns as $my): ?>
                        <tr>
                            <td><?= $my['id'] ?></td>
                            <td><strong><?= esc($my['name']) ?></strong></td>
                            <td><span class="badge bg-secondary"><?= esc($my['yarn_count']) ?></span></td>
                            <td>
                                <span class="badge bg-<?= $my['yarn_type'] === 'Dyed' ? 'info' : 'light text-dark border' ?>">
                                    <?= esc($my['yarn_type']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= site_url('production/yarns/master/edit/'.$my['id']) ?>" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?= site_url('production/yarns/master/delete/'.$my['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a>
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
        $('#masterTable').DataTable();
    });
</script>
<?= $this->endSection() ?>
