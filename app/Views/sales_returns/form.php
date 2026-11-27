<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-undo-alt text-primary me-2"></i><?= esc($title) ?></h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('sales_returns') ?>" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Sales Returns
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary shadow-sm">
    <form action="<?= site_url('sales_returns/store') ?>" method="post" id="returnForm">
        <?= csrf_field() ?>
        <div class="card-body">
            <div class="row g-3">
                <!-- Left: Customer & Tax Info -->
                <div class="col-lg-5">
                    <div class="border rounded p-3 h-100 bg-light-subtle">
                        <div class="mb-3">
                            <label for="customer_id" class="form-label fw-semibold">Customer <span class="text-danger">*</span></label>
                            <select name="customer_id" id="customer_id" class="form-select select2" required>
                                <option value="">Select Customer</option>
                                <?php foreach ($customers as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= (!empty($preselected_customer_id) && (int) $preselected_customer_id === (int) $c['id']) ? 'selected' : '' ?>>
                                        <?= esc($c['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="small text-muted fw-semibold mb-1">Billing Address</label>
                                <div id="billingAddressDisplay" class="p-2 border rounded bg-white small"
                                    style="min-height: 62px; white-space: pre-wrap;">Select Customer</div>
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted fw-semibold mb-1">Shipping Address</label>
                                <div id="shippingAddressDisplay" class="p-2 border rounded bg-white small"
                                    style="min-height: 62px; white-space: pre-wrap;">Select Customer</div>
                            </div>
                        </div>

                        <div id="contactInfoSection" class="row g-2 mt-2" style="display: none;">
                            <div class="col-6">
                                <label class="text-muted small text-uppercase fw-bold mb-0 d-block"><i class="fas fa-phone text-primary me-1"></i> Phone</label>
                                <div id="customerPhone" class="small"></div>
                            </div>
                            <div class="col-6">
                                <label class="text-muted small text-uppercase fw-bold mb-0 d-block"><i class="fab fa-whatsapp text-success me-1"></i> WhatsApp</label>
                                <div id="customerWhatsapp" class="small"></div>
                            </div>
                            <div class="col-12 mt-1">
                                <label class="text-muted small text-uppercase fw-bold mb-0 d-block"><i class="fas fa-id-card text-info me-1"></i> GST Details</label>
                                <div class="small">
                                    <span id="customerGstType" class="badge bg-secondary me-1"></span>
                                    <span id="customerGstin" class="fw-bold"></span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-2">
                            <div id="taxModeIndicator" class="alert alert-info py-1 px-2 mb-0 small">
                                <i class="fas fa-info-circle me-1"></i> <span id="taxModeText">Select customer to see tax mode</span>
                            </div>
                            <input type="hidden" name="is_inter_state" id="is_inter_state" value="0">
                        </div>
                    </div>
                </div>

                <!-- Right: Return Metadata, Invoice Link & Quick Reason -->
                <div class="col-lg-7">
                    <div class="border rounded p-3 h-100">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="return_number" class="form-label fw-semibold">Return # (Credit Note)</label>
                                <input type="text" name="return_number" id="return_number" class="form-control fw-bold"
                                    value="<?= esc($return_number) ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="return_date" class="form-label fw-semibold">Return Date <span class="text-danger">*</span></label>
                                <input type="date" name="return_date" id="return_date" class="form-control"
                                    value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="invoice_id" class="form-label fw-semibold">
                                    Reference Invoice
                                    <span class="badge bg-primary-subtle text-primary ms-1">Auto-Fill</span>
                                </label>
                                <select name="invoice_id" id="invoice_id" class="form-select select2">
                                    <option value="">-- Optional: Select Invoice --</option>
                                </select>
                            </div>
                        </div>

                        <!-- Linked Invoice Summary -->
                        <div id="invoiceSummaryBox" class="alert alert-light border mt-3 mb-2 py-2 px-3 small" style="display: none;">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <div>
                                    <strong class="text-primary" id="invSumNumber"></strong>
                                    <span class="text-muted ms-2" id="invSumDate"></span>
                                    <span class="badge bg-secondary ms-2" id="invSumStatus"></span>
                                </div>
                                <div class="d-flex flex-wrap gap-3">
                                    <span id="invSumDiscountWrap" class="text-success" style="display: none;">Inv Discount: <strong id="invSumDiscount"></strong></span>
                                    <span>Total: <strong id="invSumTotal"></strong></span>
                                    <span>Paid: <strong id="invSumPaid"></strong></span>
                                    <span>Balance: <strong class="text-danger" id="invSumBalance"></strong></span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-1 gap-2">
                                <label for="reason" class="form-label fw-semibold mb-0">Reason for Return</label>
                                <div class="d-flex flex-wrap gap-1">
                                    <?php foreach (['Damaged Goods', 'Quality Issue', 'Wrong Item Sent', 'Excess Quantity', 'Color / Design Mismatch', 'Customer Cancelled'] as $preset): ?>
                                        <button type="button" class="btn btn-outline-secondary btn-xs py-0 px-2 small reason-chip" data-reason="<?= esc($preset) ?>">
                                            <?= esc($preset) ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <textarea name="reason" id="reason" class="form-control" rows="2" placeholder="Click a preset above or type return reason..."></textarea>
                        </div>

                        <!-- Live Summary Strip -->
                        <div class="row g-2 mt-2 pt-2 border-top text-center">
                            <div class="col-3">
                                <div class="small text-muted">Items</div>
                                <div class="fw-bold" id="sumItemCount">1</div>
                            </div>
                            <div class="col-3">
                                <div class="small text-muted">Total Qty</div>
                                <div class="fw-bold" id="sumTotalQty">1</div>
                            </div>
                            <div class="col-3">
                                <div class="small text-muted">Tax</div>
                                <div class="fw-bold" id="sumTaxBadge">₹0.00</div>
                            </div>
                            <div class="col-3">
                                <div class="small text-muted">Grand Total</div>
                                <div class="fw-bold text-primary" id="sumGrandBadge">₹0.00</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-3">

            <!-- Item Details Header + Barcode Scanner -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <h5 class="mb-0"><i class="fas fa-boxes text-primary me-1"></i> Item Details</h5>
                <div class="d-flex align-items-center gap-2">
                    <div class="input-group input-group-sm" style="width: 280px;">
                        <span class="input-group-text bg-dark text-white"><i class="fas fa-barcode"></i></span>
                        <input type="text" id="barcodeScanner" class="form-control" placeholder="Scan barcode & press Enter...">
                    </div>
                    <span id="barcodeStatusMsg" class="badge border" style="display: none;"></span>
                    <button type="button" class="btn btn-sm btn-success" id="addItem">
                        <i class="fas fa-plus me-1"></i> Add Item
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="itemsTable">
                    <thead class="table-light">
                        <tr>
                            <th width="4%" class="text-center">#</th>
                            <th width="40%">Product &amp; Description</th>
                            <th width="13%">Return Qty</th>
                            <th width="13%">Rate (₹)</th>
                            <th width="12%">Tax (%)</th>
                            <th width="14%">Line Total</th>
                            <th width="4%"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <tr class="item-row">
                            <td class="text-center fw-bold row-sno text-muted">1</td>
                            <td>
                                <select name="items[0][product_id]" class="form-select product-select select2">
                                    <option value="">-- Select Product --</option>
                                    <?php foreach ($products as $product): ?>
                                        <option value="<?= $product['id'] ?>" data-rate="<?= $product['selling_price'] ?>"
                                            data-gst="<?= $product['tax_percentage'] ?>">
                                            <?= esc($product['product_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="text" name="items[0][description]" class="form-control form-control-sm mt-1 product-description" placeholder="Item description...">
                                <small class="text-muted inv-qty-hint d-none"></small>
                            </td>
                            <td><input type="number" name="items[0][quantity]" class="form-control qty text-end" step="0.01" min="0.01" value="1" required></td>
                            <td><input type="number" name="items[0][rate]" class="form-control rate text-end" step="0.01" min="0" value="0" required></td>
                            <td><input type="number" name="items[0][tax_percentage]" class="form-control tax text-end" step="0.01" min="0" value="0"></td>
                            <td><input type="text" class="form-control total text-end fw-semibold" readonly value="0.00"></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-row" title="Remove Row">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="table-secondary">
                        <tr>
                            <td colspan="5" class="text-end fw-bold">Subtotal:</td>
                            <td><input type="text" id="subtotal" class="form-control fw-bold text-end" readonly value="0.00"></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-end align-middle">
                                <div class="d-inline-flex flex-wrap align-items-center justify-content-end gap-2">
                                    <div class="form-check form-check-inline mb-0">
                                        <input class="form-check-input" type="checkbox" name="include_discount" id="includeDiscountChk" value="1">
                                        <label class="form-check-label fw-semibold" for="includeDiscountChk">Include Discount</label>
                                    </div>
                                    <div class="input-group input-group-sm" style="width: 210px;" id="discountInputGroup">
                                        <input type="number" step="0.01" min="0" name="discount_amount" id="discountAmountInput" class="form-control text-end" value="0">
                                        <select name="discount_type" id="discountTypeSelect" class="form-select no-select2" style="max-width: 72px;">
                                            <option value="Fixed">₹</option>
                                            <option value="Percentage">%</option>
                                        </select>
                                    </div>
                                </div>
                            </td>
                            <td><input type="text" id="discountCalculatedDisplay" class="form-control text-end text-danger" readonly value="-0.00"></td>
                            <td></td>
                        </tr>
                        <tbody id="taxBreakdownBody"></tbody>
                        <tr>
                            <td colspan="5" class="text-end">Total Tax:</td>
                            <td><input type="text" id="taxAmount" class="form-control text-end" readonly value="0.00"></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-end align-middle">
                                <div class="d-inline-flex align-items-center justify-content-end gap-2">
                                    <span>Adjustment / Round Off:</span>
                                    <button type="button" class="btn btn-outline-secondary btn-xs py-0 px-2 small" id="btnAutoRound" title="Auto round Grand Total to nearest rupee">Round</button>
                                </div>
                            </td>
                            <td><input type="number" step="0.01" name="adjustment" id="adjustmentInput" class="form-control text-end" value="0.00"></td>
                            <td></td>
                        </tr>
                        <tr class="table-primary">
                            <td colspan="5" class="text-end fw-bold fs-5">Grand Total:</td>
                            <td><input type="text" id="totalAmount" class="form-control fw-bold fs-5 text-end" readonly value="0.00"></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-1"></i> <?= lang("App.save") ?> Sales Return</button>
            <a href="<?= site_url('sales_returns') ?>" class="btn btn-secondary"><?= lang("App.cancel") ?></a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    let rowIndex = 1;
    let isInterState = false;
    let _rowTemplateHtml = '';
    const preselectedInvoiceId = <?= (int) ($preselected_invoice_id ?? 0) ?>;

    function initRowSelect2($el) {
        $el.select2({ theme: 'bootstrap-5', width: '100%' });
    }

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
        let totalQty = 0;
        let activeRows = 0;
        const itemData = [];

        $('.item-row').each(function () {
            const $row = $(this);
            const qty = parseFloat($row.find('.qty').val()) || 0;
            const rate = parseFloat($row.find('.rate').val()) || 0;
            const amount = qty * rate;
            const taxPercentage = parseFloat($row.find('.tax').val()) || 0;

            if ($row.find('.product-select').val() || ($row.find('.product-description').val() || '').trim() !== '') {
                activeRows++;
            }
            totalQty += qty;
            grossSubtotal += amount;
            itemData.push({ amount, taxPercentage });
        });

        const includeDisc = $('#includeDiscountChk').is(':checked');
        const rawDisc = includeDisc ? Math.max(0, parseFloat($('#discountAmountInput').val()) || 0) : 0;
        const discType = $('#discountTypeSelect').val() || 'Fixed';
        const discountValue = (discType === 'Percentage')
            ? Math.round((grossSubtotal * rawDisc) / 100 * 100) / 100
            : Math.min(grossSubtotal, rawDisc);

        $('#discountAmountInput, #discountTypeSelect').prop('disabled', !includeDisc);
        $('#discountCalculatedDisplay').val('-' + discountValue.toFixed(2));

        const taxFactor = (grossSubtotal > 0 && discountValue > 0)
            ? Math.max(0, (grossSubtotal - discountValue) / grossSubtotal)
            : 1;

        let taxBreakdown = {};
        let totalTax = 0;

        itemData.forEach(item => {
            const taxAmount = ((item.amount * item.taxPercentage) / 100) * taxFactor;
            if (item.taxPercentage > 0) {
                taxBreakdown[item.taxPercentage] = (taxBreakdown[item.taxPercentage] || 0) + taxAmount;
                totalTax += taxAmount;
            }
        });

        const $taxBody = $('#taxBreakdownBody');
        $taxBody.empty();

        Object.keys(taxBreakdown).sort((a, b) => a - b).forEach(percentage => {
            const amount = taxBreakdown[percentage];
            const rate = parseFloat(percentage);

            if (isInterState) {
                $taxBody.append(`
                    <tr>
                        <td colspan="5" class="text-end">IGST (${rate}%):</td>
                        <td><input type="text" class="form-control text-end" readonly value="${amount.toFixed(2)}"></td>
                        <td></td>
                    </tr>
                `);
            } else {
                const halfAmount = amount / 2;
                const halfRate = rate / 2;
                $taxBody.append(`
                    <tr>
                        <td colspan="5" class="text-end">CGST (${halfRate}%):</td>
                        <td><input type="text" class="form-control text-end" readonly value="${halfAmount.toFixed(2)}"></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-end">SGST (${halfRate}%):</td>
                        <td><input type="text" class="form-control text-end" readonly value="${halfAmount.toFixed(2)}"></td>
                        <td></td>
                    </tr>
                `);
            }
        });

        const adjustment = parseFloat($('#adjustmentInput').val()) || 0;
        const netTotal = Math.max(0, grossSubtotal - discountValue) + totalTax + adjustment;
        $('#subtotal').val(grossSubtotal.toFixed(2));
        $('#taxAmount').val(totalTax.toFixed(2));
        $('#totalAmount').val(netTotal.toFixed(2));

        $('#sumItemCount').text(activeRows || $('.item-row').length);
        $('#sumTotalQty').text(Number.isInteger(totalQty) ? totalQty : totalQty.toFixed(2));
        $('#sumTaxBadge').text('₹' + totalTax.toFixed(2));
        $('#sumGrandBadge').text('₹' + netTotal.toFixed(2));
    }

    function updateRowSerialNumbers() {
        $('#itemsBody .item-row').each(function (idx) {
            $(this).find('.row-sno').text(idx + 1);
        });
    }

    function appendItemRow(prefill = null) {
        const $newRow = $(_rowTemplateHtml);
        $newRow.find('input, select').each(function () {
            const name = $(this).attr('name');
            if (name) {
                $(this).attr('name', name.replace('[0]', `[${rowIndex}]`));
            }
        });

        $('#itemsBody').append($newRow);
        const $select = $newRow.find('.product-select');
        initRowSelect2($select);

        if (prefill) {
            if (prefill.product_id) {
                $select.val(String(prefill.product_id)).trigger('change.select2');
            }
            $newRow.find('.product-description').val(prefill.description || '');
            $newRow.find('.qty').val(prefill.quantity ?? 1);
            $newRow.find('.rate').val(parseFloat(prefill.rate || 0).toFixed(2));
            $newRow.find('.tax').val(parseFloat(prefill.tax_percentage || 0));
            if (prefill.inv_qty) {
                $newRow.find('.inv-qty-hint')
                    .text('Invoice Qty: ' + prefill.inv_qty)
                    .removeClass('d-none');
            }
            calculateRow($newRow);
        }

        rowIndex++;
        updateRowSerialNumbers();
        return $newRow;
    }

    function loadCustomerInvoices(customerId, autoSelectInvId = 0) {
        const $invSelect = $('#invoice_id');
        $invSelect.html('<option value="">-- Optional: Select Invoice --</option>').trigger('change.select2');
        $('#invoiceSummaryBox').hide();

        if (!customerId) return;

        $.get('<?= site_url('sales_returns/customer-invoices/') ?>' + customerId, function (res) {
            if (res && res.status === 'success' && Array.isArray(res.invoices)) {
                res.invoices.forEach(inv => {
                    const dt = inv.invoice_date ? inv.invoice_date.split('-').reverse().join('/') : '';
                    const label = `${inv.invoice_number} (${dt} - ₹${parseFloat(inv.total_amount || 0).toFixed(2)})`;
                    const opt = new Option(label, inv.id, false, autoSelectInvId && Number(autoSelectInvId) === Number(inv.id));
                    $invSelect.append(opt);
                });
                $invSelect.trigger('change.select2');
                if (autoSelectInvId) {
                    $invSelect.trigger('change');
                }
            }
        });
    }

    function updateTaxMode(customerId, autoSelectInvId = 0) {
        if (!customerId) {
            isInterState = false;
            $('#is_inter_state').val(0);
            $('#billingAddressDisplay').text('Select Customer');
            $('#shippingAddressDisplay').text('Select Customer');
            $('#contactInfoSection').hide();
            $('#taxModeText').text('Select customer to see tax mode');
            $('#taxModeIndicator').removeClass('alert-success alert-warning alert-info').addClass('alert-info');
            loadCustomerInvoices(0);
            calculateTotals();
            return;
        }

        loadCustomerInvoices(customerId, autoSelectInvId);

        $.get('<?= site_url('customers/details/') ?>' + customerId, function (data) {
            if (data.status === 'success') {
                isInterState = data.is_inter_state;
                $('#is_inter_state').val(isInterState ? 1 : 0);

                $('#billingAddressDisplay').text(data.billing_address);
                $('#shippingAddressDisplay').text(data.shipping_address);

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

    function showBarcodeFeedback(msg, isError = false) {
        const $badge = $('#barcodeStatusMsg');
        $badge.text(msg)
            .removeClass('bg-success-subtle text-success border-success-subtle bg-danger-subtle text-danger border-danger-subtle')
            .addClass(isError
                ? 'bg-danger-subtle text-danger border-danger-subtle'
                : 'bg-success-subtle text-success border-success-subtle')
            .stop(true, true).fadeIn().delay(2500).fadeOut();
    }

    $(document).ready(function () {
        const $initialRow = $('.item-row').first();
        const $template = $initialRow.clone();
        $template.find('.select2-container').remove();
        $template.find('select').attr('class', 'form-select product-select select2');
        $template.find('input').val('');
        $template.find('.tax').val('0');
        $template.find('.qty').val('1');
        $template.find('.inv-qty-hint').text('').addClass('d-none');
        _rowTemplateHtml = $template[0].outerHTML;

        initRowSelect2($('.select2'));

        // Quick reason chips
        $('.reason-chip').on('click', function () {
            const val = $(this).data('reason');
            const $reason = $('#reason');
            const cur = ($reason.val() || '').trim();
            if (!cur) {
                $reason.val(val);
            } else if (!cur.includes(val)) {
                $reason.val(cur + '; ' + val);
            }
        });

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

        $('#addItem').on('click', function () {
            appendItemRow();
            calculateTotals();
        });

        $(document).on('click', '.remove-row', function () {
            if ($('.item-row').length > 1) {
                $(this).closest('tr').remove();
                updateRowSerialNumbers();
                calculateTotals();
            } else {
                const $row = $(this).closest('tr');
                $row.find('.product-select').val('').trigger('change.select2');
                $row.find('.product-description').val('');
                $row.find('.qty').val('1');
                $row.find('.rate').val('0');
                $row.find('.tax').val('0');
                $row.find('.inv-qty-hint').text('').addClass('d-none');
                calculateRow($row);
            }
        });

        $(document).on('input', '.qty, .rate, .tax', function () {
            calculateRow($(this).closest('tr'));
        });

        $('#includeDiscountChk, #discountTypeSelect').on('change', calculateTotals);
        $('#discountAmountInput, #adjustmentInput').on('input change', calculateTotals);

        $('#btnAutoRound').on('click', function () {
            const sub = parseFloat($('#subtotal').val()) || 0;
            const discText = parseFloat(($('#discountCalculatedDisplay').val() || '0').replace('-', '')) || 0;
            const tax = parseFloat($('#taxAmount').val()) || 0;
            const beforeAdj = Math.max(0, sub - discText) + tax;
            const rounded = Math.round(beforeAdj);
            $('#adjustmentInput').val((rounded - beforeAdj).toFixed(2));
            calculateTotals();
        });

        $('#customer_id').on('change', function () {
            updateTaxMode($(this).val());
        });

        // Invoice selection -> auto-fill items & summary
        $('#invoice_id').on('change', function () {
            const invId = $(this).val();
            if (!invId) {
                $('#invoiceSummaryBox').hide();
                return;
            }
            $.get('<?= site_url('sales_returns/invoice-items/') ?>' + invId, function (res) {
                if (!res || res.status !== 'success') return;
                const inv = res.invoice;
                const dt = inv.invoice_date ? inv.invoice_date.split('-').reverse().join('/') : '';
                $('#invSumNumber').text(inv.invoice_number);
                $('#invSumDate').text(dt);
                $('#invSumStatus').text(inv.status);
                $('#invSumTotal').text('₹' + parseFloat(inv.total_amount || 0).toFixed(2));
                $('#invSumPaid').text('₹' + parseFloat(inv.paid_amount || 0).toFixed(2));
                $('#invSumBalance').text('₹' + parseFloat(inv.balance || 0).toFixed(2));

                const invDisc = parseFloat(inv.discount_amount || 0);
                const invDiscType = inv.discount_type || 'Fixed';
                if (invDisc > 0) {
                    $('#invSumDiscount').text(invDiscType === 'Percentage' ? `${invDisc}%` : `₹${invDisc.toFixed(2)}`);
                    $('#invSumDiscountWrap').show();
                    $('#includeDiscountChk').prop('checked', true);
                    $('#discountAmountInput').val(invDisc);
                    $('#discountTypeSelect').val(invDiscType === 'Percentage' ? 'Percentage' : 'Fixed');
                } else {
                    $('#invSumDiscountWrap').hide();
                    $('#includeDiscountChk').prop('checked', false);
                    $('#discountAmountInput').val(0);
                }
                $('#adjustmentInput').val(parseFloat(inv.roundoff_amount || 0).toFixed(2));
                $('#invoiceSummaryBox').show();

                if (Array.isArray(res.items) && res.items.length > 0) {
                    $('#itemsBody').empty();
                    res.items.forEach(item => {
                        appendItemRow({
                            product_id: item.product_id,
                            description: item.description,
                            quantity: item.quantity,
                            inv_qty: item.quantity,
                            rate: item.rate,
                            tax_percentage: item.tax_percentage,
                        });
                    });
                    calculateTotals();
                }
            });
        });

        // Barcode scanner lookup
        $('#barcodeScanner').on('keypress', function (e) {
            if (e.which !== 13) return;
            e.preventDefault();
            const code = ($(this).val() || '').trim();
            if (!code) return;
            const $inp = $(this).prop('disabled', true);

            $.get('<?= site_url('products/barcode/') ?>' + encodeURIComponent(code), function (res) {
                $inp.prop('disabled', false).val('').focus();
                const prod = res && (res.product || res.data || (res.id ? res : null));
                if (!prod || !prod.id) {
                    showBarcodeFeedback('Product not found for barcode: ' + code, true);
                    return;
                }

                // Increment existing row if same product already present
                let matchedRow = null;
                $('.item-row').each(function () {
                    if (String($(this).find('.product-select').val()) === String(prod.id)) {
                        matchedRow = $(this);
                        return false;
                    }
                });

                if (matchedRow) {
                    const $qty = matchedRow.find('.qty');
                    $qty.val((parseFloat($qty.val()) || 0) + 1);
                    calculateRow(matchedRow);
                } else {
                    const $first = $('.item-row').first();
                    const firstEmpty = $('.item-row').length === 1 && !$first.find('.product-select').val() && !($first.find('.product-description').val() || '').trim();
                    if (firstEmpty) {
                        $first.find('.product-select').val(String(prod.id)).trigger('change.select2');
                        $first.find('.product-description').val(prod.product_name || '');
                        $first.find('.qty').val(1);
                        $first.find('.rate').val(parseFloat(prod.selling_price || 0).toFixed(2));
                        $first.find('.tax').val(parseFloat(prod.tax_percentage || 0));
                        calculateRow($first);
                    } else {
                        appendItemRow({
                            product_id: prod.id,
                            description: prod.product_name || '',
                            quantity: 1,
                            rate: prod.selling_price || 0,
                            tax_percentage: prod.tax_percentage || 0,
                        });
                    }
                }
                showBarcodeFeedback('Added: ' + (prod.product_name || code));
            }).fail(function () {
                $inp.prop('disabled', false).val('').focus();
                showBarcodeFeedback('Barcode not found: ' + code, true);
            });
        });

        $('#returnForm').on('submit', function () {
            const $btn = $(this).find('button[type="submit"]');
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');
        });

        if ($('#customer_id').val()) {
            updateTaxMode($('#customer_id').val(), preselectedInvoiceId);
        } else {
            calculateTotals();
        }
    });
</script>
<?= $this->endSection() ?>
