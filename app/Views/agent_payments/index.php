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
                <li class="breadcrumb-item active">Agent Payments</li>
            </ol>
        </div>
    </div>

    <!-- Filters -->
    <div class="card card-outline card-primary collapsed-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filters</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form method="get" action="<?= site_url('agent-payments') ?>">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Agent</label>
                            <select name="agent_id" class="form-select">
                                <option value="">-- All Agents --</option>
                                <?php foreach ($agents as $agent): ?>
                                <option value="<?= $agent['id'] ?>" <?= ($filters['agent_id'] == $agent['id']) ? 'selected' : '' ?>>
                                    <?= esc($agent['agent_name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label class="form-label">From Date</label>
                            <input type="date" name="date_from" class="form-control" value="<?= $filters['date_from'] ?? '' ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label class="form-label">To Date</label>
                            <input type="date" name="date_to" class="form-control" value="<?= $filters['date_to'] ?? '' ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Payment Mode</label>
                            <select name="payment_mode" class="form-select">
                                <option value="">-- All Modes --</option>
                                <option value="Cash" <?= ($filters['payment_mode'] == 'Cash') ? 'selected' : '' ?>>Cash</option>
                                <option value="Bank Transfer" <?= ($filters['payment_mode'] == 'Bank Transfer') ? 'selected' : '' ?>>Bank Transfer</option>
                                <option value="Cheque" <?= ($filters['payment_mode'] == 'Cheque') ? 'selected' : '' ?>>Cheque</option>
                                <option value="UPI" <?= ($filters['payment_mode'] == 'UPI') ? 'selected' : '' ?>>UPI</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Filter</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Payments List -->
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Agent Commission Payments</h3>
            <div class="card-tools">
                <a href="<?= site_url('agent-payments/create') ?>" class="btn btn-sm btn-success">
                    <i class="fas fa-plus"></i> Record Payment
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Payment #</th>
                            <th>Date</th>
                            <th>Agent</th>
                            <th>Phone</th>
                            <th>Payment Mode</th>
                            <th>Reference</th>
                            <th class="text-end">Amount</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($payments)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    No payments found
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($payments as $payment): ?>
                                <tr>
                                    <td><strong><?= esc($payment['payment_number']) ?></strong></td>
                                    <td><?= date('d/m/Y', strtotime($payment['payment_date'])) ?></td>
                                    <td><?= esc($payment['agent_name']) ?></td>
                                    <td><?= esc($payment['phone_number']) ?></td>
                                    <td><span class="badge text-bg-info"><?= $payment['payment_mode'] ?></span></td>
                                    <td><?= esc($payment['reference_number']) ?: '-' ?></td>
                                    <td class="text-end fw-bold">₹<?= number_format($payment['amount'], 2) ?></td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= site_url('agent-payments/view/' . $payment['id']) ?>" class="btn btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= site_url('agent-payments/delete/' . $payment['id']) ?>" 
                                               class="btn btn-danger" 
                                               onclick="return confirm('Are you sure you want to delete this payment? This will mark all invoices as unpaid again.')"
                                               title="Delete">
                                                <i class="fas fa-trash"></i>
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
</div>
<?= $this->endSection() ?>
