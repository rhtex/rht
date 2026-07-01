<?= $this->extend('layouts/master') ?>
<?= $this->section('title') ?><?= isset($dc) ? 'Edit Dyeing DC' : 'Create Dyeing DC' ?><?= $this->endSection() ?>
<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6"><h1><?= isset($dc) ? 'Edit Dyeing DC' : 'New Dyeing DC' ?></h1></div>
    <div class="col-sm-6 text-end"><a href="<?= site_url('production/yarn-dyeing') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a></div>
</div>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <form action="<?= isset($dc) ? site_url('production/yarn-dyeing/update/'.$dc['id']) : site_url('production/yarn-dyeing/store') ?>" method="post" id="dcForm">
        <?= csrf_field() ?>
        <div class="card-body">
            <?php if(session()->has('errors')): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                    <?php foreach(session('errors') as $e): ?>
                        <li><?= esc($e) ?></li>
                    <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">DC Number <span class="text-danger">*</span></label>
                    <input type="text" name="dc_number" class="form-control" value="<?= old('dc_number', isset($dc) ? $dc['dc_number'] : $nextDcNumber) ?>" required readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">DC Date <span class="text-danger">*</span></label>
                    <input type="date" name="dc_date" class="form-control" value="<?= old('dc_date', isset($dc) ? $dc['dc_date'] : date('Y-m-d')) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Vendor (Dyer) <span class="text-danger">*</span></label>
                    <input type="text" name="vendor_name" class="form-control" value="<?= old('vendor_name', isset($dc) ? $dc['vendor_name'] : '') ?>" placeholder="Dyer name" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Expected Return Date</label>
                    <input type="date" name="expected_return_date" class="form-control" value="<?= old('expected_return_date', isset($dc) ? $dc['expected_return_date'] : '') ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Vehicle Details</label>
                    <input type="text" name="vehicle_details" class="form-control" value="<?= old('vehicle_details', isset($dc) ? $dc['vehicle_details'] : '') ?>" placeholder="Vehicle No.">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" class="form-control" rows="1"><?= old('remarks', isset($dc) ? $dc['remarks'] : '') ?></textarea>
                </div>
            </div>

            <!-- Select Yarn -->
            <div class="card card-outline card-secondary mt-3">
                <div class="card-header"><h5 class="card-title">Select Yarn & Issue Qty</h5></div>
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-5 mb-2">
                            <label class="form-label">Select Raw Yarn from Warehouse</label>
                            <select id="select-yarn-inventory" class="form-select">
                                <option value="">-- Choose Yarn --</option>
                                <?php foreach($availableYarns as $y): ?>
                                    <option value="<?= htmlspecialchars(json_encode($y)) ?>">
                                        <?= esc($y['brand_mill']) ?> - <?= esc($y['yarn_count']) ?> (Lot: <?= esc($y['lot_number'] ?: '-') ?>, Qty: <?= number_format($y['quantity_available'], 2) ?> Kg, Cones: <?= (int)($y['cones_available'] ?? 0) ?>, Color: <?= esc($y['color']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Quantity to Issue (Kg)</label>
                            <input type="number" step="0.01" id="input-issue-qty" class="form-control" min="0.01" placeholder="0.00">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="form-label">Cones to Issue</label>
                            <input type="number" id="input-issue-cones" class="form-control" min="0" placeholder="0">
                        </div>
                        <div class="col-md-2 mb-2">
                            <button type="button" id="btn-add-item" class="btn btn-dark w-100"><i class="fas fa-plus"></i> Add Item</button>
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
                            <th>Current Color</th>
                            <th>Required Color <span class="text-danger">*</span></th>
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
                                    <td><input type="hidden" name="items[<?= $index ?>][mill_name]" value="<?= esc($item['mill_name']) ?>"><?= esc($item['mill_name']) ?></td>
                                    <td><input type="hidden" name="items[<?= $index ?>][yarn_count]" value="<?= esc($item['yarn_count']) ?>"><?= esc($item['yarn_count']) ?></td>
                                    <td><input type="hidden" name="items[<?= $index ?>][warp_weft]" value="<?= esc($item['warp_weft']) ?>"><?= esc($item['warp_weft']) ?></td>
                                    <td><input type="hidden" name="items[<?= $index ?>][lot_number]" value="<?= esc($item['lot_number']) ?>"><?= esc($item['lot_number'] ?: '-') ?></td>
                                    <td><input type="hidden" name="items[<?= $index ?>][csp]" value="<?= esc($item['csp']) ?>"><?= esc($item['csp'] ?: '-') ?></td>
                                    <td><input type="hidden" name="items[<?= $index ?>][yarn_type]" value="<?= esc($item['yarn_type']) ?>"><input type="hidden" name="items[<?= $index ?>][current_color]" value="<?= esc($item['current_color']) ?>"><?= esc($item['current_color']) ?></td>
                                    <td>
                                        <select name="items[<?= $index ?>][required_color]" class="form-select form-select-sm select2-target-color" required>
                                            <option value="">-- Select Color --</option>
                                            <?php if(!empty($colorsList)): ?>
                                                <?php foreach($colorsList as $c): ?>
                                                    <option value="<?= esc($c['name']) ?>" <?= esc($item['required_color']) === $c['name'] ? 'selected' : '' ?> data-palette="<?= esc($c['color_palette'] ?: '') ?>"><?= esc($c['name']) ?></option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </td>
                                    <td><input type="hidden" name="items[<?= $index ?>][quantity_issued_kg]" value="<?= esc($item['quantity_issued_kg']) ?>"><strong><?= number_format($item['quantity_issued_kg'], 2) ?> Kg</strong></td>
                                    <td><input type="number" name="items[<?= $index ?>][cones_issued]" class="form-control form-control-sm" value="<?= esc($item['cones_issued']) ?>" min="0"></td>
                                    <td><button type="button" class="btn btn-sm btn-danger btn-remove-item"><i class="fas fa-trash"></i></button></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-success" id="btnSubmit"><i class="fas fa-check-circle"></i> Save Delivery Challan</button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    var itemIndex = <?= isset($dcItems) ? count($dcItems) : 0 ?>;
    
    $('#btn-add-item').click(function() {
        var rawJson = $('#select-yarn-inventory').val();
        var qty = parseFloat($('#input-issue-qty').val()) || 0;
        var cones = parseInt($('#input-issue-cones').val()) || 0;
        
        if (!rawJson) { alert('Please select a yarn.'); return; }
        if (qty <= 0) { alert('Please enter a valid quantity.'); return; }
        
        var y = JSON.parse(rawJson);
        if (qty > parseFloat(y.quantity_available)) {
            alert('Cannot issue more than available stock (' + y.quantity_available + ' Kg)');
            return;
        }
        var availCones = parseInt(y.cones_available) || 0;
        if (cones > availCones) {
            alert('Warning: Issuing ' + cones + ' cones exceeds available stock (' + availCones + ' Cones).');
        }

        var colorSelectHtml = '<select name="items[' + itemIndex + '][required_color]" class="form-select form-select-sm select2-target-color" required>' +
            '<option value="">-- Select Color --</option>';
        <?php if(!empty($colorsList)): ?>
            <?php foreach($colorsList as $c): ?>
                colorSelectHtml += '<option value="<?= esc($c['name']) ?>" data-palette="<?= esc($c['color_palette'] ?: '') ?>"><?= esc($c['name']) ?></option>';
            <?php endforeach; ?>
        <?php endif; ?>
        colorSelectHtml += '</select>';

        var row = '<tr class="item-row">' +
            '<td><input type="hidden" name="items[' + itemIndex + '][mill_name]" value="' + y.brand_mill + '">' + y.brand_mill + '</td>' +
            '<td><input type="hidden" name="items[' + itemIndex + '][yarn_count]" value="' + y.yarn_count + '">' + y.yarn_count + '</td>' +
            '<td><input type="hidden" name="items[' + itemIndex + '][warp_weft]" value="' + y.warp_weft + '">' + y.warp_weft + '</td>' +
            '<td><input type="hidden" name="items[' + itemIndex + '][lot_number]" value="' + (y.lot_number || '') + '">' + (y.lot_number || '-') + '</td>' +
            '<td><input type="hidden" name="items[' + itemIndex + '][csp]" value="' + (y.csp || '') + '">' + (y.csp || '-') + '</td>' +
            '<td><input type="hidden" name="items[' + itemIndex + '][yarn_type]" value="' + y.yarn_type + '"><input type="hidden" name="items[' + itemIndex + '][current_color]" value="' + y.color + '">' + y.color + '</td>' +
            '<td>' + colorSelectHtml + '</td>' +
            '<td><input type="hidden" name="items[' + itemIndex + '][quantity_issued_kg]" value="' + qty + '"><strong>' + qty.toFixed(2) + ' Kg</strong></td>' +
            '<td><input type="number" name="items[' + itemIndex + '][cones_issued]" class="form-control form-control-sm" value="' + cones + '" min="0"></td>' +
            '<td><button type="button" class="btn btn-sm btn-danger btn-remove-item"><i class="fas fa-trash"></i></button></td>' +
            '</tr>';
            
        var $rowObj = $(row);
        $('#itemsTable tbody').append($rowObj);
        
        // Initialize select2 on the newly added row color dropdown
        $rowObj.find('.select2-target-color').select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('#itemsTable'),
            templateResult: formatColorOption,
            templateSelection: formatColorOption
        });

        itemIndex++;
        
        // Reset
        $('#select-yarn-inventory').val('').trigger('change');
        $('#input-issue-qty').val('');
        $('#input-issue-cones').val('');
    });

    $(document).on('click', '.btn-remove-item', function() {
        $(this).closest('tr').remove();
    });

    // Format option template with color swatch
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

    // Initialize Select2 on page load for any already rendered items (e.g. from old data)
    $('.select2-target-color').select2({
        theme: 'bootstrap-5',
        width: '100%',
        templateResult: formatColorOption,
        templateSelection: formatColorOption
    });
    
    $('#select-yarn-inventory').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });
});
</script>
<?= $this->endSection() ?>