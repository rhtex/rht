<?= $this->extend('layouts/master') ?>
<?= $this->section('title') ?><?= isset($dc) ? 'Edit Warping & Sizing DC' : 'Create Warping & Sizing DC' ?><?= $this->endSection() ?>
<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1><?= isset($dc) ? 'Edit Warping & Sizing DC' : 'New Warping & Sizing DC' ?></h1>
    </div>
    <div class="col-sm-6 text-end"><a href="<?= site_url('production/yarn-warping-sizing') ?>"
            class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a></div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <form
        action="<?= isset($dc) ? site_url('production/yarn-warping-sizing/update/' . $dc['id']) : site_url('production/yarn-warping-sizing/store') ?>"
        method="post" id="dcForm">
        <?= csrf_field() ?>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">DC Number <span class="text-danger">*</span></label>
                    <input type="text" name="dc_number" class="form-control"
                        value="<?= old('dc_number', isset($dc) ? $dc['dc_number'] : $nextDcNumber) ?>" required
                        readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">DC Date <span class="text-danger">*</span></label>
                    <input type="date" name="dc_date" class="form-control"
                        value="<?= old('dc_date', isset($dc) ? $dc['dc_date'] : date('Y-m-d')) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Vendor (Sizer) <span class="text-danger">*</span></label>
                    <input type="text" name="vendor_name" class="form-control"
                        value="<?= old('vendor_name', isset($dc) ? $dc['vendor_name'] : '') ?>" placeholder="Sizer name"
                        required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Design / Pattern Name <span class="text-danger">*</span></label>
                    <input type="text" name="design_pattern" class="form-control"
                        value="<?= old('design_pattern', isset($dc) ? $dc['design_pattern'] : '') ?>"
                        placeholder="e.g. Stripe Blue" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Total Ends <span class="text-danger">*</span></label>
                    <input type="number" name="total_ends" id="total_ends" class="form-control"
                        value="<?= old('total_ends', isset($dc) ? $dc['total_ends'] : '') ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Expected Return Date</label>
                    <input type="date" name="expected_return_date" class="form-control"
                        value="<?= old('expected_return_date', isset($dc) ? $dc['expected_return_date'] : '') ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Vehicle Details</label>
                    <input type="text" name="vehicle_details" class="form-control"
                        value="<?= old('vehicle_details', isset($dc) ? $dc['vehicle_details'] : '') ?>"
                        placeholder="Vehicle No.">
                </div>
            </div>

            <div class="card card-outline card-secondary mt-3">
                <div class="card-header">
                    <h5 class="card-title">Select Yarn & Issue Qty</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-4 mb-2">
                            <label class="form-label d-flex justify-content-between align-items-center mb-2">
                                <span>Select Yarn from Warehouse</span>
                                <small class="text-success fw-bold" id="available-stock-lbl"
                                    style="display: none;"></small>
                            </label>
                            <select id="select-yarn-inventory" class="form-select">
                                <option value="">-- Choose Yarn --</option>
                                <?php foreach ($availableYarns as $y): ?>
                                    <option value="<?= htmlspecialchars(json_encode($y)) ?>">
                                        <?= esc($y['brand_mill']) ?> - <?= esc($y['yarn_count']) ?> (Lot:
                                        <?= esc($y['lot_number'] ?: '-') ?>, Color: <?= esc($y['color']) ?>) [Stock:
                                        <?= number_format($y['quantity_available'], 2) ?> Kg | Cones: <?= (int)($y['cones_available'] ?? 0) ?>]
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2" id="warp-type-container" style="display: none;">
                            <label class="form-label">Warp Type <span class="text-danger">*</span></label>
                            <select id="select-warp-type" class="form-select">
                                <option value="body">body</option>
                                <option value="border">border</option>
                                <option value="kattam">kattam</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Quantity to Issue (Kg)</label>
                            <input type="number" step="0.01" id="input-issue-qty" class="form-control" min="0.01">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="form-label">Cones to Issue</label>
                            <input type="number" id="input-issue-cones" class="form-control" min="0">
                        </div>
                        <div class="col-md-2 mb-2">
                            <button type="button" id="btn-add-item" class="btn btn-dark w-100"><i
                                    class="fas fa-plus"></i> Add Item</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="table-responsive mt-3">
                <table class="table table-bordered align-middle" id="itemsTable">
                    <thead>
                        <tr class="bg-light">
                            <th>Mill / Brand</th>
                            <th>Yarn Count</th>
                            <th>Warp/Weft</th>
                            <th>Lot No</th>
                            <th>CSP</th>
                            <th>Color</th>
                            <th>Qty to Issue (Kg)</th>
                            <th>Cones Issued</th>
                            <th width="80">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $renderedItems = isset($dcItems) ? $dcItems : (old('items') ?: []);
                        if (!empty($renderedItems)): 
                            foreach ($renderedItems as $index => $item): 
                        ?>
                                <tr class="item-row">
                                    <td><input type="hidden" name="items[<?= $index ?>][mill_name]"
                                            value="<?= esc($item['mill_name']) ?>"><?= esc($item['mill_name']) ?></td>
                                    <td><input type="hidden" name="items[<?= $index ?>][yarn_count]"
                                            value="<?= esc($item['yarn_count']) ?>"><?= esc($item['yarn_count']) ?></td>
                                    <td>
                                        <input type="hidden" name="items[<?= $index ?>][warp_weft]" value="<?= esc($item['warp_weft']) ?>">
                                        <?php if ($item['warp_weft'] === 'Warp'): ?>
                                            Warp - 
                                            <select name="items[<?= $index ?>][warp_yarn_type]" class="form-select form-select-sm d-inline-block w-auto py-0 pe-4 ps-2" style="font-size: 0.8rem; height: auto; display: inline-block;">
                                                <option value="body" <?= ($item['warp_yarn_type'] ?? '') === 'body' ? 'selected' : '' ?>>body</option>
                                                <option value="border" <?= ($item['warp_yarn_type'] ?? '') === 'border' ? 'selected' : '' ?>>border</option>
                                                <option value="kattam" <?= ($item['warp_yarn_type'] ?? '') === 'kattam' ? 'selected' : '' ?>>kattam</option>
                                            </select>
                                        <?php else: ?>
                                            <input type="hidden" name="items[<?= $index ?>][warp_yarn_type]" value="">
                                            <?= esc($item['warp_weft']) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><input type="hidden" name="items[<?= $index ?>][lot_number]"
                                            value="<?= esc($item['lot_number']) ?>"><?= esc($item['lot_number'] ?: '-') ?></td>
                                    <td><input type="hidden" name="items[<?= $index ?>][csp]"
                                            value="<?= esc($item['csp']) ?>"><?= esc($item['csp'] ?: '-') ?></td>
                                    <td><input type="hidden" name="items[<?= $index ?>][yarn_type]"
                                            value="<?= esc($item['yarn_type']) ?>"><input type="hidden"
                                            name="items[<?= $index ?>][current_color]"
                                            value="<?= esc($item['current_color']) ?>"
                                            class="row-yarn-color"><?= esc($item['current_color']) ?></td>
                                    <td><input type="hidden" name="items[<?= $index ?>][quantity_issued_kg]"
                                            value="<?= esc($item['quantity_issued_kg']) ?>"><strong><?= number_format($item['quantity_issued_kg'], 2) ?>
                                             Kg</strong></td>
                                    <td><input type="number" name="items[<?= $index ?>][cones_issued]" class="form-control form-control-sm" value="<?= (int)($item['cones_issued'] ?? 0) ?>" min="0"></td>
                                    <td><button type="button" class="btn btn-sm btn-danger btn-remove-item"><i
                                                class="fas fa-trash"></i></button></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Color-wise Ends Breakdown & Beams -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h5 class="card-title"><i class="fas fa-palette"></i> Color-wise Ends Breakdown</h5>
                            <span class="badge bg-danger ms-2" id="ends-mismatch-badge" style="display:none;">Ends
                                Mismatch</span>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-sm align-middle" id="endsBreakdownTable">
                                <thead>
                                    <tr>
                                        <th>Color <span class="text-danger">*</span></th>
                                        <th width="40%">Ends Count <span class="text-danger">*</span></th>
                                        <th width="70">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $renderedEnds = isset($colorEnds) ? $colorEnds : (old('ends_breakdown') ?: []);
                                    if (!empty($renderedEnds)): 
                                        foreach ($renderedEnds as $index => $ce): 
                                    ?>
                                            <tr class="ends-row">
                                                <td>
                                                    <select name="ends_breakdown[<?= $index ?>][color]"
                                                        class="form-select form-select-sm select-ends-color" required>
                                                        <option value="<?= esc($ce['color']) ?>"><?= esc($ce['color']) ?>
                                                        </option>
                                                    </select>
                                                </td>
                                                <td><input type="number" name="ends_breakdown[<?= $index ?>][ends_count]"
                                                        class="form-control form-control-sm input-ends-count"
                                                        value="<?= esc($ce['ends_count']) ?>" required></td>
                                                <td><button type="button" class="btn btn-sm btn-danger btn-remove-ends-row"><i
                                                            class="fas fa-trash"></i></button></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <button type="button" id="btn-add-ends-row" class="btn btn-sm btn-dark mt-2"><i
                                    class="fas fa-plus"></i> Add Color Ends</button>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h5 class="card-title"><i class="fas fa-ring"></i> Issue Empty Beams</h5>
                        </div>
                        <div class="card-body">
                            <label class="form-label">Select Beams to Send</label>
                            <select id="select-empty-beams" class="form-select mb-3" multiple style="height: 120px;">
                                <?php foreach ($emptyBeams as $b): ?>
                                    <option value="<?= esc($b['beam_number']) ?>"><?= esc($b['beam_number']) ?>
                                        (<?= esc($b['remarks'] ?: 'No remarks') ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm align-middle" id="beamsTable">
                                    <thead>
                                        <tr>
                                            <th>Beam Number</th>
                                            <th width="70">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $renderedBeams = [];
                                        if (isset($beams)) {
                                            foreach ($beams as $b) {
                                                $renderedBeams[] = $b['beam_number'];
                                            }
                                        } else if (old('beams')) {
                                            $renderedBeams = old('beams');
                                        }
                                        if (!empty($renderedBeams)):
                                            foreach ($renderedBeams as $beamNumber):
                                        ?>
                                                <tr class="beam-row">
                                                    <td><input type="hidden" name="beams[]"
                                                            value="<?= esc($beamNumber) ?>"><?= esc($beamNumber) ?>
                                                    </td>
                                                    <td><button type="button"
                                                            class="btn btn-sm btn-danger btn-remove-beam-row"><i
                                                                class="fas fa-trash"></i></button></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-success" id="btnSubmit"><i class="fas fa-check-circle"></i> Save
                Delivery Challan</button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
    $(document).ready(function () {
        var itemIndex = <?= isset($dcItems) ? count($dcItems) : 0 ?>;
        var endsIndex = <?= isset($colorEnds) ? count($colorEnds) : 0 ?>;

        // Add Yarn Item
        $('#btn-add-item').click(function () {
            var rawJson = $('#select-yarn-inventory').val();
            var qty = parseFloat($('#input-issue-qty').val()) || 0;
            var cones = parseInt($('#input-issue-cones').val()) || 0;

            if (!rawJson) { alert('Please select a yarn.'); return; }
            if (qty <= 0) { alert('Please enter a valid quantity.'); return; }

            var y = JSON.parse(rawJson);
            var available = parseFloat(y.quantity_available) || 0;
            if (qty > available) {
                alert('Cannot issue ' + qty.toFixed(2) + ' Kg. Only ' + available.toFixed(2) + ' Kg is available in stock.');
                return;
            }
            var availCones = parseInt(y.cones_available) || 0;
            if (cones > availCones) {
                alert('Warning: Issuing ' + cones + ' cones exceeds available stock (' + availCones + ' Cones).');
            }

            var warpYarnType = '';
            var warpWeftCell = '';

            if (y.warp_weft === 'Warp') {
                warpYarnType = $('#select-warp-type').val();
                warpWeftCell = 'Warp - <select name="items[' + itemIndex + '][warp_yarn_type]" class="form-select form-select-sm d-inline-block w-auto py-0 pe-4 ps-2" style="font-size: 0.8rem; height: auto;">' +
                    '<option value="body"' + (warpYarnType === 'body' ? ' selected' : '') + '>body</option>' +
                    '<option value="border"' + (warpYarnType === 'border' ? ' selected' : '') + '>border</option>' +
                    '<option value="kattam"' + (warpYarnType === 'kattam' ? ' selected' : '') + '>kattam</option>' +
                    '</select>';
            } else {
                warpWeftCell = y.warp_weft + '<input type="hidden" name="items[' + itemIndex + '][warp_yarn_type]" value="">';
            }

            var row = '<tr class="item-row">' +
                '<td><input type="hidden" name="items[' + itemIndex + '][mill_name]" value="' + y.brand_mill + '">' + y.brand_mill + '</td>' +
                '<td><input type="hidden" name="items[' + itemIndex + '][yarn_count]" value="' + y.yarn_count + '">' + y.yarn_count + '</td>' +
                '<td>' +
                '<input type="hidden" name="items[' + itemIndex + '][warp_weft]" value="' + y.warp_weft + '">' +
                warpWeftCell +
                '</td>' +
                '<td><input type="hidden" name="items[' + itemIndex + '][lot_number]" value="' + (y.lot_number || '') + '">' + (y.lot_number || '-') + '</td>' +
                '<td><input type="hidden" name="items[' + itemIndex + '][csp]" value="' + (y.csp || '') + '">' + (y.csp || '-') + '</td>' +
                '<td><input type="hidden" name="items[' + itemIndex + '][yarn_type]" value="' + y.yarn_type + '"><input type="hidden" name="items[' + itemIndex + '][current_color]" value="' + y.color + '" class="row-yarn-color">' + y.color + '</td>' +
                '<td><input type="hidden" name="items[' + itemIndex + '][quantity_issued_kg]" value="' + qty + '"><strong>' + qty.toFixed(2) + ' Kg</strong></td>' +
                '<td><input type="number" name="items[' + itemIndex + '][cones_issued]" class="form-control form-control-sm" value="' + cones + '" min="0"></td>' +
                '<td><button type="button" class="btn btn-sm btn-danger btn-remove-item"><i class="fas fa-trash"></i></button></td>' +
                '</tr>';

            $('#itemsTable tbody').append(row);
            itemIndex++;

            $('#select-yarn-inventory').val('');
            $('#input-issue-qty').val('');
            $('#input-issue-cones').val('');
            $('#select-warp-type').val('body');
            $('#warp-type-container').hide();
            $('#available-stock-lbl').hide();
            updateEndsColorDropdowns();
        });

        // Toggle Warp Type dropdown and show available stock based on selected yarn
        $('#select-yarn-inventory').change(function () {
            var rawJson = $(this).val();
            if (rawJson) {
                var y = JSON.parse(rawJson);
                var available = parseFloat(y.quantity_available) || 0;
                $('#available-stock-lbl').html('<i class="fas fa-cubes me-1"></i>Available Stock: ' + available.toFixed(2) + ' Kg').show();
                if (y.warp_weft === 'Warp') {
                    $('#warp-type-container').show();
                } else {
                    $('#warp-type-container').hide();
                }
            } else {
                $('#available-stock-lbl').hide();
                $('#warp-type-container').hide();
            }
        });

        $(document).on('click', '.btn-remove-item', function () {
            $(this).closest('tr').remove();
            updateEndsColorDropdowns();
        });

        // Color Ends
        function getSelectedYarnColors() {
            var colors = [];
            $('.row-yarn-color').each(function () {
                colors.push($(this).val());
            });
            return [...new Set(colors)];
        }

        function updateEndsColorDropdowns() {
            var colors = getSelectedYarnColors();
            $('.select-ends-color').each(function () {
                var $dropdown = $(this);
                var selectedVal = $dropdown.val();
                $dropdown.empty().append('<option value="">-- Color --</option>');
                colors.forEach(function (c) {
                    $dropdown.append('<option value="' + c + '"' + (selectedVal === c ? ' selected' : '') + '>' + c + '</option>');
                });
            });
        }

        $('#btn-add-ends-row').click(function () {
            var colors = getSelectedYarnColors();
            var optionsHtml = '<option value="">-- Color --</option>';
            colors.forEach(function (c) {
                optionsHtml += '<option value="' + c + '">' + c + '</option>';
            });

            var row = '<tr class="ends-row">' +
                '<td><select name="ends_breakdown[' + endsIndex + '][color]" class="form-select form-select-sm select-ends-color" required>' + optionsHtml + '</select></td>' +
                '<td><input type="number" name="ends_breakdown[' + endsIndex + '][ends_count]" class="form-control form-control-sm input-ends-count" required></td>' +
                '<td><button type="button" class="btn btn-sm btn-danger btn-remove-ends-row"><i class="fas fa-trash"></i></button></td>' +
                '</tr>';

            $('#endsBreakdownTable tbody').append(row);
            endsIndex++;
            validateEndsCount();
        });

        $(document).on('click', '.btn-remove-ends-row', function () {
            $(this).closest('tr').remove();
            validateEndsCount();
        });

        $(document).on('input change', '.input-ends-count, #total_ends', function () {
            validateEndsCount();
        });

        function validateEndsCount() {
            var totalEnds = parseInt($('#total_ends').val()) || 0;
            var sumEnds = 0;
            $('.input-ends-count').each(function () {
                sumEnds += parseInt($(this).val()) || 0;
            });

            if (totalEnds > 0 && sumEnds !== totalEnds) {
                $('#ends-mismatch-badge').text('Ends Mismatch: ' + totalEnds + ' / ' + sumEnds).show();
                $('#btnSubmit').prop('disabled', true);
            } else {
                $('#ends-mismatch-badge').hide();
                $('#btnSubmit').prop('disabled', false);
            }
        }

        // Beams selection
        $('#select-empty-beams').change(function () {
            $('#select-empty-beams option:selected').each(function () {
                var beamNum = $(this).val();

                // Check if already added
                var exists = false;
                $('#beamsTable tbody tr').each(function () {
                    if ($(this).find('input').val() === beamNum) exists = true;
                });

                if (!exists) {
                    var row = '<tr class="beam-row">' +
                        '<td><input type="hidden" name="beams[]" value="' + beamNum + '">' + beamNum + '</td>' +
                        '<td><button type="button" class="btn btn-sm btn-danger btn-remove-beam-row"><i class="fas fa-trash"></i></button></td>' +
                        '</tr>';
                    $('#beamsTable tbody').append(row);
                }
            });
            $(this).val([]); // reset select
        });

        $(document).on('click', '.btn-remove-beam-row', function () {
            $(this).closest('tr').remove();
        });
    });
</script>
<?= $this->endSection() ?>