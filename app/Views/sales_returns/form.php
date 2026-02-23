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
                            <?php foreach ($customers as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= esc($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <label class="small text-muted mb-0">Billing Address</label>
                                <div id="billingAddressDisplay" class="p-1 border rounded bg-light small"
                                    style="min-height: 60px; white-space: pre-wrap;">Select Customer</div>
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted mb-0">Shipping Address</label>
                                <div id="shippingAddressDisplay" class="p-1 border rounded bg-light small"
                                    style="min-height: 60px; white-space: pre-wrap;">Select Customer</div>
                            </div>
                        </div>
                        <div id="contactInfoSection" class="row mt-2" style="display: none;">
                            <div class="col-md-6">
                                <label class="text-muted small text-uppercase fw-bold mb-0 d-block"><i
                                        class="fas fa-phone text-primary me-1"></i> Phone</label>
                                <div id="customerPhone" class="small"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small text-uppercase fw-bold mb-0 d-block"><i
                                        class="fab fa-whatsapp text-success me-1"></i> WhatsApp</label>
                                <div id="customerWhatsapp" class="small"></div>
                            </div>
                            <div class="col-md-12 mt-2">
                                <label class="text-muted small text-uppercase fw-bold mb-0 d-block"><i
                                        class="fas fa-id-card text-info me-1"></i> GST Details</label>
                                <div class="small">
                                    <span id="customerGstType" class="badge bg-secondary me-1"></span>
                                    <span id="customerGstin" class="fw-bold"></span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <div id="taxModeIndicator" class="alert alert-info py-1 px-2 mb-0">
                                <i class="fas fa-info-circle"></i> <span id="taxModeText">Select customer to see tax
                                    mode</span>
                            </div>
                            <input type="hidden" name="is_inter_state" id="is_inter_state" value="0">
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="return_number">Return #</label>
                        <input type="text" name="return_number" id="return_number" class="form-control"
                            value="<?= $return_number ?>" readonly>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="return_date">Date <span class="text-danger">*</span></label>
                        <input type="date" name="return_date" id="return_date" class="form-control"
                            value="<?= date('Y-m-d') ?>" required>
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
                            <td>
                                <select name="items[0][product_id]" class="form-control product-select select2"
                                    required>
                                    <option value="">-- Select Product --</option>
                                    <?php foreach ($products as $product): ?>
                                        <option value="<?= $product['id'] ?>" data-rate="<?= $product['selling_price'] ?>"
                                            data-gst="<?= $product['tax_percentage'] ?>">
                                            <?= esc($product['product_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="items[0][description]" class="product-description">
                            </td>
                            <td><input type="number" name="items[0][quantity]" class="form-control qty" step="0.01"
                                    value="1" required></td>
                            <td><input type="number" name="items[0][rate]" class="form-control rate" step="0.01"
                                    value="0" required></td>
                            <td><input type="number" name="items[0][tax_percentage]" class="form-control tax"
                                    step="0.01" value="0"></td>
                            <td><input type="text" class="form-control total" readonly value="0.00"></td>
                            <td class="text-center">-</td>
                        </tr>
                    </tbody>
                    <tfoot class="table-secondary">
                        <tr>
                            <td colspan="4" class="text-end fw-bold">Subtotal:</td>
                            <td><input type="text" id="subtotal" class="form-control fw-bold" readonly value="0.00">
                            </td>
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
                            <td><input type="text" id="totalAmount" class="form-control fw-bold fs-5" readonly
                                    value="0.00"></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
                <button type="button" class="btn btn-sm btn-success" id="addItem"><i class="fas fa-plus"></i> Add
                    Item</button>
            </div>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary">Save Sales Return</button>
            <a href="<?= site_url('sales_returns') ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    let rowIndex = 1;
    let isInterState = false;

    // Global caches for clean HTML templates
    let _rowTemplateHtml = '';

    function calculateRow($row) {
        const qty = parseFloat($row.find('.qty').val()) || 0;
        const rate = parseFloat($row.find('.rate').val()) || 0;
        const tax = parseFloat($row.find('.tax').val()) || 0;

        const lineTotal = qty * rate;
        const taxAmount = (lineTotal * tax) / 100;
        $row.find('.total').val((lineTotal + taxAmount).toFixed(2));

        calculateTotals();
    }

    function calculateTotals() {
        let grossSubtotal = 0;
        const itemData = [];

        $('.item-row').each(function () {
            const $row = $(this);
            const qty = parseFloat($row.find('.qty').val()) || 0;
            const rate = parseFloat($row.find('.rate').val()) || 0;
            const amount = qty * rate;
            const taxPercentage = parseFloat($row.find('.tax').val()) || 0;

            grossSubtotal += amount;
            itemData.push({ amount, taxPercentage });
        });

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
        const $taxBody = $('#taxBreakdownBody');
        $taxBody.empty();

        Object.keys(taxBreakdown).sort((a, b) => a - b).forEach(percentage => {
            const amount = taxBreakdown[percentage];
            const rate = parseFloat(percentage);

            if (isInterState) {
                $taxBody.append(`
                    <tr>
                        <td colspan="4" class="text-end">IGST (${rate}%):</td>
                        <td><input type="text" class="form-control" readonly value="${amount.toFixed(2)}"></td>
                        <td></td>
                    </tr>
                `);
            } else {
                const halfAmount = amount / 2;
                const halfRate = rate / 2;
                $taxBody.append(`
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
                `);
            }
        });

        const netTotal = grossSubtotal + totalTax;
        $('#subtotal').val(grossSubtotal.toFixed(2));
        $('#taxAmount').val(totalTax.toFixed(2));
        $('#totalAmount').val(netTotal.toFixed(2));
    }

    function updateTaxMode(customerId) {
        if (!customerId) {
            isInterState = false;
            $('#is_inter_state').val(0);
            $('#billingAddressDisplay').text('Select Customer');
            $('#shippingAddressDisplay').text('Select Customer');
            $('#taxModeText').text('Select customer to see tax mode');
            $('#taxModeIndicator').removeClass('alert-success alert-warning alert-info').addClass('alert-info');
            calculateTotals();
            return;
        }

        $.get('<?= site_url('customers/details/') ?>' + customerId, function (data) {
            if (data.status === 'success') {
                isInterState = data.is_inter_state;
                $('#is_inter_state').val(isInterState ? 1 : 0);

                $('#billingAddressDisplay').text(data.billing_address);
                $('#shippingAddressDisplay').text(data.shipping_address);

                // Update contact info
                $('#contactInfoSection').show();
                $('#customerPhone').text(data.phone || 'N/A');
                $('#customerWhatsapp').text(data.whatsapp_number || 'N/A');
                $('#customerGstType').text(data.gst_type || 'Unregistered');
                $('#customerGstin').text(data.gstin || 'N/A');

                $('#taxModeIndicator').removeClass('alert-info alert-success alert-warning');
                if (isInterState) {
                    $('#taxModeIndicator').addClass('alert-warning');
                    $('#taxModeText').text('INTER-STATE TRANSACTION (IGST)');
                } else {
                    $('#taxModeIndicator').addClass('alert-success');
                    $('#taxModeText').text('INTRA-STATE TRANSACTION (CGST + SGST)');
                }
                calculateTotals();
            }
        });
    }

    $(document).ready(function () {
        // Capture row template (ensure it is clean for cloning)
        const $initialRow = $('.item-row').first();
        const $template = $initialRow.clone();
        $template.find('.select2-container').remove();
        $template.find('select').attr('class', 'form-control product-select select2');
        $template.find('input').val('');
        $template.find('.tax').val('0');
        $template.find('.qty').val('1');
        _rowTemplateHtml = $template[0].outerHTML;

        // Initialize Select2
        function initSelect2(element) {
            $(element || '.select2').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });
        }
        initSelect2();

        // Product selection logic
        $(document).on('change', '.product-select', function () {
            const $row = $(this).closest('tr');
            const $option = $(this).find(':selected');
            if ($option.val()) {
                $row.find('.rate').val($option.data('rate'));
                $row.find('.tax').val($option.data('gst'));
                $row.find('.product-description').val($option.text().trim());
            }
            calculateRow($row);
        });

        // Add item
        $('#addItem').on('click', function () {
            const $newRow = $(_rowTemplateHtml);

            // Fix input names
            $newRow.find('input, select').each(function () {
                const name = $(this).attr('name');
                if (name) {
                    $(this).attr('name', name.replace('[0]', `[${rowIndex}]`));
                }
            });

            // Add remove button
            $newRow.find('td:last').empty().append('<button type="button" class="btn btn-sm btn-danger remove-row"><i class="fas fa-trash"></i></button>');

            $('#itemsBody').append($newRow);
            initSelect2($newRow.find('.select2'));
            rowIndex++;
        });

        // Remove row (delegated)
        $(document).on('click', '.remove-row', function () {
            $(this).closest('tr').remove();
            calculateTotals();
        });

        // Calculate on input Change (delegated)
        $(document).on('input', '.qty, .rate, .tax', function () {
            calculateRow($(this).closest('tr'));
        });

        // Customer change -> Tax Mode
        $('#customer_id').on('change', function () {
            updateTaxMode($(this).val());
        });

        // Form submission - prevent double clicks
        $('#returnForm').on('submit', function () {
            const $btn = $(this).find('button[type="submit"]');
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        });

        // Initial calculation
        calculateTotals();
    });
</script>
<?= $this->endSection() ?>