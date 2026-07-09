<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= lang("App.view") ?> Loom Details<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1><i class="fas fa-layer-group text-primary me-2"></i> <?= lang("App.view") ?> Loom: <?= esc($loom['loom_number']) ?></h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('production/weavers/view/' . $loom['weaver_id']) ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> <?= lang("App.back") ?> to Weaver
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <!-- Left Column: Loom Details & Edit Form -->
    <div class="col-md-5">
        <div class="card card-outline card-primary shadow-sm h-100">
            <div class="card-header bg-light">
                <h3 class="card-title text-dark fw-bold"><i class="fas fa-info-circle me-1"></i> Loom Information</h3>
            </div>
            <div class="card-body">
                <?php if (session()->has('errors')) : ?>
                    <div class="alert alert-danger">
                        <ul>
                        <?php foreach (session('errors') as $error) : ?>
                            <li><?= $error ?></li>
                        <?php endforeach ?>
                        </ul>
                    </div>
                <?php endif ?>
                
                <form action="<?= site_url('production/looms/update/' . $loom['id']) ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Loom Number/Name <span class="text-danger">*</span></label>
                        <input type="text" name="loom_number" class="form-control" value="<?= esc($loom['loom_number']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Contract Type <span class="text-danger">*</span></label>
                        <select name="contract_type" class="form-control" required>
                            <option value="Job Work" <?= $loom['contract_type'] == 'Job Work' ? 'selected' : '' ?>>Job Work</option>
                            <option value="Sale & Buy Back" <?= $loom['contract_type'] == 'Sale & Buy Back' ? 'selected' : '' ?>>Sale & Buy Back</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-control" required>
                            <option value="Active" <?= $loom['status'] == 'Active' ? 'selected' : '' ?>>Active</option>
                            <option value="Inactive" <?= $loom['status'] == 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    
                    <h5 class="mt-4 mb-3 fw-bold text-primary border-bottom pb-2">Ownership Details</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold">Loom Owner</label>
                            <select name="loom_owner" class="form-control form-control-sm" onchange="document.getElementById('loom_cost_div').style.display = (this.value == 'Company') ? 'block' : 'none'">
                                <option value="Weaver" <?= $loom['loom_owner'] == 'Weaver' ? 'selected' : '' ?>>Weaver</option>
                                <option value="Company" <?= $loom['loom_owner'] == 'Company' ? 'selected' : '' ?>>Company</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3" id="loom_cost_div" style="display: <?= $loom['loom_owner'] == 'Company' ? 'block' : 'none' ?>;">
                            <label class="form-label text-muted small fw-bold">Loom Cost</label>
                            <input type="number" step="0.01" name="loom_cost" class="form-control form-control-sm" value="<?= esc($loom['loom_cost']) ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold">Jacquard Owner</label>
                            <select name="jacquard_owner" class="form-control form-control-sm" onchange="document.getElementById('jacquard_cost_div').style.display = (this.value == 'Company') ? 'block' : 'none'">
                                <option value="Weaver" <?= $loom['jacquard_owner'] == 'Weaver' ? 'selected' : '' ?>>Weaver</option>
                                <option value="Company" <?= $loom['jacquard_owner'] == 'Company' ? 'selected' : '' ?>>Company</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3" id="jacquard_cost_div" style="display: <?= $loom['jacquard_owner'] == 'Company' ? 'block' : 'none' ?>;">
                            <label class="form-label text-muted small fw-bold">Jacquard Cost</label>
                            <input type="number" step="0.01" name="jacquard_cost" class="form-control form-control-sm" value="<?= esc($loom['jacquard_cost']) ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small fw-bold">Fitted By</label>
                            <select name="fitted_by" class="form-control form-control-sm" onchange="document.getElementById('fitting_cost_div').style.display = (this.value == 'Company') ? 'block' : 'none'">
                                <option value="Weaver" <?= $loom['fitted_by'] == 'Weaver' ? 'selected' : '' ?>>Weaver</option>
                                <option value="Company" <?= $loom['fitted_by'] == 'Company' ? 'selected' : '' ?>>Company</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3" id="fitting_cost_div" style="display: <?= $loom['fitted_by'] == 'Company' ? 'block' : 'none' ?>;">
                            <label class="form-label text-muted small fw-bold">Fitting Cost</label>
                            <input type="number" step="0.01" name="fitting_cost" class="form-control form-control-sm" value="<?= esc($loom['fitting_cost']) ?>">
                        </div>
                    </div>

                    <?php if(in_array('weaver.edit', session('permissions') ?? [])): ?>
                    <div class="d-grid mt-3">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update Loom</button>
                    </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Weaving History -->
    <div class="col-md-7">
        <div class="card card-outline card-info shadow-sm h-100">
            <div class="card-header bg-light">
                <h3 class="card-title text-dark fw-bold"><i class="fas fa-history me-1"></i> History of Weaving</h3>
            </div>
            <div class="card-body">
                <?php if (empty($allocations)): ?>
                    <div class="text-center p-5 text-muted">
                        <i class="fas fa-box-open fa-3x mb-3 text-light"></i>
                        <h5>No weaving history found for this loom.</h5>
                        <p>Allocations assigned to this loom will appear here.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle" id="historyTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Allocation #</th>
                                    <th>Date</th>
                                    <th>Beam ID</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allocations as $alloc): ?>
                                <tr>
                                    <td><strong><?= esc($alloc['allocation_number']) ?></strong></td>
                                    <td><?= date('d M Y', strtotime($alloc['allocation_date'])) ?></td>
                                    <td><span class="badge bg-secondary"><?= esc($alloc['warp_beam_id']) ?></span></td>
                                    <td>
                                        <?php
                                            $badgeClass = 'secondary';
                                            if ($alloc['status'] == 'Allocated') $badgeClass = 'primary';
                                            if ($alloc['status'] == 'In Progress') $badgeClass = 'warning';
                                            if ($alloc['status'] == 'Completed') $badgeClass = 'success';
                                            if ($alloc['status'] == 'Cancelled') $badgeClass = 'danger';
                                        ?>
                                        <span class="badge bg-<?= $badgeClass ?>"><?= esc($alloc['status']) ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Loom Ledgers Section -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card card-outline card-success shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h3 class="card-title text-dark fw-bold mb-0"><i class="fas fa-file-invoice-dollar me-1"></i> Loom Financial Ledgers</h3>
                <?php if (in_array('weaver.edit', session('permissions') ?? [])): ?>
                    <button type="button" class="btn btn-sm btn-success ms-auto" data-bs-toggle="modal" data-bs-target="#newLedgerModal">
                        <i class="fas fa-plus me-1"></i> New Ledger
                    </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($ledgers)): ?>
                    <div class="text-center p-4 text-muted">
                        <i class="fas fa-wallet fa-3x mb-3 text-light"></i>
                        <h5>No active ledgers for this loom.</h5>
                        <p>If the company paid for this loom or advanced maintenance cost, you can track it here.</p>
                    </div>
                <?php else: ?>
                    <div class="accordion" id="ledgerAccordion">
                        <?php foreach ($ledgers as $index => $ledger): ?>
                            <div class="accordion-item mb-3 border">
                                <h2 class="accordion-header" id="heading<?= $ledger['id'] ?>">
                                    <button class="accordion-button <?= $index === 0 ? '' : 'collapsed' ?> bg-light fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $ledger['id'] ?>" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="collapse<?= $ledger['id'] ?>">
                                        <div class="d-flex justify-content-between w-100 pe-3">
                                            <span><?= esc($ledger['title']) ?> (ID: <?= $ledger['id'] ?>)</span>
                                            <span>
                                                Balance: <span class="text-danger fw-black">₹<?= number_format($ledger['balance_amount'], 2) ?></span>
                                                <span class="badge bg-<?= $ledger['status'] == 'Active' ? 'warning' : 'success' ?> ms-2"><?= esc($ledger['status']) ?></span>
                                            </span>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse<?= $ledger['id'] ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" aria-labelledby="heading<?= $ledger['id'] ?>" data-bs-parent="#ledgerAccordion">
                                    <div class="accordion-body">
                                        <div class="row mb-3">
                                            <div class="col-md-3"><strong>Principal:</strong> ₹<?= number_format($ledger['principal_amount'], 2) ?></div>
                                            <div class="col-md-3"><strong>Interest Rate:</strong> <?= number_format($ledger['interest_rate'], 2) ?>%</div>
                                            <div class="col-md-3"><strong>Total Due (w/ Int):</strong> ₹<?= number_format($ledger['total_amount_due'], 2) ?></div>
                                            <div class="col-md-4 text-end">
                                                <?php if ($ledger['status'] == 'Active' && in_array('weaver.edit', session('permissions') ?? [])): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="openEditLedgerModal(<?= htmlspecialchars(json_encode($ledger)) ?>)">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-primary" onclick="openPaymentModal(<?= $ledger['id'] ?>, <?= $ledger['balance_amount'] ?>)">
                                                        <i class="fas fa-hand-holding-usd"></i> Add Txn
                                                    </button>
                                                    <form action="<?= site_url('production/loom-ledgers/delete/' . $ledger['id']) ?>" method="post" class="d-inline ms-1" onsubmit="return confirm('Are you sure you want to delete this ledger entirely?');">
                                                        <?= csrf_field() ?>
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <h6 class="fw-bold mt-4 border-bottom pb-2">Transactions</h6>
                                        <?php if (empty($transactions[$ledger['id']])): ?>
                                            <p class="text-muted small">No transactions recorded yet.</p>
                                        <?php else: ?>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Date</th>
                                                            <th>Type</th>
                                                            <th>Method</th>
                                                            <th>Remarks</th>
                                                            <th class="text-end">Amount</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($transactions[$ledger['id']] as $txn): ?>
                                                            <tr>
                                                                <td><?= date('d M Y', strtotime($txn['transaction_date'])) ?></td>
                                                                <td>
                                                                    <?php if ($txn['transaction_type'] == 'Principal'): ?>
                                                                        <span class="badge bg-info text-dark">Principal</span>
                                                                    <?php elseif ($txn['transaction_type'] == 'Interest Addition'): ?>
                                                                        <span class="badge bg-danger">Interest</span>
                                                                    <?php elseif ($txn['transaction_type'] == 'Payment'): ?>
                                                                        <span class="badge bg-success">Payment</span>
                                                                    <?php elseif ($txn['transaction_type'] == 'Waiveoff'): ?>
                                                                        <span class="badge bg-warning text-dark">Waiveoff</span>
                                                                    <?php elseif ($txn['transaction_type'] == 'Reversal'): ?>
                                                                        <span class="badge bg-secondary">Reversal</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <?= esc($txn['payment_method']) ?>
                                                                    <?php if (!empty($txn['reference_number'])): ?>
                                                                        <br><small class="text-muted">Ref: <?= esc($txn['reference_number']) ?></small>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td><?= esc($txn['remarks']) ?></td>
                                                                <td class="text-end fw-bold">₹<?= number_format($txn['amount'], 2) ?></td>
                                                                <td>
                                                                    <?php if (($txn['transaction_type'] == 'Payment' || $txn['transaction_type'] == 'Waiveoff' || $txn['transaction_type'] == 'Interest Addition') && in_array('weaver.edit', session('permissions') ?? [])): ?>
                                                                    <form action="<?= site_url('production/loom-ledgers/reverse/' . $txn['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Reverse this transaction? This will update the balance.');">
                                                                        <?= csrf_field() ?>
                                                                        <button type="submit" class="btn btn-xs btn-outline-danger" title="Reverse Transaction"><i class="fas fa-undo"></i></button>
                                                                    </form>
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modals for Ledgers -->
<?php if (in_array('weaver.edit', session('permissions') ?? [])): ?>
<!-- New Ledger Modal -->
<div class="modal fade" id="newLedgerModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= site_url('production/loom-ledgers/store') ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="loom_id" value="<?= $loom['id'] ?>">
            <input type="hidden" name="weaver_id" value="<?= $loom['weaver_id'] ?>">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Initialize New Loom Ledger</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Ledger Title (e.g. Loom Purchase)</label>
                        <input type="text" name="title" class="form-control" value="Loom Purchase Advance" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Principal Amount (₹)</label>
                        <input type="number" step="0.01" name="principal_amount" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Interest Rate (%)</label>
                        <input type="number" step="0.01" name="interest_rate" class="form-control" value="0.00" required>
                        <small class="text-muted">Enter 0 if interest-free.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Create Ledger</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Transaction Modal -->
