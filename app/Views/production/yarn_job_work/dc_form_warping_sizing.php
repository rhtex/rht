<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= isset($dc) ? 'Edit Warping & Sizing DC' : 'Create Warping & Sizing DC' ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1><?= isset($dc) ? 'Edit Warping & Sizing DC' : 'Create Warping & Sizing DC' ?></h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('production/yarn-job-work?type=Warping & Sizing') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
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
                    <input type="text" class="form-control bg-light" value="Warping & Sizing" readonly>
                    <input type="hidden" name="job_work_type" value="Warping & Sizing">
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

            <!-- Warping & Sizing Specifications Section (Static) -->
            <div class="card card-outline card-info mt-4">
                <div class="card-header">
                    <h5 class="card-title text-info"><i class="fas fa-drafting-compass me-1"></i> Warping & Sizing Specifications</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Design Pattern</label>
                            <input type="text" name="design_pattern" class="form-control" value="<?= old('design_pattern', $dc['design_pattern'] ?? '') ?>" placeholder="e.g. Diamond Border, Plain Body">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Total No. of Ends <span class="text-danger">*</span></label>
                            <input type="number" name="total_ends" id="total_ends" class="form-control" value="<?= old('total_ends', $dc['total_ends'] ?? '') ?>" placeholder="e.g. 4000" required>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <!-- Beams Sent Column -->
                        <div class="col-md-6">
                            <h6 class="text-primary mb-2 fw-bold"><i class="fas fa-ring"></i> Empty Beams Sent</h6>
                            <table class="table table-bordered table-sm" id="beamsTable">
                                <thead>
                                    <tr class="bg-light">
                                        <th>Select Beam <span class="text-danger">*</span></th>
                                        <th>Remarks</th>
                                        <th width="10%">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(isset($beams) && !empty($beams)): ?>
                                        <?php foreach($beams as $bIdx => $beam): ?>
                                            <tr>
                                                <td>
                                                    <select name="beams[<?= $bIdx ?>][beam_number]" class="form-select form-select-sm beam-select" required>
                                                        <option value="<?= esc($beam['beam_number']) ?>" selected><?= esc($beam['beam_number']) ?></option>
                                                        <?php foreach($availableBeams as $ab): ?>
                                                            <?php if($ab['beam_number'] !== $beam['beam_number']): ?>
                                                                <option value="<?= esc($ab['beam_number']) ?>"><?= esc($ab['beam_number']) ?></option>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="text" name="beams[<?= $bIdx ?>][remarks]" class="form-control form-control-sm" value="<?= esc($beam['remarks']) ?>">
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-xs btn-danger btn-remove-beam"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-xs btn-outline-primary" id="btn-add-beam"><i class="fas fa-plus"></i> Add Beam</button>
                        </div>

                        <!-- Color Ends Column -->
                        <div class="col-md-6">
                            <h6 class="text-primary mb-2 fw-bold"><i class="fas fa-palette"></i> Color-wise Ends Breakdown</h6>
                            <table class="table table-bordered table-sm" id="colorEndsTable">
                                <thead>
                                    <tr class="bg-light">
                                        <th>Color <span class="text-danger">*</span></th>
                                        <th>Ends Count <span class="text-danger">*</span></th>
                                        <th width="10%">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(isset($colorEnds) && !empty($colorEnds)): ?>
                                        <?php foreach($colorEnds as $cIdx => $ce): ?>
                                            <tr>
                                                <td>
                                                    <select name="color_ends[<?= $cIdx ?>][color]" class="form-select form-select-sm color-end-select" required>
                                                        <option value="<?= esc($ce['color']) ?>" selected><?= esc($ce['color']) ?></option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" name="color_ends[<?= $cIdx ?>][ends_count]" class="form-control form-control-sm input-ends-count" value="<?= esc($ce['ends_count']) ?>" required>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-xs btn-danger btn-remove-color-end"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <div class="d-flex justify-content-between align-items-center">
                                <button type="button" class="btn btn-xs btn-outline-primary" id="btn-add-color-end"><i class="fas fa-plus"></i> Add Color End</button>
                                <span class="badge bg-danger p-2" id="ends-match-badge" style="font-size: 13px;">
                                    <i class="fas fa-exclamation-triangle"></i> Ends Mismatch: 0 / 0
                                </span>
                            </div>
                        </div>
                    </div>
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
<script id="beams-data" type="application/json">
    <?= json_encode($availableBeams ?? []) ?>
