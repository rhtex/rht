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
                <li class="breadcrumb-item active">Quotations</li>
            </ol>
        </div>
    </div>

    <div class="card card-outline card-primary mb-3">
        <div class="card-header">
            <h3 class="card-title">Filter Quotations</h3>
            <div class="card-tools">
                <a href="<?= site_url('quotations/create') ?>" class="btn btn-sm btn-success">
                    <i class="fas fa-plus"></i> New Quotation
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
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="Draft" <?= (isset($filters['status']) && $filters['status'] == 'Draft') ? 'selected' : '' ?>>Draft</option>
                        <option value="Sent" <?= (isset($filters['status']) && $filters['status'] == 'Sent') ? 'selected' : '' ?>>Sent</option>
                        <option value="Accepted" <?= (isset($filters['status']) && $filters['status'] == 'Accepted') ? 'selected' : '' ?>>Accepted</option>
                        <option value="Declined" <?= (isset($filters['status']) && $filters['status'] == 'Declined') ? 'selected' : '' ?>>Declined</option>
                        <option value="Invoiced" <?= (isset($filters['status']) && $filters['status'] == 'Invoiced') ? 'selected' : '' ?>>Invoiced</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                    <a href="<?= site_url('quotations') ?>" class="btn btn-outline-secondary">Reset</a>
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
                            <th>Quotation #</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Expiry</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($quotations)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">No quotations found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($quotations as $qnt): ?>
                                <tr>
                                    <td><strong><?= esc($qnt['quotation_number']) ?></strong></td>
                                    <td><?= esc($qnt['customer_name']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($qnt['quotation_date'])) ?></td>
                                    <td><?= $qnt['expiry_date'] ? date('d/m/Y', strtotime($qnt['expiry_date'])) : '-' ?></td>
                                    <td>₹<?= number_format($qnt['total_amount'], 2) ?></td>
                                    <td>
                                        <?php
                                        $colors = ['Draft'=>'secondary','Sent'=>'info','Accepted'=>'success','Declined'=>'danger','Invoiced'=>'primary','Expired'=>'warning'];
                                        $color = $colors[$qnt['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge text-bg-<?= $color ?>"><?= $qnt['status'] ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= site_url('quotations/view/' . $qnt['id']) ?>" class="btn btn-outline-primary"><i class="fas fa-eye"></i></a>
                                            <a href="<?= site_url('quotations/edit/' . $qnt['id']) ?>" class="btn btn-outline-warning"><i class="fas fa-edit"></i></a>
                                            <a href="<?= site_url('quotations/delete/' . $qnt['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Delete this quotation?')"><i class="fas fa-trash"></i></a>
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
