<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-truck text-primary me-2"></i><?= esc($title) ?></h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('vehicles') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Vehicles List
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-exclamation-triangle me-1"></i> Please correct the errors below:
                <ul class="mb-0 mt-2">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="card-title fw-bold mb-0 text-primary">
                    <i class="fas fa-info-circle me-2"></i> Vehicle Information
                </h5>
            </div>
            <form action="<?= $action ?>" method="post">
                <?= csrf_field() ?>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Vehicle Number <span class="text-danger">*</span></label>
                            <input type="text" name="vehicle_number" id="vehicle_number" class="form-control text-uppercase"
                                placeholder="e.g. TN28AB1234" required
                                value="<?= old('vehicle_number', $vehicle['vehicle_number'] ?? '') ?>">
                            <div class="form-text small">Enter registration number (e.g. TN28BY9140). Spaces will be removed automatically.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Vehicle Type <span class="text-danger">*</span></label>
                            <select name="vehicle_type" id="vehicle_type" class="form-select" required>
                                <option value="Regular" <?= (old('vehicle_type', $vehicle['vehicle_type'] ?? '') === 'Regular') ? 'selected' : '' ?>>
                                    Regular
                                </option>
                                <option value="Over Dimensional Cargo" <?= (old('vehicle_type', $vehicle['vehicle_type'] ?? '') === 'Over Dimensional Cargo') ? 'selected' : '' ?>>
                                    Over Dimensional Cargo
                                </option>
                            </select>
                            <div class="form-text small">Standard trucks and vans are 'Regular'.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Vehicle Name / Nickname</label>
                            <input type="text" name="vehicle_name" id="vehicle_name" class="form-control"
                                placeholder="e.g. Tata 407, Ashok Leyland Dost..."
                                value="<?= old('vehicle_name', $vehicle['vehicle_name'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Vehicle Model / Year</label>
                            <input type="text" name="vehicle_model" id="vehicle_model" class="form-control"
                                placeholder="e.g. 2022 Eicher Pro 2049"
                                value="<?= old('vehicle_model', $vehicle['vehicle_model'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="active" <?= (old('status', $vehicle['status'] ?? 'active') === 'active') ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= (old('status', $vehicle['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                            </select>
                            <div class="form-text small">Only active vehicles are suggested during e-Way Bill creation.</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light p-3 text-end">
                    <a href="<?= site_url('vehicles') ?>" class="btn btn-outline-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">
                        <i class="fas fa-save me-1"></i> <?= !empty($vehicle) ? 'Update Vehicle' : 'Save Vehicle' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
