<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Customers<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Customer Management</h1>
    </div>
    <div class="col-sm-6 text-end">
        <?php if(in_array('zoho.sync', session('permissions') ?? [])): ?>
        <a href="<?= site_url('customers/sync-zoho') ?>" class="btn btn-info me-2"><i class="fas fa-sync"></i> Fetch from Zoho</a>
        <?php endif; ?>
        <?php if(in_array('customer.create', session('permissions') ?? [])): ?>
        <a href="<?= site_url('customers/create') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Customer</a>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">List of Customers</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer Name</th>
                        <th>Contact</th>
                        <th>GST Type</th>
                        <th>GSTIN</th>
                        <th>State</th>
                        <th>Opening Balance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($customers)): ?>
                        <?php foreach($customers as $customer): ?>
                        <tr>
                            <td><?= $customer['id'] ?></td>
                            <td>
                                <strong><a href="<?= site_url('customers/view/'.$customer['id']) ?>" class="text-dark"><?= esc($customer['name']) ?></a></strong><br>
                                <small class="text-muted"><?= esc($customer['contact_person']) ?></small>
                            </td>
                            <td>
                                <?= esc($customer['phone']) ?><br>
                                <small><?= esc($customer['email']) ?></small>
                            </td>
                            <td><?= esc($customer['gst_type']) ?></td>
                            <td><code><?= esc($customer['gstin'] ?: 'N/A') ?></code></td>
                            <td><?= esc($customer['state_name']) ?></td>
                            <td>
                                <?= number_format($customer['opening_balance'], 2) ?> <?= $customer['balance_type'] ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $customer['status'] === 'active' ? 'success' : 'danger' ?>">
                                    <?= ucfirst($customer['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= site_url('customers/edit/'.$customer['id']) ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?= site_url('customers/view/'.$customer['id']) ?>" class="btn btn-sm btn-info text-white">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?= site_url('customers/delete/'.$customer['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">No customers found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
