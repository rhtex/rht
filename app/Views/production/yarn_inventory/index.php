<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Yarn Inventory<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-warehouse text-primary me-2"></i>Yarn Stock & Inventory</h1>
    </div>
    <div class="col-sm-6 text-end">
        <div class="d-inline-flex gap-2 align-items-center">
            <a href="<?= site_url('production/yarn-inventory') ?>" class="btn btn-sm btn-outline-secondary" title="Refresh List">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </a>
            <a href="<?= site_url('production/yarn-purchases/create') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Inward New Yarn
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$totalLines = count($inventory ?? []);
$totalWeight = 0;
$totalValuation = 0;
$rawCount = 0;
$dyedCount = 0;

if (!empty($inventory)) {
    foreach ($inventory as $item) {
        $qty = (float)($item['quantity_available'] ?? 0);
        $cost = (float)($item['avg_cost_per_kg'] ?? 0);
        $totalWeight += $qty;
        $totalValuation += ($qty * $cost);
        if (($item['yarn_type'] ?? '') === 'Dyed') {
            $dyedCount++;
        } else {
            $rawCount++;
        }
    }
}

$hasActiveFilters = !empty($filters['search']) || !empty($filters['yarn_type']) || !empty($filters['warp_weft']) || !empty($filters['yarn_count']) || !empty($filters['brand_mill']) || !empty($filters['color']) || !empty($filters['csp']) || !empty($filters['lot_number']) || ($filters['show_unavailable'] ?? 'no') === 'yes';
?>

