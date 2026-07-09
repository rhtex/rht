<?= $this->extend('layouts/master') ?>

<?= $this->section('content') ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><?= esc($title) ?></h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="<?= site_url('dashboard') ?>"><?= lang("App.home") ?></a></li>
                    <li class="breadcrumb-item"><a href="<?= site_url('production/weavers') ?>">Weavers</a></li>
                    <li class="breadcrumb-item active"><?= esc($title) ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        
        <?php if (session()->getFlashdata('success')) : ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-circle mr-1"></i> Weaver Profile
                </h3>
                <div class="card-tools">
                    <a href="<?= site_url('production/weavers/edit/' . $weaver['id']) ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit"></i> <?= lang("App.edit") ?> Profile
                    </a>
                </div>
            </div>
            <div class="card-body row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th width="35%">Weaver Name</th>
                            <td><strong><?= esc($weaver['name']) ?></strong></td>
                        </tr>
                        <tr>
                            <th>Weaver Code</th>
                            <td><?= esc($weaver['code'] ?: '-') ?></td>
                        </tr>
                        <tr>
                            <th>Phone Number</th>
                            <td><?= esc($weaver['phone'] ?: '-') ?></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge bg-<?= $weaver['status'] === 'active' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($weaver['status']) ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th width="35%">Location</th>
                            <td>
                                <?php if (!empty($weaver['location'])) : ?>
                                    <a href="<?= esc($weaver['location']) ?>" target="_blank" class="btn btn-sm btn-primary">
                                        <i class="fas fa-map-marker-alt"></i> Open Location Link
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Address Proof</th>
                            <td>
                                <?php if (!empty($weaver['address_proof'])) : ?>
                                    <a href="<?= base_url($weaver['address_proof']) ?>" target="_blank" class="btn btn-sm btn-info">
                                        <i class="fas fa-file-alt"></i> View Address Proof
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">No proof uploaded</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Registered On</th>
                            <td><?= esc($weaver['created_at']) ?></td>
                        </tr>
                        <tr>
                            <th>Last Updated</th>
                            <td><?= esc($weaver['updated_at']) ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-12 mt-4">
                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs" id="weaverFinancialTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold" id="looms-tab" data-bs-toggle="tab" data-bs-target="#looms" type="button" role="tab" aria-controls="looms" aria-selected="true">
                                <i class="fas fa-industry text-primary me-1"></i> Assigned Looms
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold" id="personal-ledgers-tab" data-bs-toggle="tab" data-bs-target="#personal-ledgers" type="button" role="tab" aria-controls="personal-ledgers" aria-selected="false">
                                <i class="fas fa-wallet text-success me-1"></i> Personal Ledgers
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold" id="consolidated-tab" data-bs-toggle="tab" data-bs-target="#consolidated" type="button" role="tab" aria-controls="consolidated" aria-selected="false">
                                <i class="fas fa-list-alt text-info me-1"></i> Consolidated Payments
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold" id="settlements-tab" data-bs-toggle="tab" data-bs-target="#settlements" type="button" role="tab" aria-controls="settlements" aria-selected="false">
                                <i class="fas fa-hand-holding-usd text-warning me-1"></i> Wage Settlements
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content border border-top-0 p-3 bg-white" id="weaverFinancialTabsContent">
                        
                        <!-- TAB: Assigned Looms -->
                        <div class="tab-pane fade show active" id="looms" role="tabpanel" aria-labelledby="looms-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0 fw-bold">Looms</h5>
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addLoomModal">
                                    <i class="fas fa-plus"></i> Add Loom
                                </button>
                            </div>
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Loom Number/Name</th>
                                        <th>Contract Type</th>
                                        <th>Ownership Details</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($looms)): ?>
                                        <tr><td colspan="5" class="text-center text-muted py-3">No looms assigned to this weaver.</td></tr>
                                    <?php else: foreach ($looms as $loom): ?>
                                        <tr>
                                            <td><?= esc($loom['loom_number']) ?></td>
                                            <td>
                                                <span class="badge bg-info"><?= esc($loom['contract_type']) ?></span>
                                            </td>
                                            <td>
                                                <small>
                                                    <b>Loom:</b> <?= esc($loom['loom_owner']) ?> <?= $loom['loom_owner'] == 'Company' ? '('.esc($loom['loom_cost']).')' : '' ?><br>
                                                    <b>Jacquard:</b> <?= esc($loom['jacquard_owner']) ?> <?= $loom['jacquard_owner'] == 'Company' ? '('.esc($loom['jacquard_cost']).')' : '' ?><br>
                                                    <b>Fitted By:</b> <?= esc($loom['fitted_by']) ?> <?= $loom['fitted_by'] == 'Company' ? '('.esc($loom['fitting_cost']).')' : '' ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $loom['status'] == 'Active' ? 'success' : 'secondary' ?>">
                                                    <?= esc($loom['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="<?= site_url('production/looms/view/' . $loom['id']) ?>" class="btn btn-sm btn-info mb-1">
                                                    <i class="fas fa-eye"></i> <?= lang("App.view") ?>
                                                </a>
                                                <form action="<?= site_url('production/looms/delete/' . $loom['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Delete this loom?');">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn btn-sm btn-danger mb-1"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- TAB: Personal Ledgers -->
                        <div class="tab-pane fade" id="personal-ledgers" role="tabpanel" aria-labelledby="personal-ledgers-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0 fw-bold">Personal Ledgers</h5>
                                <?php if (in_array('weaver.edit', session('permissions') ?? [])): ?>
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#newWeaverLedgerModal">
                                        <i class="fas fa-plus me-1"></i> New Ledger
                                    </button>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (empty($weaver_ledgers)): ?>
                                <div class="text-center p-4 text-muted">
                                    <i class="fas fa-wallet fa-3x mb-3 text-light"></i>
                                    <h5>No active personal ledgers.</h5>
                                    <p>Track personal loans or festival advances here.</p>
                                </div>
                            <?php else: ?>
                                <div class="accordion" id="weaverLedgerAccordion">
                                    <?php foreach ($weaver_ledgers as $index => $ledger): ?>
                                        <div class="accordion-item mb-3 border">
                                            <h2 class="accordion-header" id="headingWL<?= $ledger['id'] ?>">
                                                <button class="accordion-button <?= $index === 0 ? '' : 'collapsed' ?> bg-light fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseWL<?= $ledger['id'] ?>" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="collapseWL<?= $ledger['id'] ?>">
                                                    <div class="d-flex justify-content-between w-100 pe-3">
                                                        <span><?= esc($ledger['title']) ?> (ID: <?= $ledger['id'] ?>)</span>
                                                        <span>
                                                            Balance: <span class="text-danger fw-black">₹<?= number_format($ledger['balance_amount'], 2) ?></span>
                                                            <span class="badge bg-<?= $ledger['status'] == 'Active' ? 'warning' : 'success' ?> ms-2"><?= esc($ledger['status']) ?></span>
                                                        </span>
                                                    </div>
                                                </button>
                                            </h2>
                                            <div id="collapseWL<?= $ledger['id'] ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" aria-labelledby="headingWL<?= $ledger['id'] ?>" data-bs-parent="#weaverLedgerAccordion">
                                                <div class="accordion-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-3"><strong>Principal:</strong> ₹<?= number_format($ledger['principal_amount'], 2) ?></div>
                                                        <div class="col-md-3"><strong>Interest Rate:</strong> <?= number_format($ledger['interest_rate'], 2) ?>%</div>
                                                        <div class="col-md-3"><strong>Total Due (w/ Int):</strong> ₹<?= number_format($ledger['total_amount_due'], 2) ?></div>
                                                        <div class="col-md-3 text-end">
                                                            <?php if ($ledger['status'] == 'Active' && in_array('weaver.edit', session('permissions') ?? [])): ?>
                                                                <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick='openEditWeaverLedgerModal(<?= json_encode($ledger, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                                                    <i class="fas fa-edit"></i> Edit
                                                                </button>
                                                                <button type="button" class="btn btn-sm btn-primary" onclick="openWeaverPaymentModal(<?= $ledger['id'] ?>, <?= $ledger['balance_amount'] ?>)">
                                                                    <i class="fas fa-hand-holding-usd"></i> Add Txn
                                                                </button>
                                                                <form action="<?= site_url('production/weaver-ledgers/delete/' . $ledger['id']) ?>" method="post" class="d-inline ms-1" onsubmit="return confirm('Delete this personal ledger completely?');">
                                                                    <?= csrf_field() ?>
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </form>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    
                                                    <h6 class="fw-bold mt-4 border-bottom pb-2">Transactions</h6>
                                                    <?php if (empty($weaver_transactions[$ledger['id']])): ?>
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
                                                                    <?php foreach ($weaver_transactions[$ledger['id']] as $txn): ?>
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
                                                                                <form action="<?= site_url('production/weaver-ledgers/reverse/' . $txn['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Reverse this transaction?');">
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

                        <!-- TAB: Consolidated Payments -->
                        <div class="tab-pane fade" id="consolidated" role="tabpanel" aria-labelledby="consolidated-tab">
                            <h5 class="mb-3 fw-bold">All Payment Transactions</h5>
                            <?php if (empty($consolidated_txns)): ?>
                                <p class="text-muted">No transactions found across any ledgers.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped table-bordered" id="consolidatedTxnTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>Source</th>
                                                <th>Ledger Name</th>
                                                <th>Type</th>
                                                <th>Amount</th>
                                                <th>Method</th>
                                                <th>Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($consolidated_txns as $ctxn): ?>
                                            <tr>
                                                <td><?= date('d M Y', strtotime($ctxn['transaction_date'])) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $ctxn['ledger_type'] == 'Personal' ? 'primary' : 'dark' ?>">
                                                        <?= esc($ctxn['ledger_type']) ?>
                                                    </span>
                                                </td>
                                                <td><?= esc($ctxn['ledger_name']) ?></td>
                                                <td>
                                                    <?php if ($ctxn['transaction_type'] == 'Payment'): ?>
                                                        <span class="text-success fw-bold">Payment</span>
                                                    <?php elseif ($ctxn['transaction_type'] == 'Waiveoff'): ?>
                                                        <span class="text-warning fw-bold">Waiveoff</span>
                                                    <?php elseif ($ctxn['transaction_type'] == 'Interest Addition'): ?>
                                                        <span class="text-danger fw-bold">Interest</span>
                                                    <?php else: ?>
                                                        <span class="text-muted"><?= esc($ctxn['transaction_type']) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="fw-bold">₹<?= number_format($ctxn['amount'], 2) ?></td>
                                                <td>
                                                    <?= esc($ctxn['payment_method']) ?>
                                                    <?php if (!empty($ctxn['reference_number'])): ?>
                                                        <br><small class="text-muted">Ref: <?= esc($ctxn['reference_number']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><small><?= esc($ctxn['remarks']) ?></small></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- TAB: Wage Settlements -->
                        <div class="tab-pane fade" id="settlements" role="tabpanel" aria-labelledby="settlements-tab">
                            <h5 class="mb-3 fw-bold">Wage Settlements (Job Work)</h5>
                            <?php if (empty($settlements)): ?>
                                <p class="text-muted">No production settlements found.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped" id="settlementsTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Settlement #</th>
                                                <th>Date</th>
                                                <th class="text-end">Total Wages</th>
                                                <th class="text-end text-danger">Deductions</th>
                                                <th class="text-end text-success">Net Paid</th>
                                                <th>Status</th>
                                                <th>Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($settlements as $settlement): ?>
                                            <tr>
                                                <td><?= esc($settlement['settlement_number']) ?></td>
                                                <td><?= date('d M Y', strtotime($settlement['settlement_date'])) ?></td>
                                                <td class="text-end">₹<?= number_format($settlement['total_wages'], 2) ?></td>
                                                <td class="text-end text-danger">₹<?= number_format($settlement['deductions'], 2) ?></td>
                                                <td class="text-end text-success fw-bold">₹<?= number_format($settlement['net_amount'], 2) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $settlement['status'] == 'Paid' ? 'success' : 'warning' ?>">
                                                        <?= esc($settlement['status']) ?>
                                                    </span>
                                                </td>
                                                <td><small><?= esc($settlement['remarks']) ?></small></td>
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
        </div>
    </div>
</div>

<!-- Add Loom Modal -->
<div class="modal fade" id="addLoomModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= site_url('production/looms/store') ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="weaver_id" value="<?= $weaver['id'] ?>">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= lang("App.add_new") ?> Loom</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Loom Number / Name</label>
                        <input type="text" name="loom_number" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Contract Type</label>
                        <select name="contract_type" class="form-control" required>
                            <option value="Job Work">Job Work</option>
                            <option value="Sale & Buy Back">Sale & Buy Back</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Status</label>
                        <select name="status" class="form-control" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>

                    <hr>
                    <h6 class="fw-bold">Ownership & Costs</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Loom Owner</label>
                            <select name="loom_owner" class="form-control" onchange="document.getElementById('loom_cost_div').style.display = (this.value == 'Company') ? 'block' : 'none'">
                                <option value="Weaver">Weaver</option>
                                <option value="Company">Company</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3" id="loom_cost_div" style="display: none;">
                            <label>Loom Cost</label>
                            <input type="number" step="0.01" name="loom_cost" class="form-control" value="0">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Jacquard Owner</label>
                            <select name="jacquard_owner" class="form-control" onchange="document.getElementById('jacquard_cost_div').style.display = (this.value == 'Company') ? 'block' : 'none'">
                                <option value="Weaver">Weaver</option>
                                <option value="Company">Company</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3" id="jacquard_cost_div" style="display: none;">
                            <label>Jacquard Cost</label>
                            <input type="number" step="0.01" name="jacquard_cost" class="form-control" value="0">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Fitted By</label>
                            <select name="fitted_by" class="form-control" onchange="document.getElementById('fitting_cost_div').style.display = (this.value == 'Company') ? 'block' : 'none'">
                                <option value="Weaver">Weaver</option>
                                <option value="Company">Company</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3" id="fitting_cost_div" style="display: none;">
                            <label>Fitting Cost</label>
                            <input type="number" step="0.01" name="fitting_cost" class="form-control" value="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= lang("App.cancel") ?></button>
                    <button type="submit" class="btn btn-primary"><?= lang("App.save") ?> Loom</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- New Weaver Ledger Modal -->
<div class="modal fade" id="newWeaverLedgerModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?= site_url('production/weaver-ledgers/store') ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="weaver_id" value="<?= $weaver['id'] ?>">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Initialize Personal Ledger</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Ledger Title</label>
                        <input type="text" name="title" class="form-control" value="Festival Advance" required>
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

<!-- Weaver Transaction Modal -->
<div class="modal fade" id="weaverTransactionModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="weaverTransactionForm" method="post">
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
                        <input type="number" step="0.01" name="amount" class="form-control" required>
                        <small class="text-muted" id="wTxnBalanceInfo"></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Payment Method</label>
                        <select name="payment_method" class="form-control" onchange="document.getElementById('wTxnRefDiv').style.display = (this.value == 'Cash' || this.value == 'Auto Deduction') ? 'none' : 'block'">
                            <option value="Cash">Cash</option>
                            <option value="Cheque">Cheque</option>
                            <option value="NEFT">NEFT</option>
                            <option value="RTGS">RTGS</option>
                            <option value="UPI">UPI</option>
                            <option value="Auto Deduction">Auto Deduction</option>
                        </select>
                    </div>
                    <div class="mb-3" id="wTxnRefDiv" style="display: none;">
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
        </form>
    </div>
</div>

<!-- Edit Weaver Ledger Modal -->
<div class="modal fade" id="editWeaverLedgerModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="editWeaverLedgerForm" method="post">
            <?= csrf_field() ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Personal Ledger</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Ledger Title</label>
                        <input type="text" name="title" id="eWTitle" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Principal Amount (₹)</label>
                        <input type="number" step="0.01" name="principal_amount" id="eWPrincipal" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Interest Rate (%)</label>
                        <input type="number" step="0.01" name="interest_rate" id="eWInterest" class="form-control" required>
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

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function openWeaverPaymentModal(ledgerId, currentBalance) {
        document.getElementById('weaverTransactionForm').action = '<?= site_url('production/weaver-ledgers/payment/') ?>' + ledgerId;
        document.getElementById('wTxnBalanceInfo').innerText = 'Current Balance: ₹' + currentBalance.toFixed(2);
        var myModal = new bootstrap.Modal(document.getElementById('weaverTransactionModal'));
        myModal.show();
    }

    function openEditWeaverLedgerModal(ledger) {
        document.getElementById('editWeaverLedgerForm').action = '<?= site_url('production/weaver-ledgers/update/') ?>' + ledger.id;
        document.getElementById('eWTitle').value = ledger.title;
        document.getElementById('eWPrincipal').value = ledger.principal_amount;
        document.getElementById('eWInterest').value = ledger.interest_rate;
        var myModal = new bootstrap.Modal(document.getElementById('editWeaverLedgerModal'));
        myModal.show();
    }
</script>
<?= $this->endSection() ?>