<div class="modal fade" id="transactionModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="transactionForm" method="post">
            <?= csrf_field() ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Record Ledger Transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Transaction Type</label>
                        <select name="transaction_type" class="form-control" required>
                            <option value="Payment">Repayment / Deduction (Decreases Balance)</option>
                            <option value="Interest Addition">Interest Addition (Increases Balance)</option>
                            <option value="Waiveoff">Waiveoff (Decreases Balance)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Amount (₹)</label>
                        <input type="number" step="0.01" name="amount" id="txnAmount" class="form-control" required>
                        <small class="text-muted" id="txnBalanceInfo"></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Method</label>
                        <select name="payment_method" class="form-control" onchange="document.getElementById('lTxnRefDiv').style.display = (this.value == 'Cash' || this.value == 'Auto Deduction') ? 'none' : 'block'">
                            <option value="Cash">Cash</option>
                            <option value="Cheque">Cheque</option>
                            <option value="NEFT">NEFT</option>
                            <option value="RTGS">RTGS</option>
                            <option value="UPI">UPI</option>
                            <option value="Auto Deduction">Auto Deduction</option>
                        </select>
                    </div>
                    <div class="mb-3" id="lTxnRefDiv" style="display: none;">
                        <label class="form-label fw-bold">Reference Number (Optional)</label>
                        <input type="text" name="reference_number" class="form-control" placeholder="Cheque No / UTR / Txn ID">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Date</label>
                        <input type="date" name="transaction_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Transaction</button>
                </div>
            </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Ledger Modal -->
