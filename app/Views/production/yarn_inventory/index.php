<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Yarn Inventory<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">Yarn Inventory</h1>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Filters -->
<?php
    $hasActiveFilters = !empty($filters['search']) || !empty($filters['yarn_type']) || !empty($filters['warp_weft']) || !empty($filters['yarn_count']) || !empty($filters['brand_mill']) || !empty($filters['color']) || !empty($filters['csp']) || !empty($filters['lot_number']) || ($filters['show_unavailable'] ?? 'no') === 'yes';
?>
<div class="card card-outline card-primary mb-3">
    <div class="card-header d-flex justify-content-between align-items-center" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="<?= $hasActiveFilters ? 'true' : 'false' ?>">
        <h3 class="card-title mb-0"><i class="fas fa-filter me-1 text-primary"></i> Advanced Filters</h3>
        <div class="card-tools ms-auto">
            <button type="button" class="btn btn-tool" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                <i class="fas <?= $hasActiveFilters ? 'fa-minus' : 'fa-plus' ?>" id="filterToggleIcon"></i>
            </button>
        </div>
    </div>
    <div id="filterCollapse" class="collapse <?= $hasActiveFilters ? 'show' : '' ?>">
        <div class="card-body">
            <form action="<?= site_url('production/yarn-inventory') ?>" method="get" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search name, mill, color..." value="<?= esc($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Yarn Type</label>
                <select name="yarn_type" class="form-select">
                    <option value="">All Types</option>
                    <option value="Raw" <?= ($filters['yarn_type'] ?? '') == 'Raw' ? 'selected' : '' ?>>Raw</option>
                    <option value="Dyed" <?= ($filters['yarn_type'] ?? '') == 'Dyed' ? 'selected' : '' ?>>Dyed</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Warp / Weft</label>
                <select name="warp_weft" class="form-select">
                    <option value="">All</option>
                    <option value="Warp" <?= ($filters['warp_weft'] ?? '') == 'Warp' ? 'selected' : '' ?>>Warp</option>
                    <option value="Weft" <?= ($filters['warp_weft'] ?? '') == 'Weft' ? 'selected' : '' ?>>Weft</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Yarn Count</label>
                <input type="text" name="yarn_count" class="form-control" placeholder="e.g. 60s" value="<?= esc($filters['yarn_count'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Mill Name</label>
                <input type="text" name="brand_mill" class="form-control" placeholder="e.g. Birla" value="<?= esc($filters['brand_mill'] ?? '') ?>">
            </div>
            <div class="col-md-3">
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
            <div class="col-md-3">
                <label class="form-label">CSP</label>
                <input type="text" name="csp" class="form-control" placeholder="e.g. 2400" value="<?= esc($filters['csp'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Lot Number</label>
                <input type="text" name="lot_number" class="form-control" placeholder="e.g. LOT-10" value="<?= esc($filters['lot_number'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Show Unavailable Stock?</label>
                <select name="show_unavailable" class="form-select">
                    <option value="no" <?= ($filters['show_unavailable'] ?? 'no') === 'no' ? 'selected' : '' ?>>No (Available Only)</option>
                    <option value="yes" <?= ($filters['show_unavailable'] ?? 'no') === 'yes' ? 'selected' : '' ?>>Yes (Show Zero & Out-of-Stock)</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <div class="btn-group w-100">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                    <a href="<?= site_url('production/yarn-inventory') ?>" class="btn btn-secondary"><i class="fas fa-undo"></i> Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>
</div>

<!-- Stock List -->
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Available Yarn Stock</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="inventoryTable">
                <thead>
                    <tr>
                        <th>Mill / Brand</th>
                        <th>Yarn Count</th>
                        <th>Warp / Weft</th>
                        <th>CSP</th>
                        <th>Lot Number</th>
                        <th>Color</th>
                        <th>Type</th>
                        <th>Warehouse</th>
                        <th>Stock Available</th>
                        <th>Avg Cost / Kg</th>
                        <th>Total Value</th>
                        <th>History</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($inventory)): ?>
                        <?php foreach($inventory as $item): ?>
                        <tr>
                            <td><strong><?= esc($item['brand_mill']) ?></strong></td>
                            <td><span class="badge bg-secondary"><?= esc($item['yarn_count']) ?></span></td>
                            <td><?= esc($item['warp_weft']) ?></td>
                            <td><?= esc($item['csp'] ?: '-') ?></td>
                            <td><?= esc($item['lot_number'] ?: '-') ?></td>
                            <td><?= esc($item['color'] ?: 'Raw') ?></td>
                            <td>
                                <span class="badge bg-<?= $item['yarn_type'] === 'Dyed' ? 'info' : 'light text-dark border' ?>">
                                    <?= esc($item['yarn_type']) ?>
                                </span>
                            </td>
                            <td><?= esc($item['warehouse'] ?: 'Main Warehouse') ?></td>
                            <td><strong class="text-primary"><?= number_format($item['quantity_available'], 2) ?> Kg</strong></td>
                            <td>₹<?= number_format($item['avg_cost_per_kg'], 2) ?></td>
                            <td><strong>₹<?= number_format($item['quantity_available'] * $item['avg_cost_per_kg'], 2) ?></strong></td>
                            <td class="text-center">
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
                                ])) ?>" class="btn btn-sm btn-info" title="Transaction Ledger">
                                    <i class="fas fa-history"></i> Ledger
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="text-center mt-3" id="loadMoreContainer" style="display: none;">
            <button type="button" class="btn btn-outline-primary px-4 rounded-pill fw-bold" id="btnLoadMore"><i class="fas fa-arrow-down me-1"></i> Load More Stock Items</button>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        var table = $('#inventoryTable').DataTable({
            "order": [[8, "desc"]],
            "pageLength": -1, // Retrieve all records
            "dom": 'lfrtip',
            "paging": false, // Disable default pagination to allow custom load more
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

        // Toggle filter icon
        $('#filterCollapse').on('shown.bs.collapse', function () {
            $('#filterToggleIcon').removeClass('fa-plus').addClass('fa-minus');
        });
        $('#filterCollapse').on('hidden.bs.collapse', function () {
            $('#filterToggleIcon').removeClass('fa-minus').addClass('fa-plus');
        });

        // Initialize Select2 for Color Search Dropdown
        function formatColorOption(state) {
            if (!state.id) {
                return state.text;
            }
            var palette = $(state.element).data('palette');
            if (palette) {
                var $state = $(
                    '<span><span class="d-inline-block rounded-circle me-2 border border-light-subtle shadow-sm" style="width: 18px; height: 18px; background-color: ' + palette + '; vertical-align: middle;"></span>' + state.text + '</span>'
                );
                return $state;
            }
            return state.text;
        }

        $('.select2-color').select2({
            theme: 'bootstrap-5',
            width: '100%',
            allowClear: true,
            placeholder: 'Choose color...',
            templateResult: formatColorOption,
            templateSelection: formatColorOption
        });
    });
</script>
<?= $this->endSection() ?>
