<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-file-contract text-primary me-2"></i><?= esc($title) ?></h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex gap-2 align-items-center">
            <a href="<?= site_url('quotations') ?>" class="btn btn-sm btn-outline-secondary" title="Refresh List">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </a>
            <a href="<?= site_url('quotations/create') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> New Quotation / Estimate
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Summary Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('quotations') ?>" class="bt-stat bt-g-blue text-decoration-none <?= empty($filters['status']) ? 'active' : '' ?>">
            <i class="fas fa-file-contract bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['total'] ?? count($quotations)) ?></div>
            <div class="bt-stat-label">Total Quotations</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-purple" style="cursor:default;">
            <i class="fas fa-coins bt-stat-icon"></i>
            <div class="bt-stat-num">₹<?= number_format($stats['total_amount'] ?? 0, 2) ?></div>
            <div class="bt-stat-label">Total Quoted Value</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('quotations?status=Accepted') ?>" class="bt-stat bt-g-green text-decoration-none <?= ($filters['status'] ?? '') === 'Accepted' ? 'active' : '' ?>">
            <i class="fas fa-check-circle bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['accepted_count'] ?? 0) ?></div>
            <div class="bt-stat-label">Accepted Quotes</div>
            <div class="progress"><div class="progress-bar" style="width:<?= (!empty($stats['total']) && $stats['total'] > 0) ? min(100, round(($stats['accepted_count'] / $stats['total']) * 100)) : 0 ?>%"></div></div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('quotations?status=Invoiced') ?>" class="bt-stat bt-g-teal text-decoration-none <?= ($filters['status'] ?? '') === 'Invoiced' ? 'active' : '' ?>">
            <i class="fas fa-file-invoice-dollar bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($stats['invoiced_count'] ?? 0) ?></div>
            <div class="bt-stat-label">Converted to Invoices</div>
            <div class="progress"><div class="progress-bar" style="width:<?= (!empty($stats['total']) && $stats['total'] > 0) ? min(100, round(($stats['invoiced_count'] / $stats['total']) * 100)) : 0 ?>%"></div></div>
        </a>
    </div>
</div>

<!-- Filter Card -->
<div class="card card-outline card-primary mb-3 shadow-sm">
    <div class="card-header py-2">
        <h3 class="card-title fw-bold"><i class="fas fa-filter me-1"></i> Filter Quotations & Estimates</h3>
    </div>
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-2 col-sm-6">
                <label class="form-label">Quotation #</label>
                <input type="text" name="quotation_number" class="form-control" placeholder="Search Quotation #" value="<?= esc($filters['quotation_number'] ?? '') ?>">
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
                    <option value="Sent" <?= (isset($filters['status']) && $filters['status'] == 'Sent') ? 'selected' : '' ?>>Sent</option>
                    <option value="Accepted" <?= (isset($filters['status']) && $filters['status'] == 'Accepted') ? 'selected' : '' ?>>Accepted</option>
                    <option value="Declined" <?= (isset($filters['status']) && $filters['status'] == 'Declined') ? 'selected' : '' ?>>Declined</option>
                    <option value="Invoiced" <?= (isset($filters['status']) && $filters['status'] == 'Invoiced') ? 'selected' : '' ?>>Invoiced</option>
                    <option value="Expired" <?= (isset($filters['status']) && $filters['status'] == 'Expired') ? 'selected' : '' ?>>Expired</option>
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
                <a href="<?= site_url('quotations') ?>" class="btn btn-outline-secondary" title="Reset Filters"><i class="fas fa-undo"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Quotations Table -->