<div class="modal fade" id="editLedgerModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="editLedgerForm" method="post">
            <?= csrf_field() ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Loom Ledger</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Ledger Title</label>
                        <input type="text" name="title" id="editLedgerTitle" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Principal Amount (₹)</label>
                        <input type="number" step="0.01" name="principal_amount" id="editLedgerPrincipal" class="form-control" required>
                        <small class="text-muted">Warning: Changing this will adjust the balance automatically.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Interest Rate (%)</label>
                        <input type="number" step="0.01" name="interest_rate" id="editLedgerInterest" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        if ($('#historyTable').length) {
            $('#historyTable').DataTable({
                "order": [[ 1, "desc" ]],
                "pageLength": 10
            });
        }
    });

    function openPaymentModal(ledgerId, currentBalance) {
        document.getElementById('transactionForm').action = '<?= site_url('production/loom-ledgers/payment/') ?>' + ledgerId;
        document.getElementById('txnBalanceInfo').innerText = 'Current Balance: ₹' + currentBalance.toFixed(2);
        var myModal = new bootstrap.Modal(document.getElementById('transactionModal'));
        myModal.show();
    }

    function openEditLedgerModal(ledger) {
        document.getElementById('editLedgerForm').action = '<?= site_url('production/loom-ledgers/update/') ?>' + ledger.id;
        document.getElementById('editLedgerTitle').value = ledger.title;
        document.getElementById('editLedgerPrincipal').value = ledger.principal_amount;
        document.getElementById('editLedgerInterest').value = ledger.interest_rate;
        var myModal = new bootstrap.Modal(document.getElementById('editLedgerModal'));
        myModal.show();
    }
</script>
<?= $this->endSection() ?>
