<?= $this->extend('layouts/master') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0 text-dark"><?= $title ?></h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('vendors/returns') ?>">Pending Returns</a></li>
                <li class="breadcrumb-item active">Create Return Shipment</li>
            </ol>
        </div>
    </div>

    <div class="card card-outline card-danger">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-barcode"></i> Scan & Return Items</h3>
        </div>
        <div class="card-body">
            <form id="returnForm" action="<?= site_url('vendors/returns/process') ?>" method="post">
                <?= csrf_field() ?>
                
                <!-- Vendor Selection -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Select Vendor <span class="text-danger">*</span></label>
                        <select name="vendor_id" id="vendorSelect" class="form-select select2" required>
                            <option value="">-- Choose Vendor --</option>
                            <?php foreach ($vendors as $vendor): ?>
                                <option value="<?= $vendor['id'] ?>">
                                    <?= esc($vendor['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Return Action <span class="text-danger">*</span></label>
                        <select name="return_action" id="returnAction" class="form-select" required>
                            <option value="Returned">Returned (Create Credit)</option>
                            <option value="Exchanged">Exchanged (No Credit)</option>
                        </select>
                    </div>
                </div>

                <!-- Barcode Scanner -->
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Scan Barcode</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                            <input type="text" id="barcodeInput" class="form-control form-control-lg" 
                                   placeholder="Scan or type barcode here..." autocomplete="off">
                            <button type="button" id="addBtn" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </div>
                        <small class="text-muted">Focus on this field and scan barcodes. Press Enter or click Add.</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Items Scanned</label>
                        <h2 class="mb-0"><span id="itemCount" class="badge text-bg-info">0</span></h2>
                    </div>
                </div>

                <!-- Scanned Items Table -->
                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-hover" id="scannedItemsTable">
                        <thead class="table-light">
                            <tr>
                                <th width="15%">Barcode</th>
                                <th>Product Name</th>
                                <th>Rejection Reason</th>
                                <th width="12%">Price</th>
                                <th width="8%">Action</th>
                            </tr>
                        </thead>
                        <tbody id="scannedItems">
                            <tr id="emptyRow">
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p>No items scanned yet. Select a vendor and start scanning barcodes.</p>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot id="totalRow" style="display:none;">
                            <tr class="table-secondary fw-bold">
                                <td colspan="3" class="text-end">Total Amount:</td>
                                <td>₹<span id="totalAmount">0.00</span></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Hidden inputs for item IDs -->
                <div id="hiddenInputs"></div>

                <!-- Submit Button -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= site_url('vendors/returns') ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" id="submitBtn" class="btn btn-danger" disabled>
                        <i class="fas fa-check"></i> Process Return Shipment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const vendorSelect = document.getElementById('vendorSelect');
    const barcodeInput = document.getElementById('barcodeInput');
    const addBtn = document.getElementById('addBtn');
    const scannedItems = document.getElementById('scannedItems');
    const emptyRow = document.getElementById('emptyRow');
    const itemCount = document.getElementById('itemCount');
    const totalAmount = document.getElementById('totalAmount');
    const totalRow = document.getElementById('totalRow');
    const submitBtn = document.getElementById('submitBtn');
    const hiddenInputs = document.getElementById('hiddenInputs');
    const returnAction = document.getElementById('returnAction');
    
    let items = [];
    let total = 0;
    
    // Focus barcode input when vendor is selected
    vendorSelect.addEventListener('change', function() {
        if (this.value) {
            barcodeInput.focus();
        }
    });
    
    // Handle Enter key in barcode input
    barcodeInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addItem();
        }
    });
    
    // Handle Add button click
    addBtn.addEventListener('click', addItem);
    
    function addItem() {
        const vendorId = vendorSelect.value;
        const barcode = barcodeInput.value.trim();
        
        if (!vendorId) {
            alert('Please select a vendor first.');
            vendorSelect.focus();
            return;
        }
        
        if (!barcode) {
            alert('Please scan or enter a barcode.');
            return;
        }
        
        // Check if already scanned
        if (items.find(item => item.barcode === barcode)) {
            alert('This item has already been scanned!');
            barcodeInput.value = '';
            barcodeInput.focus();
            return;
        }
        
        // AJAX call to validate and fetch item details
        fetch('<?= site_url('vendors/returns/scan') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
                'barcode': barcode,
                'vendor_id': vendorId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                items.push(data.item);
                addItemToTable(data.item);
                updateTotals();
                barcodeInput.value = '';
                barcodeInput.focus();
            } else {
                alert('Error: ' + data.message);
                barcodeInput.value = '';
                barcodeInput.focus();
            }
        })
        .catch(error => {
            alert('Network error. Please try again.');
            console.error(error);
        });
    }
    
    function addItemToTable(item) {
        emptyRow.style.display = 'none';
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><code>${item.barcode}</code></td>
            <td>${item.product_name}</td>
            <td><small class="text-danger">${item.rejection_reason || 'N/A'}</small></td>
            <td>₹${parseFloat(item.purchase_price).toFixed(2)}</td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem('${item.barcode}')">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        scannedItems.appendChild(row);
        
        // Add hidden input
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'item_ids[]';
        input.value = item.id;
        input.id = 'item_' + item.id;
        hiddenInputs.appendChild(input);
    }
    
    window.removeItem = function(barcode) {
        const index = items.findIndex(item => item.barcode === barcode);
        if (index > -1) {
            const item = items[index];
            items.splice(index, 1);
            
            // Remove row
            scannedItems.children[index].remove();
            
            // Remove hidden input
            document.getElementById('item_' + item.id).remove();
            
            updateTotals();
            
            if (items.length === 0) {
                emptyRow.style.display = '';
            }
        }
    };
    
    function updateTotals() {
        itemCount.textContent = items.length;
        total = items.reduce((sum, item) => sum + parseFloat(item.purchase_price), 0);
        totalAmount.textContent = total.toFixed(2);
        
        if (items.length > 0) {
            totalRow.style.display = '';
            submitBtn.disabled = false;
        } else {
            totalRow.style.display = 'none';
            submitBtn.disabled = true;
        }
    }
    
    // Update total display when action changes
    returnAction.addEventListener('change', function() {
        if (this.value === 'Exchanged') {
            totalRow.style.display = 'none';
        } else if (items.length > 0) {
            totalRow.style.display = '';
        }
    });
});
</script>
<?= $this->endSection() ?>
