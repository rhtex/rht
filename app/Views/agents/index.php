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
                            <th>Commission (%)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($agents as $agent): ?>
                        <tr>
                            <td><?= $agent['id'] ?></td>
                            <td><?= $agent['agent_name'] ?></td>
                            <td><?= $agent['phone_number'] ?></td>
                            <td><?= $agent['city'] ?> (<?= $agent['state_name'] ?>)</td>
                            <td><?= number_format($agent['commission_percentage'], 2) ?>%</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= site_url('agents/view/'.$agent['id']) ?>" class="btn btn-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= site_url('agents/edit/'.$agent['id']) ?>" class="btn btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= site_url('agents/delete/'.$agent['id']) ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
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
