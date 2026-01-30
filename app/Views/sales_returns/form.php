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
                        <div class="mt-2">
                            <span id="taxModeIndicator" class="badge bg-secondary" style="display: none;">
                                <i class="fas fa-info-circle"></i> <span id="taxModeText">Select customer to see tax mode</span>
                            </span>
                        </div>
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
                        <tr class="item-row">
                            <td><input type="text" name="items[0][description]" class="form-control" required></td>
                            <td><input type="number" name="items[0][quantity]" class="form-control qty" step="0.01" required></td>
                            <td><input type="number" name="items[0][rate]" class="form-control rate" step="0.01" required></td>
                            <td><input type="number" name="items[0][tax_percentage]" class="form-control tax" step="0.01" value="0"></td>
                            <td><input type="text" class="form-control total" readonly></td>
                            <td></td>
                        </tr>
                    </tbody>
                    <tfoot class="table-secondary">
                        <tr>
                            <td colspan="4" class="text-end fw-bold">Subtotal:</td>
                            <td><input type="text" id="subtotal" class="form-control fw-bold" readonly value="0.00"></td>
                            <td></td>
                        </tr>
                        <tbody id="taxBreakdownBody">
                            <!-- Dynamic tax rows will be inserted here -->
                        </tbody>
                        <tr>
                            <td colspan="4" class="text-end">Total Tax:</td>
                            <td><input type="text" id="taxAmount" class="form-control" readonly value="0.00"></td>
                            <td></td>
                        </tr>
                        <tr class="table-primary">
                            <td colspan="4" class="text-end fw-bold fs-5">Total:</td>
                            <td><input type="text" id="totalAmount" class="form-control fw-bold fs-5" readonly value="0.00"></td>
                            <td></td>
                        </tr>
                    </tfoot>
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
    let isInterState = false;
    const itemsBody = document.getElementById('itemsBody');
    const addItemBtn = document.getElementById('addItem');
    const taxModeIndicator = document.getElementById('taxModeIndicator');
    const taxModeText = document.getElementById('taxModeText');

    // Fetch customer state when customer is selected
    document.getElementById('customer_id').addEventListener('change', function() {
        const customerId = this.value;
        
        if (!customerId) {
            isInterState = false;
            taxModeIndicator.style.display = 'none';
            calculateTotals();
            return;
        }
        
        fetch('<?= site_url('sales_returns/customer-state') ?>/' + customerId)
            .then(response => response.json())
            .then(data => {
                isInterState = data.is_inter_state;
                
                // Update tax mode indicator
                taxModeIndicator.style.display = 'inline-block';
                if (isInterState) {
                    taxModeIndicator.className = 'badge bg-info';
                    taxModeText.textContent = 'Inter-State Transaction (IGST)';
                } else {
                    taxModeIndicator.className = 'badge bg-success';
                    taxModeText.textContent = 'Intra-State Transaction (CGST + SGST)';
                }
                
                calculateTotals();
            })
            .catch(error => {
                console.error('Error fetching customer state:', error);
            });
    });

    addItemBtn.addEventListener('click', function() {
        const row = `
            <tr class="item-row">
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
            calculateTotals();
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
            
            calculateTotals();
        }
    });

    function calculateTotals() {
        let grossSubtotal = 0;
        const itemRows = document.querySelectorAll('.item-row');
        const itemData = [];

        // Calculate gross subtotal
        itemRows.forEach(row => {
            const qty = parseFloat(row.querySelector('.qty').value) || 0;
            const rate = parseFloat(row.querySelector('.rate').value) || 0;
            const amount = qty * rate;
            const taxPercentage = parseFloat(row.querySelector('.tax').value) || 0;
            
            grossSubtotal += amount;
            itemData.push({ amount, taxPercentage });
        });

        // Calculate taxes
        let taxBreakdown = {};
        let totalTax = 0;
        
        itemData.forEach(item => {
            const taxAmount = (item.amount * item.taxPercentage) / 100;

            if (item.taxPercentage > 0) {
                taxBreakdown[item.taxPercentage] = (taxBreakdown[item.taxPercentage] || 0) + taxAmount;
                totalTax += taxAmount;
            }
        });

        // Generate dynamic tax rows
        const taxBody = document.getElementById('taxBreakdownBody');
        taxBody.innerHTML = '';
        
        Object.keys(taxBreakdown).sort((a, b) => a - b).forEach(percentage => {
            const amount = taxBreakdown[percentage];
            const rate = parseFloat(percentage);
            
            if (isInterState) {
                taxBody.innerHTML += `
                    <tr>
                        <td colspan="4" class="text-end">IGST (${rate}%):</td>
                        <td><input type="text" class="form-control" readonly value="${amount.toFixed(2)}"></td>
                        <td></td>
                    </tr>
                `;
            } else {
                const halfAmount = amount / 2;
                const halfRate = rate / 2;
                taxBody.innerHTML += `
                    <tr>
                        <td colspan="4" class="text-end">CGST (${halfRate}%):</td>
                        <td><input type="text" class="form-control" readonly value="${halfAmount.toFixed(2)}"></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-end">SGST (${halfRate}%):</td>
                        <td><input type="text" class="form-control" readonly value="${halfAmount.toFixed(2)}"></td>
                        <td></td>
                    </tr>
                `;
            }
        });
        
        const netTotal = grossSubtotal + totalTax;
        
        document.getElementById('subtotal').value = grossSubtotal.toFixed(2);
        document.getElementById('taxAmount').value = totalTax.toFixed(2);
        document.getElementById('totalAmount').value = netTotal.toFixed(2);
    }
});
</script>
<?= $this->endSection() ?>
