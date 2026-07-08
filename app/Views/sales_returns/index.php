<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Sales Returns<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Sales Returns (Credit Notes)</h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('sales_returns/create') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Record Return</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary mb-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter me-1"></i> Filters</h3>
    </div>
    <div class="card-body">
        <form action="<?= site_url('sales_returns') ?>" method="get" class="row g-3">
            <div class="col-md-3">
                <label for="customer_id" class="form-label">Customer</label>
                <select name="customer_id" id="customer_id" class="form-select select2">
                    <option value="">All Customers</option>
                    <?php foreach($customers as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $filters['customer_id'] == $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="Open" <?= $filters['status'] == 'Open' ? 'selected' : '' ?>>Open</option>
                    <option value="Closed" <?= $filters['status'] == 'Closed' ? 'selected' : '' ?>>Closed</option>
                    <option value="Void" <?= $filters['status'] == 'Void' ? 'selected' : '' ?>>Void</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" name="date_from" id="date_from" class="form-control" value="<?= $filters['date_from'] ?>">
            </div>
            <div class="col-md-2">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" name="date_to" id="date_to" class="form-control" value="<?= $filters['date_to'] ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <div class="btn-group w-100">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                    <a href="<?= site_url('sales_returns') ?>" class="btn btn-secondary"><i class="fas fa-undo"></i> Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card card-outline card-primary">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="returnsTable">
                <thead>
                    <tr>
                        <th>Return #</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Invoice #</th>
                        <th class="text-end">Amount</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($returns)): ?>
                        <?php foreach($returns as $ret): ?>
                        <tr>
                            <td><?= esc($ret['return_number']) ?></td>
                            <td><?= date('d M, Y', strtotime($ret['return_date'])) ?></td>
                            <td><?= esc($ret['customer_name']) ?></td>
                            <td><?= esc($ret['invoice_number'] ?: '-') ?></td>
                            <td class="text-end fw-bold">₹<?= number_format($ret['total_amount'], 2) ?></td>
                            <td class="text-center">
                                <span class="badge bg-<?= $ret['status'] == 'Open' ? 'info' : ($ret['status'] == 'Void' ? 'danger' : 'success') ?>">
                                    <?= esc($ret['status']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="<?= site_url('sales_returns/view/'.$ret['id']) ?>" class="btn btn-sm btn-info" title="View"><i class="fas fa-eye"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">No returns found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
