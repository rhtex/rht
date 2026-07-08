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
                <li class="breadcrumb-item active">Invoices</li>
            </ol>
        </div>
    </div>

    <div class="card card-outline card-primary mb-3">
        <div class="card-header">
            <h3 class="card-title">Filter Invoices</h3>
            <div class="card-tools">
                <a href="<?= site_url('invoices/create') ?>" class="btn btn-sm btn-success">
                    <i class="fas fa-plus"></i> New Invoice
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Customer</label>
                    <select name="customer_id" class="form-select select2">
                        <option value="">All Customers</option>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?= $customer['id'] ?>" <?= (isset($filters['customer_id']) && $filters['customer_id'] == $customer['id']) ? 'selected' : '' ?>>
                                <?= esc($customer['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="Draft" <?= (isset($filters['status']) && $filters['status'] == 'Draft') ? 'selected' : '' ?>>Draft</option>
                        <option value="Open" <?= (isset($filters['status']) && $filters['status'] == 'Open') ? 'selected' : '' ?>>Open</option>
                        <option value="Paid" <?= (isset($filters['status']) && $filters['status'] == 'Paid') ? 'selected' : '' ?>>Paid</option>
                        <option value="Partially Paid" <?= (isset($filters['status']) && $filters['status'] == 'Partially Paid') ? 'selected' : '' ?>>Partially Paid</option>
                        <option value="Overdue" <?= (isset($filters['status']) && $filters['status'] == 'Overdue') ? 'selected' : '' ?>>Overdue</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" name="date_from" class="form-control" value="<?= esc($filters['date_from'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" name="date_to" class="form-control" value="<?= esc($filters['date_to'] ?? '') ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                    <a href="<?= site_url('invoices') ?>" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-outline card-secondary">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th>Commission</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($invoices)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">No invoices found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($invoices as $invoice): ?>
                                <tr>
                                    <td><strong><?= esc($invoice['invoice_number']) ?></strong></td>
                                    <td>
                                        <?= esc($invoice['customer_name']) ?>
                                        <br><small class="text-muted"><?= esc($invoice['customer_city'] ?? 'N/A') ?></small>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($invoice['invoice_date'])) ?></td>
                                    <td>₹<?= number_format($invoice['total_amount'], 2) ?></td>
                                    <td>₹<?= number_format($invoice['paid_amount'], 2) ?></td>
                                    <td>₹<?= number_format($invoice['balance'], 2) ?></td>
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
                                        $color = $statusColors[$invoice['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge text-bg-<?= $color ?>"><?= $invoice['status'] ?></span>
                                    </td>
                                    <td>
                                        <?php if ($invoice['agent_id']): ?>
                                            <?php if ($invoice['agent_commission_status'] == 'Paid'): ?>
                                                <span class="badge text-bg-success" title="Paid"><i class="fas fa-check-circle"></i> Paid</span>
                                            <?php else: ?>
                                                <span class="badge text-bg-warning" title="Pending"><i class="fas fa-clock"></i> Pending</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted small">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= site_url('invoices/view/' . $invoice['id']) ?>" class="btn btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($invoice['status'] != 'Void' && $invoice['status'] != 'Paid'): ?>
                                                <?php if ($invoice['status'] == 'Draft'): ?>
                                                    <a href="<?= site_url('invoices/mark-sent/' . $invoice['id']) ?>" class="btn btn-outline-success" title="Mark as Sent">
                                                        <i class="fas fa-paper-plane"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="<?= site_url('invoices/edit/' . $invoice['id']) ?>" class="btn btn-outline-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php if ($invoice['status'] != 'Draft' && $invoice['balance'] > 0): ?>
                                                    <a href="<?= site_url('invoices/payment/' . $invoice['id']) ?>" class="btn btn-outline-success" title="Record Payment">
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
