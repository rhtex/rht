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
                <li class="breadcrumb-item"><a href="<?= base_url('bills') ?>">Bills</a></li>
                <li class="breadcrumb-item active">Record Payment</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <!-- Bill Summary -->
        <div class="col-md-5">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">Bill Summary</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Bill Number:</th>
                            <td><?= esc($bill['bill_number']) ?></td>
                        </tr>
                        <tr>
                            <th>Vendor:</th>
                            <td><?= esc($bill['vendor_name']) ?></td>
                        </tr>
                        <tr>
                            <th>Bill Date:</th>
                            <td><?= date('d/m/Y', strtotime($bill['bill_date'])) ?></td>
                        </tr>
                        <tr>
                            <th>Due Date:</th>
                            <td><?= date('d/m/Y', strtotime($bill['due_date'])) ?></td>
                        </tr>
                        <tr>
                            <th>Total Amount:</th>
                            <td class="fw-bold">₹<?= number_format($bill['total_amount'], 2) ?></td>
                        </tr>
                        <tr>
                            <th>Paid Amount:</th>
                            <td class="text-success">₹<?= number_format($bill['paid_amount'], 2) ?></td>
                        </tr>
                        <tr class="table-warning">
                            <th>Balance Due:</th>
                            <td class="fw-bold fs-5">₹<?= number_format($bill['balance'], 2) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Payment Form -->
        <div class="col-md-7">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">Payment Details</h3>
                </div>
                <form action="<?= site_url('bills/payment/' . $bill['id']) ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                                    <input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                </div>
                            </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Gross Settlement Amount <span class="text-danger">*</span></label>
                                    <input type="number" name="gross_settlement" id="gross_settlement" class="form-control fw-bold" step="0.01" min="0.01" max="<?= $bill['balance'] ?>" value="<?= $bill['balance'] ?>" required oninput="calculateFromGross()">
                                    <small class="text-muted">Total amount to settle from bill balance</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-success fw-bold">Amount to Pay (Cash/Bank)</label>
                                    <input type="number" name="amount" id="net_amount" class="form-control fw-bold bg-light" step="0.01" value="<?= $bill['balance'] ?>" readonly>
                                    <small class="text-muted">Net payment after all deductions</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Post-sale Discount</label>
                                    <div class="input-group">
                                        <input type="number" id="ded_discount_pct" class="form-control" placeholder="%" step="0.01" oninput="calculateFromPct('discount')">
                                        <input type="number" name="discount_amount" id="ded_discount" class="form-control" step="0.01" value="0.00" oninput="calculateNetByAmount()">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Mahimai Amount</label>
                                    <div class="input-group">
                                        <input type="number" id="ded_mahimai_pct" class="form-control" placeholder="%" step="0.01" oninput="calculateFromPct('mahimai')">
                                        <input type="number" name="mahimai_amount" id="ded_mahimai" class="form-control" step="0.01" value="0.00" oninput="calculateNetByAmount()">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Postal Charges</label>
                                    <input type="number" name="postal_charges" id="ded_postal" class="form-control" step="0.01" value="0.00" oninput="calculateNetByAmount()">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Payment Mode <span class="text-danger">*</span></label>
                                    <select name="payment_mode" class="form-select" required>
                                        <option value="Bank Transfer" selected>Bank Transfer</option>
                                        <option value="Cash">Cash</option>
                                        <option value="Cheque">Cheque</option>
                                        <option value="UPI">UPI</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Bank Account</label>
                                    <select name="bank_account_id" class="form-select">
                                        <option value="">-- Select Bank Account --</option>
                                        <?php foreach ($bank_accounts as $account): ?>
                                            <option value="<?= $account['id'] ?>">
                                                <?= esc($account['bank_name']) ?> - <?= esc($account['account_number']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <script>
                        function calculateFromGross() {
                            // When gross changes, re-calculate deduction amounts if percentages exist, then update net
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
                        
                        // Initial calculation
                        window.onload = calculateNetByAmount;
                        </script>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Reference Number (Transaction ID)</label>
                                    <input type="text" name="reference_number" class="form-control" placeholder="Cheque number, transaction ID, etc.">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" class="form-control" rows="3" placeholder="Additional payment notes..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= site_url('bills/view/' . $bill['id']) ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check"></i> Record Payment
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
