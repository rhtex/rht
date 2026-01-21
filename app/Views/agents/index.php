<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Agents<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><?= $title ?></h1>
            </div>
            <div class="col-sm-6 text-end">
                <a href="<?= site_url('agents/create') ?>" class="btn btn-primary">+ Add New Agent</a>
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
                            <th>Agent Name</th>
                            <th>Phone</th>
                            <th>City</th>
                            <th>State</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($agents as $agent): ?>
                        <tr>
                            <td><?= $agent['id'] ?></td>
                            <td><?= $agent['agent_name'] ?></td>
                            <td><?= $agent['phone_number'] ?></td>
                            <td><?= $agent['city'] ?></td>
                            <td><?= $agent['state_name'] ?></td>
                            <td>
                                <a href="<?= site_url('agents/edit/'.$agent['id']) ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="<?= site_url('agents/delete/'.$agent['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
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
