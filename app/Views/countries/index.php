<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Countries<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><?= $title ?></h1>
            </div>
            <div class="col-sm-6 text-end">
                <a href="<?= site_url('countries/create') ?>" class="btn btn-primary">+ Add New Country</a>
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
                            <th>ISO Code 2</th>
                            <th>ISO Code 3</th>
                            <th>Phone Code</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($countries as $country): ?>
                        <tr>
                            <td><?= $country['id'] ?></td>
                            <td><?= $country['name'] ?></td>
                            <td><?= $country['iso_code_2'] ?></td>
                            <td><?= $country['iso_code_3'] ?></td>
                            <td><?= $country['phone_code'] ?></td>
                            <td>
                                <a href="<?= site_url('countries/edit/'.$country['id']) ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="<?= site_url('countries/delete/'.$country['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
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
