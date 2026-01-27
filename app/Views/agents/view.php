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
                <li class="breadcrumb-item"><a href="<?= base_url('agents') ?>">Agents</a></li>
                <li class="breadcrumb-item active">View</li>
            </ol>
        </div>
    </div>

    <!-- Agent Information -->
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Agent Information</h3>
            <div class="card-tools">
                <a href="<?= site_url('agents/edit/' . $agent['id']) ?>" class="btn btn-sm btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="<?= site_url('agents') ?>" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th width="40%">Agent Name:</th>
                            <td class="fw-bold"><?= esc($agent['agent_name']) ?></td>
                        </tr>
                        <tr>
                            <th>Phone Number:</th>
                            <td><?= esc($agent['phone_number']) ?></td>
                        </tr>
                        <tr>
                            <th>Commission Percentage:</th>
                            <td><span class="badge text-bg-success fs-6"><?= number_format($agent['commission_percentage'], 2) ?>%</span></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th width="40%">Address Proof Type:</th>
                            <td><?= esc($agent['address_proof_type']) ?></td>
                        </tr>
                        <tr>
                            <th>Address Proof ID:</th>
                            <td><?= esc($agent['address_proof_id']) ?></td>
                        </tr>
                        <tr>
                            <th>Proof Documents:</th>
                            <td>
                                <?php if ($agent['address_proof_front']): ?>
                                    <a href="<?= base_url('uploads/agents/' . $agent['address_proof_front']) ?>" target="_blank" class="btn btn-sm btn-info">
                                        <i class="fas fa-file-image"></i> Front
                                    </a>
                                <?php endif; ?>
                                <?php if ($agent['address_proof_back']): ?>
                                    <a href="<?= base_url('uploads/agents/' . $agent['address_proof_back']) ?>" target="_blank" class="btn btn-sm btn-info">
                                        <i class="fas fa-file-image"></i> Back
                                    </a>
                                <?php endif; ?>
                                <?php if (!$agent['address_proof_front'] && !$agent['address_proof_back']): ?>
                                    <span class="text-muted">No documents uploaded</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Address Information -->
    <div class="card card-outline card-secondary">
        <div class="card-header">
            <h3 class="card-title">Address Information</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th width="40%">Address Line 1:</th>
                            <td><?= esc($agent['address_1']) ?></td>
                        </tr>
                        <tr>
                            <th>Address Line 2:</th>
                            <td><?= esc($agent['address_2']) ?: '-' ?></td>
                        </tr>
                        <tr>
                            <th>Village:</th>
                            <td><?= esc($agent['village']) ?: '-' ?></td>
                        </tr>
                        <tr>
                            <th>City:</th>
                            <td><?= esc($agent['city']) ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th width="40%">State:</th>
                            <td><?= esc($agent['state_name']) ?></td>
                        </tr>
                        <tr>
                            <th>Country:</th>
                            <td><?= esc($agent['country_name']) ?></td>
                        </tr>
                        <tr>
                            <th>Pincode:</th>
                            <td><?= esc($agent['pincode']) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
