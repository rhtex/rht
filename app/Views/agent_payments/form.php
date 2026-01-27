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
                <li class="breadcrumb-item"><a href="<?= base_url('agent-payments') ?>">Agent Payments</a></li>
                <li class="breadcrumb-item active">Record Payment</li>
            </ol>
        </div>
    </div>

    <form action="<?= site_url('agent-payments/store') ?>" method="post" id="paymentForm">
        <?= csrf_field() ?>
        
        <!-- STEP 1: AGENT SELECTION -->
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">1. Select Agent</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Agent <span class="text-danger">*</span></label>
                            <select name="agent_id" id="agentSelect" class="form-select select2" required>
                                <option value="">-- Select Agent --</option>
                                <?php foreach ($agents as $agent): ?>
                                <option value="<?= $agent['id'] ?>" <?= ($selected_agent_id == $agent['id']) ? 'selected' : '' ?>>
                                    <?= esc($agent['agent_name']) ?> (<?= number_format($agent['commission_percentage'], 2) ?>%)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 2: PENDING COMMISSIONS -->
        <div class="card card-outline card-secondary" id="commissionsCard" style="display: none;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">2. Select Invoices for Payment</h3>
                <div class="card-tools ms-auto">
                    <button type="button" id="btnPreviewReport" class="btn btn-warning btn-sm" disabled>
                        <i class="fas fa-file-invoice"></i> Generate Waiting Payment Report
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>Invoice #</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th class="text-end">Subtotal</th>
                                <th class="text-end">Tax Amt</th>
                                <th class="text-end">Inv. Total</th>
                                <th class="text-end">Commission Amount</th>
                            </tr>
                        </thead>
                        <tbody id="commissionsBody">
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    Select an agent to view pending commissions
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- STEP 3: PAYMENT DETAILS -->
        <div class="card card-outline card-success shadow-sm" id="paymentDetailsCard" style="display: none;">
            <div class="card-header">
                <h3 class="card-title">3. Enter Payment Details</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Total Payment Amount</label>
                            <input type="number" step="0.01" name="amount" id="totalAmount" class="form-control fw-bold bg-light text-success fs-5" readonly required>
                            <small class="text-muted">Calculated from selected invoices</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Payment Mode <span class="text-danger">*</span></label>
                            <select name="payment_mode" id="paymentMode" class="form-select" required>
                                <option value="Cash">Cash</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Cheque">Cheque</option>
                                <option value="UPI">UPI</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Payment Number</label>
                            <input type="text" name="payment_number" class="form-control fw-bold" value="<?= $payment_number ?>" readonly>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6" id="bankAccountDiv" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">Bank Account</label>
                            <select name="bank_account_id" class="form-select">
                                <option value="">-- Select Bank --</option>
                                <?php foreach ($bank_accounts as $account): ?>
                                <option value="<?= $account['id'] ?>">
                                    <?= esc($account['bank_name']) ?> (<?= esc($account['account_number']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Reference Number / Transaction ID</label>
                            <input type="text" name="reference_number" class="form-control" placeholder="Cheque #, UPI Ref, etc.">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Any additional remarks..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end gap-2">
                <a href="<?= site_url('agent-payments') ?>" class="btn btn-secondary border"><i class="fas fa-times"></i> Cancel</a>
                <button type="submit" class="btn btn-success btn-lg px-4" id="btnSubmitPayment" disabled>
                    <i class="fas fa-check-circle"></i> Confirm & Record Payment
                </button>
            </div>
        </div>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });

    // Show/hide bank account based on payment mode
    $('#paymentMode').on('change', function() {
        const needsBank = ['Bank Transfer', 'Cheque', 'UPI'].includes(this.value);
        $('#bankAccountDiv').toggle(needsBank);
    });

    // Load pending commissions when agent is selected
    $('#agentSelect').on('change', function() {
        const agentId = this.value;
        if (!agentId) {
            $('#commissionsCard, #paymentDetailsCard').hide();
            $('#commissionsBody').html('<tr><td colspan="7" class="text-center text-muted py-4">Select an agent to view pending commissions</td></tr>');
            return;
        }

        // Fetch pending commissions
        fetch('<?= site_url('agent-payments/pending-commissions') ?>/' + agentId)
            .then(response => response.json())
            .then(data => {
                $('#commissionsCard').show();
                if (data.status === 'success' && data.data.length > 0) {
                    let html = '';
                    data.data.forEach(invoice => {
                        html += `
                            <tr>
                                <td>
                                    <input type="checkbox" name="invoice_ids[]" value="${invoice.id}" 
                                           class="form-check-input commission-checkbox" 
                                           data-amount="${invoice.agent_commission_amount}">
                                </td>
                                <td><strong>${invoice.invoice_number}</strong></td>
                                <td>${new Date(invoice.invoice_date).toLocaleDateString('en-GB')}</td>
                                <td>${invoice.customer_name}</td>
                                <td class="text-end">₹${parseFloat(invoice.subtotal).toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                                <td class="text-end">₹${parseFloat(invoice.tax_amount).toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                                <td class="text-end">₹${parseFloat(invoice.total_amount).toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                                <td class="text-end fw-bold">₹${parseFloat(invoice.agent_commission_amount).toLocaleString('en-IN', {minimumFractionDigits: 2})} <small class="text-muted d-block" style="font-size: 10px;">@ ${parseFloat(invoice.agent_commission_percent).toFixed(2)}%</small></td>
                            </tr>
                        `;
                    });
                    $('#commissionsBody').html(html);
                } else {
                    $('#commissionsBody').html('<tr><td colspan="7" class="text-center text-muted py-4">No pending commissions for this agent</td></tr>');
                    $('#paymentDetailsCard').hide();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to load pending commissions');
            });
    });

    // Select/Deselect all
    $('#selectAll').on('change', function() {
        $('.commission-checkbox').prop('checked', this.checked);
        updateButtonsAndTotals();
    });

    // Calculate total and toggle buttons when checkboxes change
    $(document).on('change', '.commission-checkbox', function() {
        updateButtonsAndTotals();
    });

    function updateButtonsAndTotals() {
        const selectedCheckboxes = $('.commission-checkbox:checked');
        const count = selectedCheckboxes.length;
        
        let total = 0;
        selectedCheckboxes.each(function() {
            total += parseFloat($(this).data('amount'));
        });

        $('#totalAmount').val(total.toFixed(2));

        // Toggle Payment Details Card
        if (count > 0) {
            $('#paymentDetailsCard').fadeIn();
            $('#btnPreviewReport').prop('disabled', false);
            $('#btnSubmitPayment').prop('disabled', false);
        } else {
            $('#paymentDetailsCard').fadeOut();
            $('#btnPreviewReport').prop('disabled', true);
            $('#btnSubmitPayment').prop('disabled', true);
        }
    }

    // Generate Waiting Payment Report (Preview)
    $('#btnPreviewReport').on('click', function() {
        const invoiceIds = $('.commission-checkbox:checked').map(function() {
            return this.value;
        }).get().join(',');
        
        if (invoiceIds) {
            const url = '<?= site_url('agent-payments/preview-report') ?>?ids=' + invoiceIds;
            window.open(url, '_blank');
        }
    });

    // Trigger change if agent is pre-selected
    <?php if ($selected_agent_id): ?>
        $('#agentSelect').trigger('change');
    <?php endif; ?>
});
</script>
<?= $this->endSection() ?>