<!-- Summary Stat Cards -->
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-sm-6">
        <a href="<?= site_url('production/yarn-inventory') ?>" class="bt-stat bt-g-blue text-decoration-none">
            <i class="fas fa-boxes bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($totalLines) ?></div>
            <div class="bt-stat-label">Yarn Stock Lines</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-purple" style="cursor:default;">
            <i class="fas fa-weight-hanging bt-stat-icon"></i>
            <div class="bt-stat-num"><?= number_format($totalWeight, 2) ?> <small style="font-size:1rem;">Kg</small></div>
            <div class="bt-stat-label">Total Stock in Warehouse</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-emerald" style="cursor:default;">
            <i class="fas fa-coins bt-stat-icon"></i>
            <div class="bt-stat-num">₹<?= number_format($totalValuation, 2) ?></div>
            <div class="bt-stat-label">Total Inventory Valuation</div>
            <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="bt-stat bt-g-teal" style="cursor:default;">
            <i class="fas fa-palette bt-stat-icon"></i>
            <div class="bt-stat-num"><?= $dyedCount ?> <small style="font-size:0.9rem;opacity:0.8;">Dyed</small> / <?= $rawCount ?> <small style="font-size:0.9rem;opacity:0.8;">Raw</small></div>
            <div class="bt-stat-label">Yarn Types</div>
            <div class="progress"><div class="progress-bar" style="width:<?= ($totalLines > 0) ? min(100, round(($dyedCount / $totalLines) * 100)) : 0 ?>%"></div></div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card card-outline card-primary mb-3 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center py-2" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="<?= $hasActiveFilters ? 'true' : 'false' ?>">
        <h3 class="card-title fw-bold mb-0"><i class="fas fa-filter me-1 text-primary"></i> Advanced Filters <?= $hasActiveFilters ? '<span class="badge bg-primary ms-2">Active</span>' : '' ?></h3>
        <div class="card-tools ms-auto">
            <button type="button" class="btn btn-tool" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                <i class="fas <?= $hasActiveFilters ? 'fa-minus' : 'fa-plus' ?>" id="filterToggleIcon"></i>
            </button>
        </div>
    </div>
    <div id="filterCollapse" class="collapse <?= $hasActiveFilters ? 'show' : '' ?>">
        <div class="card-body">
            <form action="<?= site_url('production/yarn-inventory') ?>" method="get" class="row g-2 align-items-end">
                <div class="col-md-3 col-sm-6">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Search name, mill, color..." value="<?= esc($filters['search'] ?? '') ?>">
                </div>
                <div class="col-md-2 col-sm-6">
                    <label class="form-label">Yarn Type</label>
                    <select name="yarn_type" class="form-select">
                        <option value="">All Types</option>
                        <option value="Raw" <?= ($filters['yarn_type'] ?? '') == 'Raw' ? 'selected' : '' ?>>Raw</option>
                        <option value="Dyed" <?= ($filters['yarn_type'] ?? '') == 'Dyed' ? 'selected' : '' ?>>Dyed</option>
                    </select>
                </div>
                <div class="col-md-2 col-sm-4">
                    <label class="form-label">Warp / Weft</label>
                    <select name="warp_weft" class="form-select">
                        <option value="">All</option>
                        <option value="Warp" <?= ($filters['warp_weft'] ?? '') == 'Warp' ? 'selected' : '' ?>>Warp</option>
                        <option value="Weft" <?= ($filters['warp_weft'] ?? '') == 'Weft' ? 'selected' : '' ?>>Weft</option>
                    </select>
                </div>
                <div class="col-md-2 col-sm-4">
                    <label class="form-label">Yarn Count</label>
                    <input type="text" name="yarn_count" class="form-control" placeholder="e.g. 60s" value="<?= esc($filters['yarn_count'] ?? '') ?>">
                </div>
                <div class="col-md-3 col-sm-4">
                    <label class="form-label">Mill Name</label>
                    <input type="text" name="brand_mill" class="form-control" placeholder="e.g. Birla" value="<?= esc($filters['brand_mill'] ?? '') ?>">
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label">Color</label>
                    <select name="color" class="form-select select2-color" data-placeholder="Choose color...">
                        <option value="">All Colors</option>
                        <option value="Raw" <?= ($filters['color'] ?? '') === 'Raw' ? 'selected' : '' ?> data-palette="">Raw (Uncolored)</option>
                        <?php if(!empty($colorsList)): ?>
                            <?php foreach($colorsList as $c): ?>
                                <option value="<?= esc($c['name']) ?>" <?= ($filters['color'] ?? '') === $c['name'] ? 'selected' : '' ?> data-palette="<?= esc($c['color_palette'] ?: '') ?>"><?= esc($c['name']) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-2 col-sm-6">
                    <label class="form-label">CSP</label>
                    <input type="text" name="csp" class="form-control" placeholder="e.g. 2400" value="<?= esc($filters['csp'] ?? '') ?>">
                </div>
                <div class="col-md-2 col-sm-6">
                    <label class="form-label">Lot Number</label>
                    <input type="text" name="lot_number" class="form-control" placeholder="e.g. LOT-10" value="<?= esc($filters['lot_number'] ?? '') ?>">
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label">Stock Status</label>
                    <select name="show_unavailable" class="form-select">
                        <option value="no" <?= ($filters['show_unavailable'] ?? 'no') === 'no' ? 'selected' : '' ?>>Available Stock Only</option>
                        <option value="yes" <?= ($filters['show_unavailable'] ?? 'no') === 'yes' ? 'selected' : '' ?>>Show Zero & Out-of-Stock</option>
                    </select>
                </div>
                <div class="col-md-2 col-sm-6 d-flex gap-1">
                    <button type="submit" class="btn btn-primary w-100" title="Apply Filter"><i class="fas fa-search"></i> Filter</button>
                    <a href="<?= site_url('production/yarn-inventory') ?>" class="btn btn-outline-secondary" title="Reset Filters"><i class="fas fa-undo"></i></a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Stock List -->
