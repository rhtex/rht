<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= isset($dc) ? 'Edit Delivery Challan' : 'Create Delivery Challan' ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1><?= isset($dc) ? 'Edit Delivery Challan' : 'Create Delivery Challan' ?></h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('production/yarn-job-work?type='.$selectedType) ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <form action="<?= isset($dc) ? site_url('production/yarn-job-work/update/'.$dc['id']) : site_url('production/yarn-job-work/store') ?>" method="post" id="dcForm">
        <?= csrf_field() ?>
        <div class="card-body">
            <?php if (session()->has('error')) : ?>
                <div class="alert alert-danger"><?= session('error') ?></div>
            <?php endif ?>
            <?php if (session()->has('errors')) : ?>
                <div class="alert alert-danger">
                    <ul>
                    <?php foreach (session('errors') as $error) : ?>
                        <li><?= $error ?></li>
                    <?php endforeach ?>
                    </ul>
                </div>
            <?php endif ?>

            <!-- DC Header Details -->
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">DC Number <span class="text-danger">*</span></label>
                    <input type="text" name="dc_number" class="form-control" value="<?= old('dc_number', $dc['dc_number'] ?? $nextDcNumber) ?>" required readonly>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">DC Date <span class="text-danger">*</span></label>
                    <input type="date" name="dc_date" class="form-control" value="<?= old('dc_date', $dc['dc_date'] ?? date('Y-m-d')) ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Vendor Name (Job Worker) <span class="text-danger">*</span></label>
                    <input type="text" name="vendor_name" class="form-control" value="<?= old('vendor_name', $dc['vendor_name'] ?? '') ?>" required placeholder="Enter Vendor Name">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Job Work Type</label>
                    <input type="text" class="form-control bg-light" value="<?= esc($selectedType) ?>" readonly>
                    <input type="hidden" name="job_work_type" value="<?= esc($selectedType) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Expected Return Date</label>
                    <input type="date" name="expected_return_date" class="form-control" value="<?= old('expected_return_date', $dc['expected_return_date'] ?? '') ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Vehicle Details</label>
                    <input type="text" name="vehicle_details" class="form-control" value="<?= old('vehicle_details', $dc['vehicle_details'] ?? '') ?>" placeholder="e.g. TN-30-AB-1234">
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" class="form-control" rows="2" placeholder="Any instructions..."><?= esc(old('remarks', $dc['remarks'] ?? '')) ?></textarea>
                </div>
            </div>

            <!-- DC Items Section -->
            <div class="row mt-4">
                <div class="col-md-12"><h5 class="text-primary"><i class="fas fa-list"></i> Select Yarns to Issue</h5><hr></div>
                
                <div class="col-md-12">
                    <table class="table table-bordered" id="itemsTable">
                        <thead>
                            <tr class="bg-light">
                                <th width="30%">Select Yarn from Stock <span class="text-danger">*</span></th>
                                <th>Details</th>
                                <th width="15%">Quantity to Issue (Kg) <span class="text-danger">*</span></th>
                                <th width="5%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($dcItems) && !empty($dcItems)): ?>
                                <?php foreach ($dcItems as $idx => $item): ?>
                                <tr id="row-<?= $idx ?>">
                                    <td>
                                        <select name="items[<?= $idx ?>][stock_index]" class="form-select yarn-select" required>
                                            <option value="custom" selected><?= esc($item['mill_name']) ?> - <?= esc($item['yarn_count']) ?> (<?= esc($item['current_color']) ?>)</option>
                                            <?php foreach ($availableYarns as $availIdx => $yarn): ?>
                                                <option value="<?= $availIdx ?>"><?= esc($yarn['brand_mill']) ?> - <?= esc($yarn['yarn_count']) ?> - <?= esc($yarn['warp_weft']) ?> (<?= esc($yarn['color']) ?>) [Stock: <?= number_format($yarn['quantity_available'], 2) ?> Kg]</option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="hidden" name="items[<?= $idx ?>][mill_name]" class="input-mill" value="<?= esc($item['mill_name']) ?>">
                                        <input type="hidden" name="items[<?= $idx ?>][yarn_count]" class="input-count" value="<?= esc($item['yarn_count']) ?>">
                                        <input type="hidden" name="items[<?= $idx ?>][warp_weft]" class="input-warp-weft" value="<?= esc($item['warp_weft']) ?>">
                                        <input type="hidden" name="items[<?= $idx ?>][csp]" class="input-csp" value="<?= esc($item['csp']) ?>">
                                        <input type="hidden" name="items[<?= $idx ?>][lot_number]" class="input-lot" value="<?= esc($item['lot_number']) ?>">
                                        <input type="hidden" name="items[<?= $idx ?>][yarn_type]" class="input-type" value="<?= esc($item['yarn_type']) ?>">
                                        <input type="hidden" name="items[<?= $idx ?>][current_color]" class="input-color" value="<?= esc($item['current_color']) ?>">
                                    </td>
                                    <td>
                                        <div class="yarn-details text-muted">
                                            <strong>Type:</strong> <?= esc($item['yarn_type']) ?><br>
                                            <strong>Warp/Weft:</strong> <?= esc($item['warp_weft']) ?><br>
                                            <strong>Lot No:</strong> <?= esc($item['lot_number'] ?: '-') ?><br>
                                            <strong>CSP:</strong> <?= esc($item['csp'] ?: '-') ?>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="items[<?= $idx ?>][quantity_issued_kg]" class="form-control input-qty" value="<?= esc($item['quantity_issued_kg']) ?>" min="0.01" required placeholder="0.00">
                                        <small class="text-danger qty-warning" style="display:none;">Exceeds available stock!</small>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-danger btn-remove-row"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btn-add-row"><i class="fas fa-plus"></i> Add Yarn Item</button>
                </div>
            </div>
        </div>
        
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= isset($dc) ? 'Update Delivery Challan' : 'Issue Delivery Challan' ?></button>
        </div>
    </form>
