<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Load Warps<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1><i class="fas fa-circle text-success me-2"></i> Load Warps</h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('production/yarn-beams') ?>" class="btn btn-secondary">
            <i class="fas fa-ring me-1"></i> Beam Tracker
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Loaded Warps Inventory Card -->
<div class="card card-outline card-success shadow-sm">
    <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);">
        <h3 class="card-title text-success fw-bold"><i class="fas fa-list me-1"></i> Loaded Warp Beams</h3>
    </div>
    <div class="card-body">
        <!-- Beams Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle" id="loadedWarpsTable">
                <thead>
                    <tr class="bg-light">
                        <th>Beam Number</th>
                        <th>Current Location</th>
                        <th class="text-center">Ends</th>
                        <th>Color</th>
                        <th class="text-end">Meters</th>
                        <th class="text-end">Cost of Beam (₹)</th>
                        <th>Last Updated</th>
                        <th>Remarks</th>
                        <th width="15%" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($beams)): ?>
                        <?php foreach($beams as $beam): ?>
                            <tr>
                                <td>
                                    <strong style="font-size: 16px;"><?= esc($beam['beam_number']) ?></strong>
                                </td>
                                <td>
                                    <?php if($beam['location'] === 'In-House'): ?>
                                        <span class="badge bg-info"><i class="fas fa-warehouse me-1"></i> In-House</span>
                                    <?php elseif($beam['location'] === 'At Job Work'): ?>
                                        <span class="badge bg-warning text-dark"><i class="fas fa-industry me-1"></i> At Job Work (<?= esc($beam['current_holder'] ?: 'Job Worker') ?>)</span>
                                    <?php else: ?>
                                        <span class="badge bg-indigo"><i class="fas fa-cut me-1"></i> At Weaving (<?= esc($beam['current_holder']) ?>)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center fw-bold">
                                    <?= esc($beam['ends'] ?: '-') ?>
                                </td>
                                <td>
                                    <span class="fw-semibold text-primary"><?= esc($beam['color'] ?: '-') ?></span>
                                </td>
                                <td class="text-end fw-semibold">
                                    <?= $beam['meters'] ? number_format($beam['meters'], 2) . ' M' : '-' ?>
                                </td>
                                <td class="text-end fw-bold text-success">
                                    <?= !empty($beam['cost']) ? '₹' . number_format($beam['cost'], 2) : '-' ?>
                                </td>
                                <td>
                                    <?= date('d-M-Y h:i A', strtotime($beam['updated_at'])) ?>
                                </td>
                                <td>
                                    <span class="text-muted small"><?= esc($beam['txn_remarks'] ?: ($beam['remarks'] ?: '-')) ?></span>
                                </td>
                                <td class="text-center">
                                    <a href="<?= site_url('production/yarn-beams/ledger/'.$beam['id']) ?>" class="btn btn-sm btn-outline-primary" title="View Ledger History">
                                        <i class="fas fa-history me-1"></i> View Ledger
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted">No loaded beams present in the system.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof $ !== 'undefined' && $.fn.DataTable) {
            $('#loadedWarpsTable').DataTable({
                "order": [[ 0, "asc" ]],
                "pageLength": 25,
                "language": {
                    "search": "Quick Filter:",
                    "searchPlaceholder": "Search load warps..."
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>