<div class="card card-outline card-secondary shadow-sm">
    <div class="card-header py-2 bg-white">
        <h3 class="card-title fw-bold">Available Yarn Stock Inventory</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0" id="inventoryTable">
                <thead class="table-light">
                    <tr>
                        <th style="min-width: 140px;">Mill / Brand</th>
                        <th class="text-center" style="min-width: 80px;">Count</th>
                        <th style="min-width: 90px;">Warp / Weft</th>
                        <th style="min-width: 80px;">CSP</th>
                        <th style="min-width: 90px;">Lot #</th>
                        <th style="min-width: 120px;">Color</th>
                        <th class="text-center" style="min-width: 80px;">Type</th>
                        <th style="min-width: 130px;">Warehouse</th>
                        <th class="text-end" style="min-width: 120px;">Stock Available</th>
                        <th class="text-end" style="min-width: 110px;">Avg Cost / Kg</th>
                        <th class="text-end" style="min-width: 120px;">Total Value</th>
                        <th class="text-end" style="min-width: 100px;">History</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($inventory)): ?>
                        <?php foreach($inventory as $item): ?>
                        <tr>
                            <td><strong><?= esc($item['brand_mill'] ?: 'Unknown Mill') ?></strong></td>
                            <td class="text-center"><span class="badge text-bg-secondary"><?= esc($item['yarn_count']) ?></span></td>
                            <td><?= esc($item['warp_weft']) ?></td>
                            <td><?= esc($item['csp'] ?: '-') ?></td>
                            <td><code class="text-dark"><?= esc($item['lot_number'] ?: '-') ?></code></td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?= esc($item['color'] ?: 'Raw') ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge text-bg-<?= $item['yarn_type'] === 'Dyed' ? 'info' : 'secondary' ?> px-2 py-1">
                                    <?= esc($item['yarn_type']) ?>
                                </span>
                            </td>
                            <td><?= esc($item['warehouse'] ?: 'Main Warehouse') ?></td>
                            <td class="text-end fw-bold text-primary"><?= number_format($item['quantity_available'], 2) ?> Kg</td>
                            <td class="text-end">₹<?= number_format($item['avg_cost_per_kg'], 2) ?></td>
                            <td class="text-end fw-bold text-dark">₹<?= number_format($item['quantity_available'] * $item['avg_cost_per_kg'], 2) ?></td>
                            <td class="text-end">
                                <a href="<?= site_url('production/yarn-inventory/history?' . http_build_query([
                                    'name'      => $item['yarn_name'],
                                    'count'     => $item['yarn_count'],
                                    'type'      => $item['yarn_type'],
                                    'color'     => $item['color'],
                                    'mill'      => $item['brand_mill'],
                                    'lot'       => $item['lot_number'],
                                    'csp'       => $item['csp'],
                                    'warp_weft' => $item['warp_weft'],
                                    'warehouse' => $item['warehouse']
                                ])) ?>" class="btn btn-sm btn-outline-primary" title="View Transaction Ledger">
                                    <i class="fas fa-history me-1"></i> Ledger
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="12" class="text-center py-4 text-muted">
                                <i class="fas fa-warehouse fa-2x text-muted mb-2 d-block"></i>
                                No yarn inventory records found matching your filters.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="text-center py-3 bg-light border-top" id="loadMoreContainer" style="display: none;">
            <button type="button" class="btn btn-outline-primary px-4 rounded-pill fw-bold" id="btnLoadMore"><i class="fas fa-arrow-down me-1"></i> Load More Stock Items</button>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        if ($('#inventoryTable').length && $.fn.DataTable) {
            var table = $('#inventoryTable').DataTable({
                "order": [[8, "desc"]],
                "pageLength": -1,
                "dom": 'lfrtip',
                "paging": false,
                "info": false
            });

            var showCount = 25;
            var totalRows = $('#inventoryTable tbody tr').length;

            function updateRowsVisibility() {
                var visibleCount = 0;
                $('#inventoryTable tbody tr').each(function(index) {
                    if (index < showCount) {
                        $(this).show();
                        visibleCount++;
                    } else {
                        $(this).hide();
                    }
                });

                if (showCount >= totalRows) {
                    $('#loadMoreContainer').hide();
                } else {
                    $('#loadMoreContainer').show();
                }
            }

            updateRowsVisibility();

            $('#btnLoadMore').on('click', function() {
                showCount += 25;
                updateRowsVisibility();
            });
        }

        $('#filterCollapse').on('shown.bs.collapse', function () {
            $('#filterToggleIcon').removeClass('fa-plus').addClass('fa-minus');
        });
        $('#filterCollapse').on('hidden.bs.collapse', function () {
            $('#filterToggleIcon').removeClass('fa-minus').addClass('fa-plus');
        });

        function formatColorOption(state) {
            if (!state.id) return state.text;
            var palette = $(state.element).data('palette');
            if (palette) {
                return $('<span><span class="d-inline-block rounded-circle me-2 border border-light-subtle shadow-sm" style="width: 16px; height: 16px; background-color: ' + palette + '; vertical-align: middle;"></span>' + state.text + '</span>');
            }
            return state.text;
        }

        if ($('.select2-color').length && $.fn.select2) {
            $('.select2-color').select2({
                theme: 'bootstrap-5',
                width: '100%',
                allowClear: true,
                placeholder: 'Choose color...',
                templateResult: formatColorOption,
                templateSelection: formatColorOption
            });
        }
    });
</script>
<?= $this->endSection() ?>
