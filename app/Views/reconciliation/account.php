<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>
Reconcile - <?= esc($account['bank_name']) ?>
<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0 text-dark">Reconcile - <?= esc($account['bank_name']) ?></h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('reconciliation') ?>">Reconciliation</a></li>
            <li class="breadcrumb-item active"><?= esc($account['bank_name']) ?></li>
        </ol>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Date Filter -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-body py-3">
                <form method="get" class="row align-items-end g-2">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Start Date</label>
                        <input type="date" name="start_date" class="form-control form-control-sm" value="<?= $start_date ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">End Date</label>
                        <input type="date" name="end_date" class="form-control form-control-sm" value="<?= $end_date ?>">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary btn-sm px-4">
                            <i class="fas fa-filter me-1"></i> Filter Range
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs custom-tabs" id="reconcileTabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#credits" role="tab">
            <i class="fas fa-arrow-down text-success me-1"></i> Credits (Money In) 
            <span class="badge rounded-pill bg-warning text-dark ms-1"><?= count($unmatched_credits) ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#debits" role="tab">
            <i class="fas fa-arrow-up text-danger me-1"></i> Debits (Money Out) 
            <span class="badge rounded-pill bg-warning text-dark ms-1"><?= count($unmatched_debits) ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#matched" role="tab">
            <i class="fas fa-check-circle text-primary me-1"></i> Matched Items
            <span class="badge rounded-pill bg-success ms-1"><?= count($matched_transactions) ?></span>
        </a>
    </li>
</ul>