<div class="card card-outline card-secondary shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="min-width: 140px;">Quotation #</th>
                        <th style="min-width: 200px;">Customer</th>
                        <th style="min-width: 110px;">Date</th>
                        <th style="min-width: 110px;">Valid Until</th>
                        <th class="text-end" style="min-width: 120px;">Total Amount</th>
                        <th class="text-center" style="min-width: 120px;">Status</th>
                        <th class="text-end" style="min-width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($quotations)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fas fa-file-contract fa-2x text-muted mb-2 d-block"></i>
                                No quotations or estimates found matching the criteria.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($quotations as $qnt): ?>
                            <tr>
                                <td>
                                    <a href="<?= site_url('quotations/view/' . $qnt['id']) ?>" class="fw-bold text-primary text-decoration-none">
                                        <?= esc($qnt['quotation_number']) ?>
                                    </a>
                                    <?php if (!empty($qnt['reference_number'])): ?>
                                        <br><small class="text-muted">Ref: <?= esc($qnt['reference_number']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary-subtle text-primary fw-bold me-2 d-flex align-items-center justify-content-center" style="width:34px;height:34px;min-width:34px;font-size:0.85rem;">
                                            <?= strtoupper(substr($qnt['customer_name'] ?? 'C', 0, 1)) ?>
                                        </div>
                                        <div>
                                            <span class="fw-semibold text-dark"><?= esc($qnt['customer_name']) ?></span>
                                            <?php if (!empty($qnt['city'])): ?>
                                                <br><small class="text-muted"><i class="fas fa-map-marker-alt text-danger me-1"></i><?= esc($qnt['city']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?= date('d/m/Y', strtotime($qnt['quotation_date'])) ?></td>
                                <td><?= $qnt['expiry_date'] ? date('d/m/Y', strtotime($qnt['expiry_date'])) : '<span class="text-muted">-</span>' ?></td>
                                <td class="text-end fw-bold">₹<?= number_format($qnt['total_amount'], 2) ?></td>
                                <?php
                                $soTargetId = !empty($qnt['converted_to_so_id']) ? $qnt['converted_to_so_id'] : (!empty($qnt['linked_so_id']) ? $qnt['linked_so_id'] : null);
                                $invTargetId = !empty($qnt['converted_to_invoice_id']) ? $qnt['converted_to_invoice_id'] : (!empty($qnt['linked_invoice_id']) ? $qnt['linked_invoice_id'] : null);
                                $isConverted = !empty($soTargetId) || !empty($invTargetId) || in_array($qnt['status'], ['Accepted', 'Invoiced']);
                                ?>
                                <td class="text-center">
                                    <?php
                                    $colors = ['Draft'=>'secondary','Sent'=>'info','Accepted'=>'success','Declined'=>'danger','Invoiced'=>'primary','Expired'=>'warning'];
                                    $color = $colors[$qnt['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge text-bg-<?= $color ?> px-2 py-1"><?= $qnt['status'] ?></span>
                                    <?php if (!empty($soTargetId)): ?>
                                        <br><a href="<?= site_url('sales_orders/view/' . $soTargetId) ?>" class="badge bg-primary text-decoration-none mt-1" title="Converted to Sales Order">
                                            <i class="fas fa-shopping-cart me-1"></i> <?= esc($qnt['converted_so_number'] ?? 'SO #' . $soTargetId) ?>
                                        </a>
                                    <?php elseif (!empty($invTargetId)): ?>
                                        <br><a href="<?= site_url('invoices/view/' . $invTargetId) ?>" class="badge bg-success text-decoration-none mt-1" title="Converted to Invoice">
                                            <i class="fas fa-file-invoice-dollar me-1"></i> <?= esc($qnt['converted_invoice_number'] ?? 'INV #' . $invTargetId) ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= site_url('quotations/view/' . $qnt['id']) ?>" class="btn btn-outline-primary" title="View Quotation">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if (!$isConverted && $qnt['status'] != 'Declined' && $qnt['status'] != 'Expired'): ?>
                                            <a href="<?= site_url('quotations/convert-so/' . $qnt['id']) ?>" class="btn btn-outline-info" title="Convert to Sales Order">
                                                <i class="fas fa-shopping-cart"></i>
                                            </a>
                                            <a href="<?= site_url('quotations/convert-invoice/' . $qnt['id']) ?>" class="btn btn-outline-success" title="Convert to Invoice">
                                                <i class="fas fa-file-invoice-dollar"></i>
                                            </a>
                                            <a href="<?= site_url('quotations/edit/' . $qnt['id']) ?>" class="btn btn-outline-warning" title="Edit Quotation">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?= site_url('quotations/print/' . $qnt['id']) ?>" target="_blank" class="btn btn-outline-secondary" title="Print Quotation">
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
