<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Sales Orders<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-shopping-cart text-primary me-2"></i>Sales Orders</h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex gap-2 align-items-center">
            <a href="<?= site_url('sales_orders') ?>" class="btn btn-sm btn-outline-secondary" title="Refresh List">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </a>
            <a href="<?= site_url('sales_orders/create') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> New Sales Order
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Summary Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('sales_orders') ?>" class="bt-stat bt-g-blue text-decoration-none <?= empty($filters['status']) ? 'active' : '' ?>">
            <i class="fas fa-shopping-cart bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['total'] ?? count($orders)) ?></div>
            <div class="bt-stat-label">Total Orders</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-purple" style="cursor:default;">
            <i class="fas fa-chart-line bt-stat-icon"></i>
            <div class="bt-stat-num">₹<?= number_format($stats['total_amount'] ?? 0, 2) ?></div>
            <div class="bt-stat-label">Total Value</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('sales_orders?status=Confirmed') ?>" class="bt-stat bt-g-green text-decoration-none <?= ($filters['status'] ?? '') === 'Confirmed' ? 'active' : '' ?>">
            <i class="fas fa-check-circle bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['confirmed_count'] ?? 0) ?></div>
            <div class="bt-stat-label">Confirmed Orders</div>
            <div class="progress"><div class="progress-bar" style="width:<?= (!empty($stats['total']) && $stats['total'] > 0) ? min(100, round(($stats['confirmed_count'] / $stats['total']) * 100)) : 0 ?>%"></div></div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-teal" style="cursor:default;">
            <i class="fas fa-file-invoice bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['converted_count'] ?? 0) ?></div>
            <div class="bt-stat-label">Converted to Invoice</div>
            <div class="progress"><div class="progress-bar" style="width:<?= (!empty($stats['total']) && $stats['total'] > 0) ? min(100, round(($stats['converted_count'] / $stats['total']) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
</div>

<!-- Filter Card -->
<div class="card card-outline card-primary mb-3 shadow-sm">
    <div class="card-header py-2">
        <h3 class="card-title fw-bold"><i class="fas fa-filter me-1"></i> Filter Orders</h3>
    </div>
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-2 col-sm-6">
                <label class="form-label">Order #</label>
                <input type="text" name="sales_order_number" class="form-control" placeholder="Search Order #" value="<?= esc($filters['sales_order_number'] ?? '') ?>">
            </div>
            <div class="col-md-3 col-sm-6">
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
            <div class="col-md-2 col-sm-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="Draft" <?= (isset($filters['status']) && $filters['status'] == 'Draft') ? 'selected' : '' ?>>Draft</option>
                    <option value="Confirmed" <?= (isset($filters['status']) && $filters['status'] == 'Confirmed') ? 'selected' : '' ?>>Confirmed</option>
                    <option value="Closed" <?= (isset($filters['status']) && $filters['status'] == 'Closed') ? 'selected' : '' ?>>Closed</option>
                    <option value="Void" <?= (isset($filters['status']) && $filters['status'] == 'Void') ? 'selected' : '' ?>>Void</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-4">
                <label class="form-label">From Date</label>
                <input type="date" name="date_from" class="form-control" value="<?= esc($filters['date_from'] ?? '') ?>">
            </div>
            <div class="col-md-2 col-sm-4">
                <label class="form-label">To Date</label>
                <input type="date" name="date_to" class="form-control" value="<?= esc($filters['date_to'] ?? '') ?>">
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100" title="Apply Filters"><i class="fas fa-search"></i></button>
                <a href="<?= site_url('sales_orders') ?>" class="btn btn-outline-secondary" title="Reset Filters"><i class="fas fa-undo"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Orders Table -->
<div class="card card-outline card-secondary shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="min-width: 130px;">Order #</th>
                        <th style="min-width: 200px;">Customer</th>
                        <th style="min-width: 100px;">Order Date</th>
                        <th style="min-width: 110px;">Shipment Date</th>
                        <th class="text-end" style="min-width: 110px;">Total</th>
                        <th class="text-center" style="min-width: 100px;">Status</th>
                        <th class="text-center" style="min-width: 150px;">Invoice Status</th>
                        <th class="text-end" style="min-width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fas fa-shopping-cart fa-2x text-muted mb-2 d-block"></i>
                                No sales orders found matching the criteria.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>
                                    <a href="<?= site_url('sales_orders/view/' . $order['id']) ?>" class="fw-bold text-primary text-decoration-none">
                                        <?= esc($order['sales_order_number']) ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary-subtle text-primary fw-bold me-2 d-flex align-items-center justify-content-center" style="width:34px;height:34px;min-width:34px;font-size:0.85rem;">
                                            <?= strtoupper(substr($order['customer_name'] ?? 'C', 0, 1)) ?>
                                        </div>
                                        <div>
                                            <span class="fw-semibold text-dark"><?= esc($order['customer_name']) ?></span>
                                            <?php if (!empty($order['customer_city'])): ?>
                                                <br><small class="text-muted"><i class="fas fa-map-marker-alt text-danger me-1"></i><?= esc($order['customer_city']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?= date('d/m/Y', strtotime($order['order_date'])) ?></td>
                                <td><?= $order['shipment_date'] ? date('d/m/Y', strtotime($order['shipment_date'])) : '<span class="text-muted">-</span>' ?></td>
                                <td class="text-end fw-bold">₹<?= number_format($order['total_amount'], 2) ?></td>
                                <td class="text-center">
                                    <?php
                                    $colors = ['Draft'=>'secondary','Confirmed'=>'primary','Closed'=>'success','Void'=>'dark'];
                                    $color = $colors[$order['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge text-bg-<?= $color ?> px-2 py-1"><?= $order['status'] ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($order['converted_to_invoice_id'])): ?>
                                        <a href="<?= site_url('invoices/view/' . $order['converted_to_invoice_id']) ?>" class="badge bg-success text-decoration-none" title="View Converted Invoice">
                                            <i class="fas fa-check-circle me-1"></i> <?= !empty($order['converted_invoice_number']) ? esc($order['converted_invoice_number']) : 'Invoice #' . $order['converted_to_invoice_id'] ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="badge bg-light text-danger border border-danger-subtle small fw-semibold">
                                            <i class="fas fa-hourglass-half me-1"></i> Not Converted
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= site_url('sales_orders/view/' . $order['id']) ?>" class="btn btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if (empty($order['converted_to_invoice_id']) && $order['status'] != 'Void' && $order['status'] != 'Closed'): ?>
                                            <a href="<?= site_url('sales_orders/convert/' . $order['id']) ?>" class="btn btn-outline-success" title="Convert to Invoice">
                                                <i class="fas fa-file-invoice"></i> Convert
                                            </a>
                                            <a href="<?= site_url('sales_orders/edit/' . $order['id']) ?>" class="btn btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= site_url('sales_orders/delete/' . $order['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Delete this order?')" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?= site_url('sales_orders/print/' . $order['id']) ?>" target="_blank" class="btn btn-outline-secondary" title="Print">
                                            <i class="fas fa-print"></i>
                                        </a>
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
<?= $this->endSection() ?>
