<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>App Settings<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Application Settings</h1>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-8">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">General Settings</h3>
            </div>
            <form action="<?= site_url('settings/update') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="app_name" class="form-label">Application Name</label>
                        <input type="text" class="form-control" id="app_name" name="settings[app_name]" value="<?= esc($settings['app_name'] ?? 'RasiDev HR') ?>" required>
                        <small class="text-muted">Displays in the sidebar and header.</small>
                    </div>

                    <div class="mb-3">
                        <label for="org_name" class="form-label">Organization Name</label>
                        <input type="text" class="form-control" id="org_name" name="settings[org_name]" value="<?= esc($settings['org_name'] ?? 'RasiDev Solutions') ?>" required>
                        <small class="text-muted">Used in payroll and payslips.</small>
                    </div>

                    <hr class="my-4">
                    <h5 class="text-primary mb-3">Organization Address & Contact Info</h5>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Address Line 1 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="settings[company_address_1]" value="<?= esc($settings['company_address_1'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Address Line 2</label>
                            <input type="text" class="form-control" name="settings[company_address_2]" value="<?= esc($settings['company_address_2'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" class="form-control" name="settings[company_city]" value="<?= esc($settings['company_city'] ?? '') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Country <span class="text-danger">*</span></label>
                            <select class="form-select select2" name="settings[company_country]" id="countrySelect" required>
                                <option value="">Select Country</option>
                                <?php foreach($countries as $country): ?>
                                    <option value="<?= $country['id'] ?>" <?= ($settings['company_country'] ?? '') == $country['id'] ? 'selected' : '' ?>>
                                        <?= esc($country['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">State <span class="text-danger">*</span></label>
                            <select class="form-select select2" name="settings[company_state]" id="stateSelect" required>
                                <option value="">Select State</option>
                                <!-- States loaded via JS -->
                            </select>
                            <input type="hidden" id="selectedStateId" value="<?= esc($settings['company_state'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Pincode</label>
                            <input type="text" class="form-control" name="settings[company_pincode]" value="<?= esc($settings['company_pincode'] ?? '') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">GSTIN</label>
                            <input type="text" class="form-control" name="settings[company_gstin]" value="<?= esc($settings['company_gstin'] ?? '') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">GST Type</label>
                            <select class="form-select" name="settings[company_gst_type]">
                                <option value="">Select GST Type</option>
                                <option value="Regular" <?= ($settings['company_gst_type'] ?? '') == 'Regular' ? 'selected' : '' ?>>Regular</option>
                                <option value="Composition" <?= ($settings['company_gst_type'] ?? '') == 'Composition' ? 'selected' : '' ?>>Composition</option>
                                <option value="Unregistered" <?= ($settings['company_gst_type'] ?? '') == 'Unregistered' ? 'selected' : '' ?>>Unregistered</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h5 class="text-primary mb-3">Contact Details</h5>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="settings[company_email]" value="<?= esc($settings['company_email'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" class="form-control" name="settings[company_phone]" value="<?= esc($settings['company_phone'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="settings[company_mobile]" value="<?= esc($settings['company_mobile'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">WhatsApp Number</label>
                            <input type="text" class="form-control" name="settings[company_whatsapp]" value="<?= esc($settings['company_whatsapp'] ?? '') ?>">
                        </div>
                    </div>

                    <hr class="my-4">
                    <h5 class="text-primary mb-3"><i class="fas fa-file-invoice"></i> Invoice Numbering Configuration</h5>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Numbering Format <span class="text-danger">*</span></label>
                            <select class="form-select" name="settings[invoice_number_format]" id="invoiceFormatSelect">
                                <option value="zoho" <?= ($settings['invoice_number_format'] ?? 'zoho') === 'zoho' ? 'selected' : '' ?>>Zoho Books Standard (Prefix + Padded Sequence, e.g. INV-000001)</option>
                                <option value="yearly_month" <?= ($settings['invoice_number_format'] ?? '') === 'yearly_month' ? 'selected' : '' ?>>Year & Month Prefix (e.g. INV-<?= date('Ym') ?>-0001)</option>
                            </select>
                            <small class="text-muted">Choose between continuous Zoho Books style numbering or monthly reset format.</small>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Invoice Prefix</label>
                            <input type="text" class="form-control" id="invoicePrefixInput" name="settings[invoice_prefix]" value="<?= esc($settings['invoice_prefix'] ?? 'INV-') ?>" placeholder="INV-">
                            <small class="text-muted">E.g. <code>INV-</code></small>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Digit Padding Length</label>
                            <input type="number" class="form-control" id="invoicePaddingInput" name="settings[invoice_padding_digits]" value="<?= esc($settings['invoice_padding_digits'] ?? '6') ?>" min="1" max="10">
                            <small class="text-muted">Default is 6 (e.g. 001939).</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Next Sequence Number (Optional Override)</label>
                            <input type="number" class="form-control" id="invoiceNextNumberInput" name="settings[invoice_next_number]" value="<?= esc($settings['invoice_next_number'] ?? '') ?>" placeholder="Leave blank for auto-increment">
                            <small class="text-muted">System will auto-continue from the highest billed invoice (currently <b><?= esc($nextInvoicePreview ?? '') ?></b>). Set only to force a jump to a higher sequence.</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Next Invoice Number Live Preview</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-tag"></i></span>
                                <input type="text" class="form-control fw-bold text-success bg-light" id="invoicePreviewBox" value="<?= esc($nextInvoicePreview ?? '') ?>" readonly>
                            </div>
                            <small class="text-muted">This preview dynamically reflects your settings.</small>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h5 class="text-primary mb-3"><i class="fas fa-print"></i> Document Print & Layout Configuration</h5>
                    <p class="text-muted small">Configure the header, footer, fonts, logo, and terms for all printed documents (Invoices, Purchase Bills, Sales Orders, Credit Notes, Debit Notes, Quotations).</p>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Header Company Name on Print</label>
                            <input type="text" class="form-control" name="settings[company_print_name]" value="<?= esc($settings['company_print_name'] ?? ($settings['org_name'] ?? ($settings['app_name'] ?? 'RASI HANDLOOM TEXTILES'))) ?>" placeholder="RASI HANDLOOM TEXTILES">
                            <small class="text-muted">Primary name displayed on top of printed bills and invoices.</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Company GSTIN on Print</label>
                            <input type="text" class="form-control" name="settings[company_print_gstin]" value="<?= esc($settings['company_print_gstin'] ?? ($settings['company_gstin'] ?? '33FOPPS7926B1ZK')) ?>" placeholder="33FOPPS7926B1ZK">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="fas fa-qrcode text-primary me-1"></i> UPI ID (for Invoice / Estimate Payment QR Code)</label>
                            <input type="text" class="form-control" name="settings[company_upi_id]" value="<?= esc($settings['company_upi_id'] ?? '') ?>" placeholder="e.g. rht@okicici or 9626077333@ybl">
                            <small class="text-muted">Used to auto-generate a scannable UPI QR code with invoice/estimate number and customer name.</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">UPI Payee Name (Optional)</label>
                            <input type="text" class="form-control" name="settings[company_upi_name]" value="<?= esc($settings['company_upi_name'] ?? '') ?>" placeholder="Defaults to Print Company Name">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Print Header From-Address</label>
                            <?php 
                            $defaultPrintAddr = $settings['company_print_address'] ?? '';
                            if (empty($defaultPrintAddr)) {
                                $lines = array_filter([
                                    $settings['company_address_1'] ?? '',
                                    $settings['company_address_2'] ?? '',
                                    trim(($settings['company_city'] ?? '') . ' ' . ($settings['company_pincode'] ?? ''))
                                ]);
                                $defaultPrintAddr = implode(",\n", $lines);
                            }
                            ?>
                            <textarea class="form-control" name="settings[company_print_address]" rows="3" placeholder="Street Address, Area, City - Pincode"><?= esc($defaultPrintAddr) ?></textarea>
                            <small class="text-muted">Multi-line address shown below company title in the header.</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="mb-2">
                                <label class="form-label">Print Contact Phones</label>
                                <input type="text" class="form-control" name="settings[company_print_phone]" value="<?= esc($settings['company_print_phone'] ?? ($settings['company_phone'] ?? ($settings['company_mobile'] ?? '09626077333'))) ?>" placeholder="Mobile / Phone">
                            </div>
                            <div>
                                <label class="form-label">Print Email</label>
                                <input type="email" class="form-control" name="settings[company_print_email]" value="<?= esc($settings['company_print_email'] ?? ($settings['company_email'] ?? 'sgrgowthamraj63@gmail.com')) ?>" placeholder="Email address">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Upload Company Logo (From Local File)</label>
                            <input type="file" class="form-control" name="company_logo_file" accept="image/*">
                            <small class="text-muted">Select an image file from your computer (PNG, JPG, SVG). It will be saved and used in the top-left monogram/logo circle.</small>
                            <?php if (!empty($settings['company_logo_url'])): ?>
                                <div class="mt-2 d-flex align-items-center gap-2">
                                    <span class="small text-success"><i class="fas fa-check-circle"></i> Current Logo:</span>
                                    <img src="<?= esc($settings['company_logo_url']) ?>" alt="Logo Preview" style="height: 38px; max-width: 100px; object-fit: contain; border: 1px solid #ddd; padding: 2px; border-radius: 4px;">
                                </div>
                            <?php endif; ?>
                            <input type="hidden" name="settings[company_logo_url]" value="<?= esc($settings['company_logo_url'] ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Print Font Family</label>
                            <select class="form-select" name="settings[print_header_font]">
                                <?php $currFont = $settings['print_header_font'] ?? "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif"; ?>
                                <option value="'Segoe UI', Tahoma, Geneva, Verdana, sans-serif" <?= $currFont === "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif" ? 'selected' : '' ?>>Segoe UI (Standard Clean Sans)</option>
                                <option value="'Helvetica Neue', Helvetica, Arial, sans-serif" <?= $currFont === "'Helvetica Neue', Helvetica, Arial, sans-serif" ? 'selected' : '' ?>>Helvetica / Arial</option>
                                <option value="'Calibri', Candara, Segoe, 'Segoe UI', Optima, Arial, sans-serif" <?= $currFont === "'Calibri', Candara, Segoe, 'Segoe UI', Optima, Arial, sans-serif" ? 'selected' : '' ?>>Calibri</option>
                                <option value="'Times New Roman', Times, serif" <?= $currFont === "'Times New Roman', Times, serif" ? 'selected' : '' ?>>Times New Roman (Serif)</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Base Font Size</label>
                            <select class="form-select" name="settings[print_font_size]">
                                <?php $currSize = $settings['print_font_size'] ?? "13px"; ?>
                                <option value="12px" <?= $currSize === '12px' ? 'selected' : '' ?>>Compact (12px)</option>
                                <option value="13px" <?= $currSize === '13px' ? 'selected' : '' ?>>Standard (13px)</option>
                                <option value="14px" <?= $currSize === '14px' ? 'selected' : '' ?>>Large (14px)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Bank Accounts to Print on Documents</label>
                            <div class="card p-3 bg-light border">
                                <small class="text-muted d-block mb-2">Check up to 2 active bank accounts to display in the bottom Bank Details box of printed invoices and bills:</small>
                                <?php 
                                $selectedBankIds = !empty($settings['print_selected_bank_ids']) ? json_decode($settings['print_selected_bank_ids'], true) : [];
                                if (!is_array($selectedBankIds)) $selectedBankIds = [];
                                ?>
                                <div class="row">
                                    <?php if (!empty($bankAccounts)): ?>
                                        <?php foreach ($bankAccounts as $bk): ?>
                                            <div class="col-md-6 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="print_bank_accounts[]" value="<?= $bk['id'] ?>" id="bank_chk_<?= $bk['id'] ?>" <?= (empty($selectedBankIds) || in_array($bk['id'], $selectedBankIds)) ? 'checked' : '' ?>>
                                                    <label class="form-check-label fw-bold" for="bank_chk_<?= $bk['id'] ?>">
                                                        <?= esc($bk['bank_name']) ?> - <?= esc($bk['account_number']) ?>
                                                        <span class="badge bg-secondary fw-normal ms-1"><?= esc($bk['branch_name']) ?></span>
                                                    </label>
                                                    <small class="d-block text-muted ms-3">IFSC: <?= esc($bk['ifsc_code']) ?></small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="col-12 text-muted">No active bank accounts found. Add accounts in Bank Accounts menu.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Terms & Conditions (Sole Footer Message)</label>
                            <textarea class="form-control" name="settings[print_default_terms]" rows="4"><?= esc($settings['print_default_terms'] ?? "1. All disputes will be resolved under Rasipuram jurisdiction.\n2. Please ensure payment is made before the due date to avoid an interest charge of 24% on the outstanding amount.\n3. We are not liable for any loss or damage occurring during transit.\n4. Goods once sold are non-returnable under any circumstances.") ?></textarea>
                            <small class="text-muted">Numbered or bulleted list of terms shown in the footer box. (General notes are omitted per your preference so Terms & Conditions remains the sole footer message).</small>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h5 class="text-primary mb-3"><i class="fas fa-truck me-2"></i>API Sathi E-Way Bill Configuration</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">API Sathi API Key (<code>X-API-Key</code>)</label>
                            <input type="text" class="form-control" name="settings[apisathi_api_key]" value="<?= esc($settings['apisathi_api_key'] ?? '') ?>" placeholder="test_... or live_...">
                            <small class="text-muted">Use your <code>test_</code> key for sandbox or <code>live_</code> key for production from apisathi.in.</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">E-Way Bill GSTIN</label>
                            <input type="text" class="form-control text-uppercase" name="settings[apisathi_ewb_gstin]" value="<?= esc($settings['apisathi_ewb_gstin'] ?? ($settings['company_print_gstin'] ?? ($settings['company_gstin'] ?? ''))) ?>" placeholder="33FOPPS7926B1ZK">
                            <small class="text-muted">Taxpayer GSTIN registered on ewaybillgst.gov.in.</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><?= lang("App.save") ?> Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Initialize Select2 Elements
    $('.select2').select2({
        theme: 'bootstrap-5'
    });

    const countrySelect = $('#countrySelect');
    const stateSelect = $('#stateSelect');
    const selectedStateId = $('#selectedStateId').val();

    function loadStates(countryId, selectedId = null) {
        if (!countryId) {
            stateSelect.html('<option value="">Select State</option>');
            return;
        }

        console.log('Loading states for country:', countryId);
        
        $.ajax({
            url: '<?= site_url('master-data/states') ?>/' + countryId,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                console.log('States loaded:', data);
                let options = '<option value="">Select State</option>';
                if (Array.isArray(data)) {
                    data.forEach(function(state) {
                        const isSelected = (selectedId && state.id == selectedId) ? 'selected' : '';
                        options += `<option value="${state.id}" ${isSelected}>${state.name}</option>`;
                    });
                }
                stateSelect.html(options);
                // Trigger change to update Select2 if needed, though html() updates underlying select
                // stateSelect.trigger('change'); 
            },
            error: function(xhr, status, error) {
                console.error('Error loading states:', error);
                console.error('Response:', xhr.responseText);
                alert('Failed to load states. Please try again.');
            }
        });
    }

    countrySelect.change(function() {
        loadStates($(this).val());
    });

    // Initial load if country is selected
    if (countrySelect.val()) {
        loadStates(countrySelect.val(), selectedStateId);
    }

    // Invoice Preview Live Updates
    function updateInvoicePreview() {
        const format = $('#invoiceFormatSelect').val();
        let prefix = $('#invoicePrefixInput').val() || 'INV-';
        let padding = parseInt($('#invoicePaddingInput').val(), 10) || 6;
        if (padding < 1) padding = 6;
        let overrideNum = parseInt($('#invoiceNextNumberInput').val(), 10);

        // Fallback sequence based on current max (1938)
        let seq = (!isNaN(overrideNum) && overrideNum > 0) ? overrideNum : 1939;

        if (format === 'yearly_month') {
            const now = new Date();
            const ym = now.getFullYear().toString() + String(now.getMonth() + 1).padStart(2, '0');
            const seqStr = String(seq).padStart(Math.max(4, padding), '0');
            $('#invoicePreviewBox').val(prefix + ym + '-' + seqStr);
        } else {
            const seqStr = String(seq).padStart(padding, '0');
            $('#invoicePreviewBox').val(prefix + seqStr);
        }
    }

    $('#invoiceFormatSelect, #invoicePrefixInput, #invoicePaddingInput, #invoiceNextNumberInput').on('input change', function() {
        updateInvoicePreview();
    });
});
</script>
<?= $this->endSection() ?>
