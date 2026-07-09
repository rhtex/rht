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
    <div class="row">
        <div class="col-md-3">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">Allocation Info</h3>
                </div>
                <div class="card-body">
                    <strong>Weaver:</strong><br>
                    <?= esc($allocation['weaver_name']) ?><br><br>
                    
                    <strong>Loom:</strong><br>
                    <?= esc($allocation['loom_number']) ?><br><br>
                    
                    <strong>Warp Beam:</strong><br>
                    <?= $allocation['warp_beam_id'] ? esc($allocation['beam_number']) : '<span class="text-muted">None</span>' ?><br><br>

                    <strong>Contract:</strong><br>
                    <span class="badge bg-info"><?= esc($allocation['contract_type']) ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Receipt Details</h3>
                </div>
                <form action="<?= site_url('production/receipts/store/' . $allocation['id']) ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="card-body">
                        <table class="table table-bordered" id="itemsTable">
                            <thead>
                                <tr>
                                    <th>Item Type</th>
                                    <th>Quality</th>
                                    <th>Qty (Meters/Sarees)</th>
                                    <th>Rate/Unit</th>
                                    <th>Amount</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <input type="text" class="form-control" value="Finished Goods" readonly>
                                    </td>
                                    <td>
                                        <select name="items[0][quality_status]" class="form-control">
                                            <option value="Good">Good</option>
                                            <option value="Damaged">Damaged</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="items[0][quantity]" class="form-control qty-input" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="items[0][rate]" class="form-control rate-input" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" class="form-control amount-input" readonly>
                                    </td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-sm btn-success mt-2" id="addItemBtn"><i class="fas fa-plus"></i> Add Row</button>

                        <div class="row mt-4">
                            <div class="col-md-6 mb-3">
                                <label>Final Receipt? (Closes Beam)</label>
                                <select name="is_final_receipt" class="form-control">
                                    <option value="1">Yes - Mark Beam as Empty</option>
                                    <option value="0">No - Partial Return</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Remarks</label>
                                <input type="text" name="remarks" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary">Save Receipt</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        var rowIdx = 1;

        $('#addItemBtn').click(function() {
            var newRow = `
                <tr>
                    <td>
                        <input type="text" class="form-control" value="Finished Goods" readonly>
                    </td>
                    <td>
                        <select name="items[${rowIdx}][quality_status]" class="form-control">
                            <option value="Good">Good</option>
                            <option value="Damaged">Damaged</option>
                        </select>
                    </td>
                    <td>
                        <input type="number" step="0.01" name="items[${rowIdx}][quantity]" class="form-control qty-input" required>
                    </td>
                    <td>
                        <input type="number" step="0.01" name="items[${rowIdx}][rate]" class="form-control rate-input" required>
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control amount-input" readonly>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger remove-btn"><i class="fas fa-times"></i></button>
                    </td>
                </tr>
            `;
            $('#itemsTable tbody').append(newRow);
            rowIdx++;
        });

        $(document).on('click', '.remove-btn', function() {
            $(this).closest('tr').remove();
            calculateTotals();
        });

        $(document).on('input', '.qty-input, .rate-input', function() {
            var row = $(this).closest('tr');
            var qty = parseFloat(row.find('.qty-input').val()) || 0;
            var rate = parseFloat(row.find('.rate-input').val()) || 0;
            var amount = qty * rate;
            row.find('.amount-input').val(amount.toFixed(2));
        });
    });
</script>
<?= $this->endSection() ?>
