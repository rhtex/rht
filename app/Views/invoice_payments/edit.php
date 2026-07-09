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
                <li class="breadcrumb-item"><a href="<?= base_url('invoice_payments') ?>">Customer Receipts</a></li>
                <li class="breadcrumb-item active"><?= lang("App.edit") ?> Receipt</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <!-- Invoice Summary -->
        <div class="col-md-5">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">Associated Invoice Summary</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr><th width="40%">Invoice #:</th><td><?= esc($invoice['invoice_number']) ?></td></tr>
                        <tr><th>Customer:</th><td><?= esc($invoice['customer_name']) ?></td></tr>
                        <tr><th>Current Balance:</th><td class="fw-bold fs-5 text-primary">₹<?= number_format($invoice['balance'], 2) ?></td></tr>
                        <tr><td colspan="2"><small class="text-muted">Updating this receipt will automatically adjust the invoice balance.</small></td></tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="col-md-7">
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title">Modify Receipt Details</h3>
                </div>
                <form action="<?= site_url('invoice_payments/update/' . $payment['id']) ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Receipt Date <span class="text-danger">*</span></label>
                                    <input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d', strtotime($payment['payment_date'])) ?>" required>
                                </div>
                            </div>
                        </div>

                        <?php $currentSettlement = $payment['amount'] + ($payment['discount_amount'] ?? 0) + ($payment['mahimai_amount'] ?? 0) + ($payment['postal_charges'] ?? 0); ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Gross Settlement <span class="text-danger">*</span></label>
                                    <input type="number" name="gross_settlement" id="gross_settlement" class="form-control fw-bold" step="0.01" min="0.01" value="<?= $currentSettlement ?>" required oninput="calculateFromGross()">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-success fw-bold">Actual Cash Received</label>
                                    <input type="number" name="amount" id="net_amount" class="form-control fw-bold bg-light" step="0.01" value="<?= $payment['amount'] ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Discount</label>
                                    <div class="input-group">
                                        <input type="number" id="ded_discount_pct" class="form-control" placeholder="%" step="0.01" oninput="calculateFromPct('discount')">
                                        <input type="number" name="discount_amount" id="ded_discount" class="form-control" step="0.01" value="<?= $payment['discount_amount'] ?? 0 ?>" oninput="calculateNetByAmount()">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Mahimai</label>
                                    <div class="input-group">
                                        <input type="number" id="ded_mahimai_pct" class="form-control" placeholder="%" step="0.01" oninput="calculateFromPct('mahimai')">
                                        <input type="number" name="mahimai_amount" id="ded_mahimai" class="form-control" step="0.01" value="<?= $payment['mahimai_amount'] ?? 0 ?>" oninput="calculateNetByAmount()">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Postal</label>
                                    <input type="number" name="postal_charges" id="ded_postal" class="form-control" step="0.01" value="<?= $payment['postal_charges'] ?? 0 ?>" oninput="calculateNetByAmount()">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Mode <span class="text-danger">*</span></label>
                                    <select name="payment_mode" class="form-select" required>
                                        <option value="Cash" <?= $payment['payment_mode'] == 'Cash' ? 'selected' : '' ?>>Cash</option>
                                        <option value="Bank Transfer" <?= $payment['payment_mode'] == 'Bank Transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                                        <option value="Cheque" <?= $payment['payment_mode'] == 'Cheque' ? 'selected' : '' ?>>Cheque</option>
                                        <option value="UPI" <?= $payment['payment_mode'] == 'UPI' ? 'selected' : '' ?>>UPI</option>
                                        <option value="Other" <?= $payment['payment_mode'] == 'Other' ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Bank Account</label>
                                    <select name="bank_account_id" class="form-select">
                                        <option value="">-- Select Bank --</option>
                                        <?php foreach ($bank_accounts as $account): ?>
                                            <option value="<?= $account['id'] ?>" <?= $payment['bank_account_id'] == $account['id'] ? 'selected' : '' ?>>
                                                <?= esc($account['bank_name']) ?> (<?= esc($account['account_number']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Reference #</label>
                            <input type="text" name="reference_number" class="form-control" value="<?= esc($payment['reference_number']) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2"><?= esc($payment['notes']) ?></textarea>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-end gap-2">
                        <a href="<?= site_url('invoice_payments/view/' . $payment['id']) ?>" class="btn btn-secondary"><?= lang("App.cancel") ?></a>
                        <button type="submit" class="btn btn-warning">Update Receipt</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function calculateFromGross() {
    calculateFromPct('discount');
    calculateFromPct('mahimai');
    calculateNetByAmount();
}

function calculateFromPct(type) {
    const gross = parseFloat(document.getElementById('gross_settlement').value) || 0;
    const pct = parseFloat(document.getElementById('ded_' + type + '_pct').value) || 0;
    const amountField = document.getElementById('ded_' + type);
    
    if (pct > 0) {
        const amount = (gross * pct) / 100;
        amountField.value = amount.toFixed(2);
    }
    calculateNetByAmount();
}

function calculateNetByAmount() {
    const gross = parseFloat(document.getElementById('gross_settlement').value) || 0;
    const discount = parseFloat(document.getElementById('ded_discount').value) || 0;
    const mahimai = parseFloat(document.getElementById('ded_mahimai').value) || 0;
    const postal = parseFloat(document.getElementById('ded_postal').value) || 0;
    
    const net = gross - discount - mahimai - postal;
    document.getElementById('net_amount').value = net.toFixed(2);
}
</script>

<?= $this->endSection() ?>
