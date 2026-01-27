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
                <li class="breadcrumb-item"><a href="<?= base_url('agent-payments') ?>">Agent Payments</a></li>
                <li class="breadcrumb-item active">Waiting Reports</li>
            </ol>
        </div>
    </div>

    <!-- Filters -->
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filters</h3>
        </div>
        <div class="card-body">
            <form method="get" action="<?= site_url('agent-payments/reports') ?>">
                <div class="row">
                    <div class="col-md-4">
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
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">From Date</label>
                            <input type="date" name="date_from" class="form-control" value="<?= $filters['date_from'] ?? '' ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">To Date</label>
                            <input type="date" name="date_to" class="form-control" value="<?= $filters['date_to'] ?? '' ?>">
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

    <!-- Commission Summary -->
    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-chart-bar"></i> Waiting Payment / Commission Summary</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Agent Name</th>
                            <th class="text-center">Commission %</th>
                            <th class="text-center">Total Invoices</th>
                            <th class="text-end">Total Commission</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Pending</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($summary)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    No commission data found
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $grandTotalCommission = 0;
                            $grandTotalPaid = 0;
                            $grandTotalPending = 0;
                            foreach ($summary as $row): 
                                $grandTotalCommission += $row['total_commission'];
                                $grandTotalPaid += $row['paid_commission'];
                                $grandTotalPending += $row['pending_commission'];
                            ?>
                                <tr>
                                    <td><strong><?= esc($row['agent_name']) ?></strong></td>
                                    <td class="text-center"><?= number_format($row['commission_percentage'], 2) ?>%</td>
                                    <td class="text-center"><?= $row['total_invoices'] ?></td>
                                    <td class="text-end fw-bold">₹<?= number_format($row['total_commission'], 2) ?></td>
                                    <td class="text-end text-success">₹<?= number_format($row['paid_commission'], 2) ?></td>
                                    <td class="text-end text-warning">₹<?= number_format($row['pending_commission'], 2) ?></td>
                                    <td class="text-center">
                                        <?php if ($row['pending_commission'] > 0): ?>
                                             <div class="btn-group">
                                                 <a href="<?= site_url('agent-payments/create/' . $row['id']) ?>" class="btn btn-sm btn-success" title="Record Payment">
                                                     <i class="fas fa-hand-holding-usd"></i> Pay
                                                 </a>
                                                 <a href="<?= site_url('agent-payments/preview-report?agent_id=' . $row['id'] . '&date_from=' . ($filters['date_from'] ?? '') . '&date_to=' . ($filters['date_to'] ?? '')) ?>" 
                                                    target="_blank" class="btn btn-sm btn-warning" title="Waiting Payment Report">
                                                     <i class="fas fa-file-invoice"></i> Report
                                                 </a>
                                             </div>
                                         <?php else: ?>
                                             <span class="badge text-bg-success"><i class="fas fa-check"></i> All Paid</span>
                                         <?php endif; ?>
                                     </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="table-primary fw-bold">
                                <td colspan="3" class="text-end">Grand Total:</td>
                                <td class="text-end">₹<?= number_format($grandTotalCommission, 2) ?></td>
                                <td class="text-end text-success">₹<?= number_format($grandTotalPaid, 2) ?></td>
                                <td class="text-end text-warning">₹<?= number_format($grandTotalPending, 2) ?></td>
                                <td></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
