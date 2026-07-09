<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1><?= esc($title) ?></h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('production/warp-allocations') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Warp & Weft Allocation Form</h3>
        </div>
        <form action="<?= site_url('production/warp-allocations/store') ?>" method="post">
            <?= csrf_field() ?>
            <div class="card-body">
                <!-- Weaver and Loom Details -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Weaver <span class="text-danger">*</span></label>
                        <select name="weaver_id" id="weaver_id" class="form-control select2" required>
                            <option value="">Select Weaver</option>
                            <?php foreach($weavers as $weaver): ?>
                                <option value="<?= $weaver['id'] ?>"><?= esc($weaver['name']) ?> (<?= esc($weaver['code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Loom <span class="text-danger">*</span></label>
                        <select name="loom_id" id="loom_id" class="form-control" required disabled>
                            <option value="">Select Weaver First</option>
                        </select>
                        <small id="loom_info" class="form-text text-info fw-bold mt-1"></small>
                    </div>
                </div>

                <hr>

                <!-- Warp Beam Details -->
                <h5 class="mb-3">Warp Allocation (Optional)</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Loaded Warp Beam</label>
                        <select name="beam_id" id="beam_id" class="form-control select2">
                            <option value="">-- No Warp Beam --</option>
                            <?php foreach($beams as $beam): ?>
                                <option value="<?= $beam['id'] ?>"><?= esc($beam['beam_number']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <hr>

                <!-- Weft Yarn Details -->
                <h5 class="mb-3">Weft Allocation (Optional)</h5>
                <div class="table-responsive">
                    <table class="table table-bordered" id="weftTable">
                        <thead>
                            <tr>
                                <th>Yarn <span class="text-danger">*</span></th>
                                <th>Batch / Lot</th>
                                <th>Issue Weight (Kg) <span class="text-danger">*</span></th>
                                <th>Rate/Kg (For Sales)</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Start with one empty row -->
                            <tr>
                                <td>
                                    <select name="wefts[0][yarn_id]" class="form-control yarn-select">
                                        <option value="">Select Yarn</option>
                                        <?php foreach($yarns as $yarn): ?>
                                            <option value="<?= $yarn['id'] ?>" data-stock="<?= $yarn['stock_kg'] ?>"><?= esc($yarn['name']) ?> (Stock: <?= $yarn['stock_kg'] ?>kg)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="wefts[0][batch_number]" class="form-control">
                                </td>
                                <td>
                                    <input type="number" step="0.001" name="wefts[0][issued_weight]" class="form-control weight-input">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="wefts[0][rate]" class="form-control">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger remove-btn"><i class="fas fa-times"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-sm btn-success mt-2" id="addWeftBtn"><i class="fas fa-plus"></i> Add Weft Yarn</button>
                </div>

                <hr>
                
                <div class="row mt-3">
                    <div class="col-md-4 mb-3">
                        <label>Expected Return Date</label>
                        <input type="date" name="expected_return_date" class="form-control">
                    </div>
                    <div class="col-md-8 mb-3">
                        <label>Remarks</label>
                        <input type="text" name="remarks" class="form-control">
                    </div>
                </div>
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary" id="submitBtn">Allocate Warp & Weft</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $('.select2').select2({ theme: 'bootstrap-5' });

        $('#weaver_id').change(function() {
            var weaverId = $(this).val();
            var loomSelect = $('#loom_id');
            var loomInfo = $('#loom_info');
            
            loomSelect.empty().append('<option value="">Select Loom</option>');
            loomInfo.text('');
            
            if (weaverId) {
                loomSelect.prop('disabled', false);
                $.get('<?= site_url('production/looms/by-weaver/') ?>' + weaverId, function(response) {
                    if (response.status === 'success') {
                        if (response.data.length > 0) {
                            response.data.forEach(function(loom) {
                                loomSelect.append('<option value="' + loom.id + '" data-contract="' + loom.contract_type + '">' + loom.loom_number + ' - ' + loom.contract_type + '</option>');
                            });
                        } else {
                            loomSelect.empty().append('<option value="">No Active Looms Found</option>');
                            loomSelect.prop('disabled', true);
                        }
                    }
                });
            } else {
                loomSelect.prop('disabled', true);
            }
        });

        $('#loom_id').change(function() {
            var contractType = $(this).find(':selected').data('contract');
            var loomInfo = $('#loom_info');
            
            if (contractType) {
                if (contractType === 'Job Work') {
                    loomInfo.text('System will generate a single Delivery Challan (DC) for Warp & Weft.');
                    loomInfo.removeClass('text-warning').addClass('text-info');
                } else if (contractType === 'Sale & Buy Back') {
                    loomInfo.text('System will generate a single Sales Invoice for Warp & Weft. Ensure Weaver has a Customer profile.');
                    loomInfo.removeClass('text-info').addClass('text-warning');
                }
            } else {
                loomInfo.text('');
            }
        });

        // Dynamic Weft Row Addition
        var rowIdx = 1;
        $('#addWeftBtn').click(function() {
            var newRow = `
                <tr>
                    <td>
                        <select name="wefts[${rowIdx}][yarn_id]" class="form-control yarn-select">
                            <option value="">Select Yarn</option>
                            <?php foreach($yarns as $yarn): ?>
                                <option value="<?= $yarn['id'] ?>" data-stock="<?= $yarn['stock_kg'] ?>"><?= esc($yarn['name']) ?> (Stock: <?= $yarn['stock_kg'] ?>kg)</option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <input type="text" name="wefts[${rowIdx}][batch_number]" class="form-control">
                    </td>
                    <td>
                        <input type="number" step="0.001" name="wefts[${rowIdx}][issued_weight]" class="form-control weight-input">
                    </td>
                    <td>
                        <input type="number" step="0.01" name="wefts[${rowIdx}][rate]" class="form-control">
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger remove-btn"><i class="fas fa-times"></i></button>
                    </td>
                </tr>
            `;
            $('#weftTable tbody').append(newRow);
            rowIdx++;
        });

        $(document).on('click', '.remove-btn', function() {
            $(this).closest('tr').remove();
        });

        // Basic frontend validation to ensure at least warp or weft is selected
        $('#submitBtn').click(function(e) {
            var beamId = $('#beam_id').val();
            var hasWeft = false;

            $('.yarn-select').each(function() {
                var yarnVal = $(this).val();
                var weightVal = $(this).closest('tr').find('.weight-input').val();
                if (yarnVal && weightVal && parseFloat(weightVal) > 0) {
                    hasWeft = true;
                }
            });

            if (!beamId && !hasWeft) {
                e.preventDefault();
                alert('You must allocate at least a Warp Beam or Weft Yarn!');
            }
        });
    });
</script>
<?= $this->endSection() ?>
