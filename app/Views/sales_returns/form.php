<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= $title ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1><?= $title ?></h1>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <form action="<?= site_url('sales_returns/store') ?>" method="post" id="returnForm">
        <?= csrf_field() ?>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="customer_id">Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" id="customer_id" class="form-control select2" required>
                            <option value="">Select Customer</option>
                            <?php foreach($customers as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= esc($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="return_number">Return #</label>
                        <input type="text" name="return_number" id="return_number" class="form-control" value="<?= $return_number ?>" readonly>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="return_date">Date <span class="text-danger">*</span></label>
                        <input type="date" name="return_date" id="return_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group mb-3">
                        <label for="reason">Reason for Return</label>
                        <textarea name="reason" id="reason" class="form-control" rows="2"></textarea>
                    </div>
                </div>
            </div>

            <hr>
            <h5>Item Details</h5>
            <div class="table-responsive">
                <table class="table table-bordered" id="itemsTable">
                    <thead>
                        <tr>
                            <th width="40%">Description</th>
                            <th width="15%">Quantity</th>
                            <th width="15%">Rate</th>
                            <th width="15%">Tax (%)</th>
                            <th width="15%">Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <tr>
                            <td><input type="text" name="items[0][description]" class="form-control" required></td>
                            <td><input type="number" name="items[0][quantity]" class="form-control qty" step="0.01" required></td>
                            <td><input type="number" name="items[0][rate]" class="form-control rate" step="0.01" required></td>
                            <td><input type="number" name="items[0][tax_percentage]" class="form-control tax" step="0.01" value="0"></td>
                            <td><input type="text" class="form-control total" readonly></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
                <button type="button" class="btn btn-sm btn-success" id="addItem"><i class="fas fa-plus"></i> Add Item</button>
            </div>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary">Save Sales Return</button>
            <a href="<?= site_url('sales_returns') ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let rowCount = 1;
    const itemsBody = document.getElementById('itemsBody');
    const addItemBtn = document.getElementById('addItem');

    addItemBtn.addEventListener('click', function() {
        const row = `
            <tr>
                <td><input type="text" name="items[${rowCount}][description]" class="form-control" required></td>
                <td><input type="number" name="items[${rowCount}][quantity]" class="form-control qty" step="0.01" required></td>
                <td><input type="number" name="items[${rowCount}][rate]" class="form-control rate" step="0.01" required></td>
                <td><input type="number" name="items[${rowCount}][tax_percentage]" class="form-control tax" step="0.01" value="0"></td>
                <td><input type="text" class="form-control total" readonly></td>
                <td><button type="button" class="btn btn-sm btn-danger remove-row"><i class="fas fa-trash"></i></button></td>
            </tr>
        `;
        itemsBody.insertAdjacentHTML('beforeend', row);
        rowCount++;
    });

    itemsBody.addEventListener('click', function(e) {
        if (e.target.closest('.remove-row')) {
            e.target.closest('tr').remove();
        }
    });

    itemsBody.addEventListener('input', function(e) {
        if (e.target.classList.contains('qty') || e.target.classList.contains('rate') || e.target.classList.contains('tax')) {
            const row = e.target.closest('tr');
            const qty = parseFloat(row.querySelector('.qty').value) || 0;
            const rate = parseFloat(row.querySelector('.rate').value) || 0;
            const tax = parseFloat(row.querySelector('.tax').value) || 0;
            
            const lineTotal = qty * rate;
            const taxAmount = (lineTotal * tax) / 100;
            row.querySelector('.total').value = (lineTotal + taxAmount).toFixed(2);
        }
    });
});
</script>
<?= $this->endSection() ?>