</script>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        var availableYarns = JSON.parse($('#yarns-data').html());
        var availableBeams = JSON.parse($('#beams-data').html());
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
            updateColorBreakdownDropdowns();
            validateEndsMatching();
        });

        // Beam row index and functions
        var beamIndex = <?= isset($beams) ? count($beams) : 0 ?>;
        function addBeamRow() {
            var beamOptionsHtml = '<option value="">Select Beam</option>';
            availableBeams.forEach(function(b) {
                beamOptionsHtml += `<option value="${b.beam_number}">${b.beam_number}</option>`;
            });

            var rowHtml = `
                <tr>
                    <td>
                        <select name="beams[${beamIndex}][beam_number]" class="form-select form-select-sm beam-select" required>
                            ${beamOptionsHtml}
                        </select>
                    </td>
                    <td>
                        <input type="text" name="beams[${beamIndex}][remarks]" class="form-control form-control-sm" placeholder="Remarks">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-xs btn-danger btn-remove-beam"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `;
            $('#beamsTable tbody').append(rowHtml);
            beamIndex++;
        }

        // Color end row index and functions
        var colorEndIndex = <?= isset($colorEnds) ? count($colorEnds) : 0 ?>;
        function addColorEndRow() {
            var colors = getSelectedYarnColors();
            var colorOptions = '<option value="">Select Color</option>';
            colors.forEach(function(c) {
                colorOptions += `<option value="${c}">${c}</option>`;
            });

            var rowHtml = `
                <tr>
                    <td>
                        <select name="color_ends[${colorEndIndex}][color]" class="form-select form-select-sm color-end-select" required>
                            ${colorOptions}
                        </select>
                    </td>
                    <td>
                        <input type="number" name="color_ends[${colorEndIndex}][ends_count]" class="form-control form-control-sm input-ends-count" placeholder="e.g. 1200" required>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-xs btn-danger btn-remove-color-end"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `;
            $('#colorEndsTable tbody').append(rowHtml);
            colorEndIndex++;
            validateEndsMatching();
        }

        $('#btn-add-beam').on('click', function() { addBeamRow(); });
        $(document).on('click', '.btn-remove-beam', function() { $(this).closest('tr').remove(); });

        $('#btn-add-color-end').on('click', function() { addColorEndRow(); });
        $(document).on('click', '.btn-remove-color-end', function() {
            $(this).closest('tr').remove();
            validateEndsMatching();
        });

        // Add first beam and color end row by default only in Create mode if empty
        <?php if (!isset($dc)): ?>
        addBeamRow();
        addColorEndRow();
        <?php endif; ?>

        // Helper functions to get selected yarn colors and sync dropdowns
        function getSelectedYarnColors() {
            var colors = [];
            $('.yarn-select').each(function() {
                var idx = $(this).val();
                if (idx !== '' && idx !== 'custom') {
                    var yarn = availableYarns[idx];
                    if (yarn && yarn.color && !colors.includes(yarn.color)) {
                        colors.push(yarn.color);
                    }
                } else if (idx === 'custom') {
                    var color = $(this).closest('tr').find('.input-color').val();
                    if (color && !colors.includes(color)) {
                        colors.push(color);
                    }
                }
            });
            return colors;
        }

        function updateColorBreakdownDropdowns() {
            var colors = getSelectedYarnColors();
            $('.color-end-select').each(function() {
                var $select = $(this);
                var currentVal = $select.val();
                
                var html = '<option value="">Select Color</option>';
                colors.forEach(function(c) {
                    html += `<option value="${c}" ${c === currentVal ? 'selected' : ''}>${c}</option>`;
                });
                $select.html(html);
            });
        }

        // Real-time verification of Ends matching
        function validateEndsMatching() {
            var totalEnds = parseInt($('input[name="total_ends"]').val()) || 0;
            var breakdownSum = 0;
            $('.input-ends-count').each(function() {
                breakdownSum += parseInt($(this).val()) || 0;
            });

            var $badge = $('#ends-match-badge');
            if (totalEnds === breakdownSum && totalEnds > 0) {
                $badge.removeClass('bg-danger').addClass('bg-success');
                $badge.html('<i class="fas fa-check-circle me-1"></i> Ends Match: ' + breakdownSum + ' / ' + totalEnds);
            } else {
                $badge.removeClass('bg-success').addClass('bg-danger');
                $badge.html('<i class="fas fa-exclamation-triangle me-1"></i> Ends Mismatch: ' + breakdownSum + ' / ' + totalEnds);
            }
        }

        // Trigger real-time ends validation on input change
        $(document).on('input change keyup', 'input[name="total_ends"], .input-ends-count', function() {
            validateEndsMatching();
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

            // Sync color breakdown options
            updateColorBreakdownDropdowns();
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
            // 1. Yarn Qty Validation
            var hasQtyError = false;
            $('.input-qty').each(function() {
                var val = parseFloat($(this).val()) || 0;
                var max = parseFloat($(this).attr('max')) || 0;
                if (max > 0 && val > max) {
                    hasQtyError = true;
                }
            });

            if (hasQtyError) {
                e.preventDefault();
                alert('Please correct the quantities. You cannot issue more than the available stock.');
                return false;
            }

            // 2. Ends Matching Validation
            var totalEnds = parseInt($('input[name="total_ends"]').val()) || 0;
            var breakdownSum = 0;
            $('.input-ends-count').each(function() {
                breakdownSum += parseInt($(this).val()) || 0;
            });

            if (totalEnds !== breakdownSum) {
                e.preventDefault();
                alert('Validation Error: The sum of ends in the Color-wise Breakdown (' + breakdownSum + ') must exactly match the Total No. of Ends (' + totalEnds + ').');
                return false;
            }
        });

        // Run initial color dropdown update and ends validation on load
        updateColorBreakdownDropdowns();
        validateEndsMatching();
    });
</script>
<?= $this->endSection() ?>
