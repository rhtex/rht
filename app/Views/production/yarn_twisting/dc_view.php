<?= $this->extend('layouts/master') ?>
<?= $this->section('title') ?>Twisting DC Details<?= $this->endSection() ?>
<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="fw-bold text-dark"><i class="fas fa-file-invoice text-primary me-2"></i>Twisting DC Details</h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('production/yarn-twisting') ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-none"><i class="fas fa-list me-1"></i> List</a>
        <?php if ($dc['status'] !== 'Completed' && $dc['status'] !== 'Cancelled'): ?>
            <a href="<?= site_url('production/yarn-twisting/receipt-create/' . $dc['id']) ?>" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm"><i class="fas fa-download me-1"></i> Receive Yarn</a>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="border-radius: 12px;">
    <div class="card-header border-0 py-3" style="background: linear-gradient(135deg, #f39c12 0%, #d35400 100%);">
        <h5 class="card-title text-white mb-0 fw-bold"><i class="fas fa-receipt me-2"></i>DC: <?= esc($dc['dc_number']) ?></h5>
    </div>
    <div class="card-body p-4 bg-white">
        <!-- Info Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3 border border-light-subtle h-100">
                    <span class="text-uppercase text-xs text-muted fw-bold d-block mb-1"><i class="fas fa-user-tie me-1"></i> Vendor (Twister)</span>
                    <strong class="text-dark" style="font-size: 1rem;"><?= esc($dc['vendor_name']) ?></strong>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3 border border-light-subtle h-100">
                    <span class="text-uppercase text-xs text-muted fw-bold d-block mb-1"><i class="far fa-calendar-alt me-1"></i> DC Date</span>
                    <strong class="text-secondary" style="font-size: 0.95rem;"><?= date('d-M-Y', strtotime($dc['dc_date'])) ?></strong>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3 border border-light-subtle h-100">
                    <span class="text-uppercase text-xs text-muted fw-bold d-block mb-1"><i class="fas fa-info-circle me-1"></i> Current Status</span>
                    <?php 
                    $statusClass = 'secondary';
                    if ($dc['status'] === 'Open') $statusClass = 'info';
                    if ($dc['status'] === 'Partially Received') $statusClass = 'warning text-dark';
                    if ($dc['status'] === 'Completed') $statusClass = 'success';
                    if ($dc['status'] === 'Cancelled') $statusClass = 'danger';
                    ?>
                    <span class="badge rounded-pill bg-<?= $statusClass ?> text-uppercase mt-1" style="font-size: 0.75rem;"><?= esc($dc['status']) ?></span>
                </div>
            </div>
        </div>

        <h5 class="fw-bold text-dark mb-3"><i class="fas fa-dolly text-primary me-2"></i>Issued Yarns</h5>
        <div class="table-responsive mb-5">
            <table class="table align-middle table-hover mb-0">
                <thead>
                    <tr class="bg-light">
                        <th class="py-2 px-3 text-secondary fw-bold">Mill / Brand</th>
                        <th class="py-2 px-2 text-secondary fw-bold">Yarn Count</th>
                        <th class="py-2 px-2 text-secondary fw-bold">Warp/Weft</th>
                        <th class="py-2 px-2 text-secondary fw-bold">Issued Color</th>
                        <th class="py-2 px-2 text-secondary fw-bold text-end">Qty Issued</th>
                        <th class="py-2 px-2 text-secondary fw-bold text-end">Qty Received</th>
                        <th class="py-2 px-2 text-secondary fw-bold text-end">Qty Wastage</th>
                        <th class="py-2 px-3 text-secondary fw-bold text-end">Pending</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <?php
                        $pending = $item['quantity_issued_kg'] - ($item['quantity_received_kg'] + $item['quantity_wastage_kg']);
                        ?>
                        <tr>
                            <td class="py-2 px-3 fw-bold"><?= esc($item['mill_name']) ?></td>
                            <td class="py-2 px-2"><?= esc($item['yarn_count']) ?></td>
                            <td class="py-2 px-2"><span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1"><?= esc($item['warp_weft']) ?></span></td>
                            <td class="py-2 px-2"><?= esc($item['current_color']) ?></td>
                            <td class="py-2 px-2 text-end"><?= number_format($item['quantity_issued_kg'], 2) ?> Kg</td>
                            <td class="py-2 px-2 text-end text-success fw-semibold"><?= number_format($item['quantity_received_kg'], 2) ?> Kg</td>
                            <td class="py-2 px-2 text-end text-warning"><?= number_format($item['quantity_wastage_kg'], 2) ?> Kg</td>
                            <td class="py-2 px-3 text-end"><strong class="<?= $pending > 0 ? 'text-danger' : 'text-success' ?>"><?= number_format(max(0, $pending), 2) ?> Kg</strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Receipts List -->
        <h5 class="fw-bold text-dark mt-5 mb-3"><i class="fas fa-file-invoice text-success me-2"></i>Received Receipts</h5>
        <?php if (!empty($receipts)): ?>
            <?php foreach ($receipts as $r): ?>
                <div class="card border border-success-subtle mb-3 overflow-hidden shadow-sm">
                    <div class="card-header bg-success-subtle border-bottom border-success-subtle py-2">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <span class="fw-bold text-success"><i class="fas fa-check-double me-1"></i> Receipt No: <?= esc($r['receipt_number']) ?></span> 
                                <span class="text-muted ms-2">| Date: <?= date('d-M-Y', strtotime($r['receipt_date'])) ?></span>
                            </div>
                            <div class="col-md-6 text-end">
                                <a href="<?= site_url('production/yarn-twisting/receipt-delete/' . $r['id']) ?>" class="btn btn-xs btn-outline-danger btn-danger-bg rounded-pill px-3" onclick="return confirm('Revert and delete this receipt?')"><i class="fas fa-trash me-1"></i> Delete Receipt</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table align-middle table-sm mb-0">
                            <thead>
                                <tr class="bg-light" style="font-size: 0.72rem;">
                                    <th class="py-2 px-3 text-secondary fw-bold">Yarn Description</th>
                                    <th class="py-2 px-2 text-secondary fw-bold text-end">Recd Qty</th>
                                    <th class="py-2 px-2 text-secondary fw-bold text-end">Wastage</th>
                                    <th class="py-2 px-3 text-secondary fw-bold text-end">Job Charges</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($r['items'] as $ri): ?>
                                    <tr>
                                        <td class="py-2 px-3"><?= esc($ri['mill_name']) ?> - <?= esc($ri['yarn_count']) ?></td>
                                        <td class="py-2 px-2 text-end fw-semibold"><?= number_format($ri['quantity_received_kg'], 2) ?> Kg</td>
                                        <td class="py-2 px-2 text-end text-muted"><?= number_format($ri['quantity_wastage_kg'], 2) ?> Kg</td>
                                        <td class="py-2 px-3 text-end fw-bold">₹<?= number_format($ri['job_work_charges'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-light text-center py-4 border border-dashed rounded-3">
                <i class="fas fa-receipt fa-2x text-muted mb-2"></i>
                <p class="mb-0 text-muted">No receipts saved yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>