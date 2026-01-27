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
                <li class="breadcrumb-item active">Sales Orders</li>
            </ol>
        </div>
    </div>

    <div class="card card-outline card-primary mb-3">
        <div class="card-header">
            <h3 class="card-title">Filter Orders</h3>
            <div class="card-tools">
                <a href="<?= site_url('sales_orders/create') ?>" class="btn btn-sm btn-success">
                    <i class="fas fa-plus"></i> New Sales Order
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
                        <option value="Confirmed" <?= (isset($filters['status']) && $filters['status'] == 'Confirmed') ? 'selected' : '' ?>>Confirmed</option>
                        <option value="Closed" <?= (isset($filters['status']) && $filters['status'] == 'Closed') ? 'selected' : '' ?>>Closed</option>
                        <option value="Void" <?= (isset($filters['status']) && $filters['status'] == 'Void') ? 'selected' : '' ?>>Void</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                    <a href="<?= site_url('sales_orders') ?>" class="btn btn-outline-secondary">Reset</a>
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
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Shipment Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Zoho</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">No orders found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><strong><?= esc($order['sales_order_number']) ?></strong></td>
                                    <td><?= esc($order['customer_name']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($order['order_date'])) ?></td>
                                    <td><?= $order['shipment_date'] ? date('d/m/Y', strtotime($order['shipment_date'])) : '-' ?></td>
                                    <td>₹<?= number_format($order['total_amount'], 2) ?></td>
                                    <td>
                                        <?php
                                        $colors = ['Draft'=>'secondary','Confirmed'=>'primary','Closed'=>'success','Void'=>'dark'];
                                        $color = $colors[$order['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge text-bg-<?= $color ?>"><?= $order['status'] ?></span>
                                    </td>
                                    <td>
                                        <?php if ($order['zoho_sync_status'] == 'Synced'): ?>
                                            <span class="badge text-bg-success"><i class="fas fa-check"></i></span>
                                        <?php else: ?>
                                            <span class="badge text-bg-warning"><?= $order['zoho_sync_status'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= site_url('sales_orders/view/' . $order['id']) ?>" class="btn btn-outline-primary"><i class="fas fa-eye"></i></a>
                                            <a href="<?= site_url('sales_orders/edit/' . $order['id']) ?>" class="btn btn-outline-warning"><i class="fas fa-edit"></i></a>
                                            <a href="<?= site_url('sales_orders/delete/' . $order['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Delete this order?')"><i class="fas fa-trash"></i></a>
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
