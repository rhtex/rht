<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Transports<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><?= $title ?></h1>
            </div>
            <div class="col-sm-6 text-end">
                <a href="<?= site_url('transports/create') ?>" class="btn btn-primary">+ Add New Transport</a>
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
                            <th>Transport Name</th>
                            <th>Code</th>
                            <th>Branch</th>
                            <th>Phone</th>
                            <th>GST</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($transports as $transport): ?>
                        <tr>
                            <td><?= $transport['id'] ?></td>
                            <td><?= $transport['transport_name'] ?></td>
                            <td><?= $transport['transport_code'] ?></td>
                            <td><?= $transport['branch'] ?></td>
                            <td><?= $transport['branch_phone_number'] ?></td>
                            <td><?= $transport['branch_gst_number'] ?></td>
                            <td>
                                <a href="<?= site_url('transports/edit/'.$transport['id']) ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="<?= site_url('transports/delete/'.$transport['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
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
