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
                <li class="breadcrumb-item active">Bills</li>
            </ol>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card card-outline card-primary mb-3">
        <div class="card-header">
            <h3 class="card-title">Filter Bills</h3>
            <div class="card-tools">
                <a href="<?= site_url('bills/create') ?>" class="btn btn-sm btn-success">
                    <i class="fas fa-plus"></i> New Bill
                </a>
                <a href="<?= site_url('bills/sync-zoho') ?>" class="btn btn-sm btn-info">
                    <i class="fas fa-sync"></i> Sync from Zoho
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Vendor</label>
                    <select name="vendor_id" class="form-select select2">
                        <option value="">All Vendors</option>
                        <?php foreach ($vendors as $vendor): ?>
                            <option value="<?= $vendor['id'] ?>" <?= ($filters['vendor_id'] == $vendor['id']) ? 'selected' : '' ?>>
                                <?= esc($vendor['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="Draft" <?= ($filters['status'] == 'Draft') ? 'selected' : '' ?>>Draft</option>
                        <option value="Open" <?= ($filters['status'] == 'Open') ? 'selected' : '' ?>>Open</option>
                        <option value="Paid" <?= ($filters['status'] == 'Paid') ? 'selected' : '' ?>>Paid</option>
                        <option value="Partially Paid" <?= ($filters['status'] == 'Partially Paid') ? 'selected' : '' ?>>Partially Paid</option>
                        <option value="Overdue" <?= ($filters['status'] == 'Overdue') ? 'selected' : '' ?>>Overdue</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" name="date_from" class="form-control" value="<?= esc($filters['date_from']) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" name="date_to" class="form-control" value="<?= esc($filters['date_to']) ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                    <a href="<?= site_url('bills') ?>" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bills Table -->
    <div class="card card-outline card-secondary">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Bill #</th>
                            <th>Vendor</th>
                            <th>Bill Date</th>
                            <th>Due Date</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th>Zoho</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bills)): ?>
                            <tr>
                                <td colspan="10" class="text-center py-4">No bills found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($bills as $bill): ?>
                                <tr>
                                    <td><strong><?= esc($bill['bill_number']) ?></strong></td>
                                    <td><?= esc($bill['vendor_name']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($bill['bill_date'])) ?></td>
                                    <td><?= date('d/m/Y', strtotime($bill['due_date'])) ?></td>
                                    <td>₹<?= number_format($bill['total_amount'], 2) ?></td>
                                    <td>₹<?= number_format($bill['paid_amount'], 2) ?></td>
                                    <td>₹<?= number_format($bill['balance'], 2) ?></td>
                                    <td>
                                        <?php
                                        $statusColors = [
                                            'Draft' => 'secondary',
                                            'Open' => 'primary',
                                            'Paid' => 'success',
                                            'Partially Paid' => 'info',
                                            'Overdue' => 'danger',
                                            'Void' => 'dark'
                                        ];
                                        $color = $statusColors[$bill['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge text-bg-<?= $color ?>"><?= $bill['status'] ?></span>
                                    </td>
                                    <td>
                                        <?php if ($bill['zoho_sync_status'] == 'Synced'): ?>
                                            <span class="badge text-bg-success" title="Synced at <?= $bill['zoho_sync_at'] ?>">
                                                <i class="fas fa-check"></i> Synced
                                            </span>
                                        <?php elseif ($bill['zoho_sync_status'] == 'Failed'): ?>
                                            <span class="badge text-bg-danger"><i class="fas fa-times"></i> Failed</span>
                                        <?php else: ?>
                                            <span class="badge text-bg-warning"><i class="fas fa-clock"></i> Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= site_url('bills/view/' . $bill['id']) ?>" class="btn btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($bill['status'] != 'Void' && $bill['status'] != 'Paid'): ?>
                                                <a href="<?= site_url('bills/edit/' . $bill['id']) ?>" class="btn btn-outline-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php if ($bill['balance'] > 0): ?>
                                                    <a href="<?= site_url('bills/payment/' . $bill['id']) ?>" class="btn btn-outline-success" title="Record Payment">
                                                        <i class="fas fa-money-bill"></i>
                                                    </a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