<div class="tab-content bg-white border border-top-0 p-4 rounded-bottom shadow-sm">
    <!-- CREDITS TAB -->
    <div id="credits" class="tab-pane fade show active" role="tabpanel">
        <div class="row">
            <div class="col-md-6 border-end">
                <h6 class="text-uppercase fw-bold text-info mb-3">Bank Credits (Unmatched)</h6>
                <div class="list-group list-group-flush scroll-container" style="max-height: 500px; overflow-y: auto;">
                    <?php if (empty($unmatched_credits)): ?>
                        <p class="text-muted p-4 text-center">No pending credit transactions.</p>
                    <?php else: ?>
                        <?php foreach ($unmatched_credits as $trans): ?>
                            <div class="list-group-item list-group-item-action transaction-item rounded mb-2 border mt-1" data-id="<?= $trans['id'] ?>" data-type="bank_credit">
                                <div class="d-flex justify-content-between">
                                    <span class="small fw-bold"><?= date('d-M-Y', strtotime($trans['transaction_date'])) ?></span>
                                    <span class="text-success fw-bold">₹<?= number_format($trans['amount'], 2) ?></span>
                                </div>
                                <div class="small text-truncate mt-1"><?= esc($trans['description']) ?></div>
                                <div class="small text-muted mt-1">Ref: <?= esc($trans['reference_number'] ?: 'N/A') ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6">
                <h6 class="text-uppercase fw-bold text-success mb-3 ms-md-3">Invoice Payments (Unmatched)</h6>
                <div class="list-group list-group-flush scroll-container ms-md-3" style="max-height: 500px; overflow-y: auto;">
                    <?php if (empty($unmatched_invoice_payments)): ?>
                        <p class="text-muted p-4 text-center">No pending invoice payments.</p>
                    <?php else: ?>
                        <?php foreach ($unmatched_invoice_payments as $payment): ?>
                            <div class="list-group-item list-group-item-action payment-item rounded mb-2 border mt-1" data-id="<?= $payment['id'] ?>" data-type="invoice_payment">
                                <div class="d-flex justify-content-between">
                                    <span class="small fw-bold"><?= date('d-M-Y', strtotime($payment['payment_date'])) ?></span>
                                    <span class="text-success fw-bold">₹<?= number_format($payment['amount'], 2) ?></span>
                                </div>
                                <div class="small mt-1 text-truncate">Invoice: <?= esc($payment['invoice_number']) ?></div>
                                <div class="small text-muted mt-1">Customer: <?= esc($payment['customer_name']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-12 text-center mt-4">
            <button id="match-credit-btn" class="btn btn-primary btn-lg px-5 shadow" disabled>
                <i class="fas fa-link me-2"></i> Match Records
            </button>
        </div>
    </div>

    <!-- DEBITS TAB -->
    <div id="debits" class="tab-pane fade" role="tabpanel">
        <div class="row">
            <div class="col-md-6 border-end">
                <h6 class="text-uppercase fw-bold text-info mb-3">Bank Debits (Unmatched)</h6>
                <div class="list-group list-group-flush scroll-container" style="max-height: 500px; overflow-y: auto;">
                    <?php if (empty($unmatched_debits)): ?>
                        <p class="text-muted p-4 text-center">No pending debit transactions.</p>
                    <?php else: ?>
                        <?php foreach ($unmatched_debits as $trans): ?>
                            <div class="list-group-item list-group-item-action transaction-item rounded mb-2 border mt-1" data-id="<?= $trans['id'] ?>" data-type="bank_debit">
                                <div class="d-flex justify-content-between">
                                    <span class="small fw-bold"><?= date('d-M-Y', strtotime($trans['transaction_date'])) ?></span>
                                    <span class="text-danger fw-bold">₹<?= number_format($trans['amount'], 2) ?></span>
                                </div>
                                <div class="small text-truncate mt-1"><?= esc($trans['description']) ?></div>
                                <div class="small text-muted mt-1">Ref: <?= esc($trans['reference_number'] ?: 'N/A') ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6">
                <h6 class="text-uppercase fw-bold text-danger mb-3 ms-md-3">Outgoing Payments (Unmatched)</h6>
                <div class="list-group list-group-flush scroll-container ms-md-3" style="max-height: 500px; overflow-y: auto;">
                    <?php if (empty($unmatched_outgoing_payments)): ?>
                        <p class="text-muted p-4 text-center">No pending outgoing payments.</p>
                    <?php else: ?>
                        <?php foreach ($unmatched_outgoing_payments as $payment): ?>
                            <div class="list-group-item list-group-item-action payment-item rounded mb-2 border mt-1" data-id="<?= $payment['id'] ?>" data-type="<?= $payment['type'] ?>">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-light text-dark border small fw-normal"><?= esc($payment['type_label']) ?></span>
                                    <span class="text-danger fw-bold">₹<?= number_format($payment['amount'], 2) ?></span>
                                </div>
                                <div class="small fw-bold mt-1"><?= date('d-M-Y', strtotime($payment['date'])) ?></div>
                                <div class="small text-muted mt-1 text-truncate"><?= esc($payment['description']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-12 text-center mt-4">
            <button id="match-debit-btn" class="btn btn-primary btn-lg px-5 shadow" disabled>
                <i class="fas fa-link me-2"></i> Match Records
            </button>
        </div>
    </div>

    <!-- MATCHED TAB -->
    <div id="matched" class="tab-pane fade" role="tabpanel">
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th class="text-end">Amount</th>
                        <th>Matched With</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($matched_transactions)): ?>
                        <tr><td colspan="6" class="text-center text-muted p-4">No matched records found for this period.</td></tr>
                    <?php else: ?>
                        <?php foreach ($matched_transactions as $trans): ?>
                            <tr>
                                <td class="align-middle"><?= date('d-M-Y', strtotime($trans['transaction_date'])) ?></td>
                                <td class="align-middle">
                                    <span class="badge bg-<?= $trans['type'] == 'credit' ? 'success' : 'danger' ?> px-2 rounded-pill">
                                        <?= ucfirst($trans['type']) ?>
                                    </span>
                                </td>
                                <td class="align-middle"><?= esc($trans['description']) ?></td>
                                <td class="align-middle text-end fw-bold">₹<?= number_format($trans['amount'], 2) ?></td>
                                <td class="align-middle">
                                    <span class="small text-muted"><?= esc($trans['reference_type']) ?></span>
                                    <span class="fw-bold fs-7">#<?= $trans['reference_id'] ?></span>
                                </td>
                                <td class="align-middle text-center">
                                    <button class="btn btn-sm btn-outline-warning unmatch-btn py-0 px-2" data-id="<?= $trans['id'] ?>" title="Unmatch Record">
                                        <i class="fas fa-unlink scale-8"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let selectedTransaction = null;
let selectedPayment = null;

// Handle transaction selection
document.querySelectorAll('.transaction-item').forEach(item => {
    item.addEventListener('click', function() {
        document.querySelectorAll('.transaction-item').forEach(i => i.classList.remove('bg-info', 'text-white', 'border-info'));
        this.classList.add('bg-info', 'text-white', 'border-info');
        selectedTransaction = this.dataset.id;
        updateMatchButton();
    });
});

// Handle payment selection
document.querySelectorAll('.payment-item').forEach(item => {
    item.addEventListener('click', function() {
        document.querySelectorAll('.payment-item').forEach(i => i.classList.remove('bg-info', 'text-white', 'border-info'));
        this.classList.add('bg-info', 'text-white', 'border-info');
        selectedPayment = {
            id: this.dataset.id,
            type: this.dataset.type
        };
        updateMatchButton();
    });
});

// Update match button state
function updateMatchButton() {
    const btns = [document.getElementById('match-credit-btn'), document.getElementById('match-debit-btn')];
    btns.forEach(btn => {
        if (btn) btn.disabled = !(selectedTransaction && selectedPayment);
    });
}

// Universal match function
function handleMatch() {
    if (!selectedTransaction || !selectedPayment) return;
    
    fetch('<?= base_url('reconciliation/match') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            'transaction_id': selectedTransaction,
            'payment_type': selectedPayment.type,
            'payment_id': selectedPayment.id,
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
}

const matchCreditBtn = document.getElementById('match-credit-btn');
if (matchCreditBtn) matchCreditBtn.addEventListener('click', handleMatch);

const matchDebitBtn = document.getElementById('match-debit-btn');
if (matchDebitBtn) matchDebitBtn.addEventListener('click', handleMatch);

// Unmatch transactions
document.querySelectorAll('.unmatch-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        if (!confirm('Unmatch this transaction and return to pending list?')) return;
        
        fetch('<?= base_url('reconciliation/unmatch') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                'transaction_id': this.dataset.id,
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    });
});
</script>
<style>
.transaction-item, .payment-item {
    cursor: pointer;
    transition: all 0.2s;
    border-left: 3px solid transparent !important;
}
.transaction-item:hover, .payment-item:hover {
    background-color: #f8f9fa;
    border-left: 3px solid #0dcaf0 !important;
}
.scroll-container::-webkit-scrollbar {
    width: 6px;
}
.scroll-container::-webkit-scrollbar-thumb {
    background-color: #ddd;
    border-radius: 10px;
}
.custom-tabs .nav-link {
    border-radius: 0;
    font-weight: 600;
    color: #6c757d;
    padding: 12px 20px;
}
.custom-tabs .nav-link.active {
    color: #0d6efd;
    border-top: 3px solid #0d6efd;
}
.scale-8 { transform: scale(0.8); }
.fs-7 { font-size: 0.85rem; }
</style>
<?= $this->endSection() ?>