</div>

<!-- Raw data store for Javascript -->
<script id="yarns-data" type="application/json">
    <?= json_encode($availableYarns) ?>
</script>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        var availableYarns = JSON.parse($('#yarns-data').html());
        var rowIndex = <?= isset($dcItems) ? count($dcItems) : 0 ?>;

        function addRow() {
            var optionsHtml = '<option value="">Select Yarn</option>';
            availableYarns.forEach(function(yarn, idx) {
                var label = yarn.brand_mill + ' - ' + yarn.yarn_count + ' - ' + yarn.warp_weft + ' (' + yarn.color + ') [Stock: ' + parseFloat(yarn.quantity_available).toFixed(2) + ' Kg]';
                optionsHtml += '<option value="' + idx + '">' + label + '</option>';
            });

            var rowHtml = `
                <tr id="row-${rowIndex}">
                    <td>
                        <select name="items[${rowIndex}][stock_index]" class="form-select yarn-select" required>
                            ${optionsHtml}
                        </select>
                        <input type="hidden" name="items[${rowIndex}][mill_name]" class="input-mill">
                        <input type="hidden" name="items[${rowIndex}][yarn_count]" class="input-count">
                        <input type="hidden" name="items[${rowIndex}][warp_weft]" class="input-warp-weft">
                        <input type="hidden" name="items[${rowIndex}][csp]" class="input-csp">
                        <input type="hidden" name="items[${rowIndex}][lot_number]" class="input-lot">
                        <input type="hidden" name="items[${rowIndex}][yarn_type]" class="input-type">
                        <input type="hidden" name="items[${rowIndex}][current_color]" class="input-color">
                    </td>
                    <td>
                        <div class="yarn-details text-muted">-</div>
                    </td>
                    <td>
                        <input type="number" step="0.01" name="items[${rowIndex}][quantity_issued_kg]" class="form-control input-qty" min="0.01" required placeholder="0.00">
                        <small class="text-danger qty-warning" style="display:none;">Exceeds available stock!</small>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger btn-remove-row"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `;
            $('#itemsTable tbody').append(rowHtml);
            rowIndex++;
        }

        // Add first row by default only in Create mode
        <?php if (!isset($dc)): ?>
        addRow();
        <?php endif; ?>

        $('#btn-add-row').on('click', function() {
            addRow();
        });

        $(document).on('click', '.btn-remove-row', function() {
            $(this).closest('tr').remove();
        });

        // Handle yarn stock selection
        $(document).on('change', '.yarn-select', function() {
            var $row = $(this).closest('tr');
            var idx = $(this).val();
            
            if (idx !== '' && idx !== 'custom') {
                var yarn = availableYarns[idx];
                
                // Populate hidden inputs
                $row.find('.input-mill').val(yarn.brand_mill);
                $row.find('.input-count').val(yarn.yarn_count);
                $row.find('.input-warp-weft').val(yarn.warp_weft);
                $row.find('.input-csp').val(yarn.csp);
                $row.find('.input-lot').val(yarn.lot_number);
                $row.find('.input-type').val(yarn.yarn_type);
                $row.find('.input-color').val(yarn.color);

                // Show details text
                var details = `
                    <strong>Type:</strong> ${yarn.yarn_type}<br>
                    <strong>Warp/Weft:</strong> ${yarn.warp_weft}<br>
                    <strong>Lot No:</strong> ${yarn.lot_number || '-'}<br>
                    <strong>CSP:</strong> ${yarn.csp || '-'}<br>
                    <strong>Available:</strong> ${parseFloat(yarn.quantity_available).toFixed(2)} Kg
                `;
                $row.find('.yarn-details').html(details);

                // Set max limit on qty input
                $row.find('.input-qty').attr('max', yarn.quantity_available);
            } else if (idx === '') {
                $row.find('.yarn-details').text('-');
                $row.find('.input-qty').removeAttr('max');
            }
        });

        // Validate quantity vs stock in real time
        $(document).on('input', '.input-qty', function() {
            var $row = $(this).closest('tr');
            var val = parseFloat($(this).val()) || 0;
            var max = parseFloat($(this).attr('max')) || 0;

            if (max > 0 && val > max) {
                $row.find('.qty-warning').show();
            } else {
                $row.find('.qty-warning').hide();
            }
        });

        $('#dcForm').on('submit', function(e) {
            var hasError = false;
            $('.input-qty').each(function() {
                var val = parseFloat($(this).val()) || 0;
                var max = parseFloat($(this).attr('max')) || 0;
                if (max > 0 && val > max) {
                    hasError = true;
                }
            });

            if (hasError) {
                e.preventDefault();
                alert('Please correct the quantities. You cannot issue more than the available stock.');
            }
        });
    });
</script>
<?= $this->endSection() ?>
