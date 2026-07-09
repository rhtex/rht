<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>View Weaver Details<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>View Weaver Details</h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('production/weavers') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-info-circle me-1"></i> Weaver Information</h3>
            <div class="card-tools">
                <?php if(in_array('weaver.edit', session('permissions') ?? [])): ?>
                <a href="<?= site_url('production/weavers/edit/' . $weaver['id']) ?>" class="btn btn-sm btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
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
                <div class="col-md-12 mt-3">
                    <div class="card card-outline card-secondary">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Looms</h5>
                            <div class="card-tools">
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addLoomModal">
                                    <i class="fas fa-plus"></i> Add Loom
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
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
                                    <?php 
                                        $loomModel = new \App\Models\WeaverLoomModel();
                                        $looms = $loomModel->where('weaver_id', $weaver['id'])->findAll();
                                        if (empty($looms)):
                                    ?>
                                        <tr><td colspan="4" class="text-center">No looms added yet.</td></tr>
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
                                                <button type="button" class="btn btn-sm btn-warning" onclick="editLoom(<?= htmlspecialchars(json_encode($loom)) ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="<?= site_url('production/looms/delete/' . $loom['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Delete this loom?');">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
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
                    <h5 class="modal-title">Add New Loom</h5>
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Loom</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<!-- Edit Loom Modal -->
<div class="modal fade" id="editLoomModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="editLoomForm" method="post">
            <?= csrf_field() ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Loom</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Loom Number / Name</label>
                        <input type="text" name="loom_number" id="edit_loom_number" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Contract Type</label>
                        <select name="contract_type" id="edit_contract_type" class="form-control" required>
                            <option value="Job Work">Job Work</option>
                            <option value="Sale & Buy Back">Sale & Buy Back</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Status</label>
                        <select name="status" id="edit_status" class="form-control" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>

                    <hr>
                    <h6 class="fw-bold">Ownership & Costs</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Loom Owner</label>
                            <select name="loom_owner" id="edit_loom_owner" class="form-control" onchange="document.getElementById('edit_loom_cost_div').style.display = (this.value == 'Company') ? 'block' : 'none'">
                                <option value="Weaver">Weaver</option>
                                <option value="Company">Company</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3" id="edit_loom_cost_div" style="display: none;">
                            <label>Loom Cost</label>
                            <input type="number" step="0.01" name="loom_cost" id="edit_loom_cost" class="form-control" value="0">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Jacquard Owner</label>
                            <select name="jacquard_owner" id="edit_jacquard_owner" class="form-control" onchange="document.getElementById('edit_jacquard_cost_div').style.display = (this.value == 'Company') ? 'block' : 'none'">
                                <option value="Weaver">Weaver</option>
                                <option value="Company">Company</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3" id="edit_jacquard_cost_div" style="display: none;">
                            <label>Jacquard Cost</label>
                            <input type="number" step="0.01" name="jacquard_cost" id="edit_jacquard_cost" class="form-control" value="0">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Fitted By</label>
                            <select name="fitted_by" id="edit_fitted_by" class="form-control" onchange="document.getElementById('edit_fitting_cost_div').style.display = (this.value == 'Company') ? 'block' : 'none'">
                                <option value="Weaver">Weaver</option>
                                <option value="Company">Company</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3" id="edit_fitting_cost_div" style="display: none;">
                            <label>Fitting Cost</label>
                            <input type="number" step="0.01" name="fitting_cost" id="edit_fitting_cost" class="form-control" value="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Loom</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function editLoom(loom) {
    document.getElementById('editLoomForm').action = '<?= site_url('production/looms/update/') ?>' + loom.id;
    document.getElementById('edit_loom_number').value = loom.loom_number;
    document.getElementById('edit_contract_type').value = loom.contract_type;
    document.getElementById('edit_status').value = loom.status;

    document.getElementById('edit_loom_owner').value = loom.loom_owner;
    document.getElementById('edit_loom_cost').value = loom.loom_cost;
    document.getElementById('edit_loom_cost_div').style.display = loom.loom_owner === 'Company' ? 'block' : 'none';

    document.getElementById('edit_jacquard_owner').value = loom.jacquard_owner;
    document.getElementById('edit_jacquard_cost').value = loom.jacquard_cost;
    document.getElementById('edit_jacquard_cost_div').style.display = loom.jacquard_owner === 'Company' ? 'block' : 'none';

    document.getElementById('edit_fitted_by').value = loom.fitted_by;
    document.getElementById('edit_fitting_cost').value = loom.fitting_cost;
    document.getElementById('edit_fitting_cost_div').style.display = loom.fitted_by === 'Company' ? 'block' : 'none';

    var myModal = new bootstrap.Modal(document.getElementById('editLoomModal'));
    myModal.show();
}
</script>
