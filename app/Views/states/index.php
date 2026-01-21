<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>States<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><?= $title ?></h1>
            </div>
            <div class="col-sm-6 text-end">
                <a href="<?= site_url('states/create') ?>" class="btn btn-primary">+ Add New State</a>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Country</th>
                            <th>State Code</th>
                            <th>GST State Code</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($states as $state): ?>
                        <tr>
                            <td><?= $state['id'] ?></td>
                            <td><?= $state['name'] ?></td>
                            <td><?= $state['country_name'] ?></td>
                            <td><?= $state['state_code'] ?></td>
                            <td><?= $state['gst_state_code'] ?></td>
                            <td>
                                <span class="badge bg-<?= $state['status'] == 'active' ? 'success' : 'danger' ?>">
                                    <?= ucfirst($state['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= site_url('states/edit/'.$state['id']) ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="<?= site_url('states/delete/'.$state['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
