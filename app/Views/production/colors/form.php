<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="fw-bold text-dark"><i class="fas fa-palette text-primary me-2"></i><?= isset($color) ? 'Edit Color' : 'Add New Color' ?></h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('production/colors') ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-none"><i class="fas fa-arrow-left me-1"></i> Back</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="border-radius: 12px; max-width: 600px; margin: auto;">
    <div class="card-header border-0 py-3" style="background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);">
        <h5 class="card-title text-white mb-0 fw-bold"><i class="fas fa-edit me-2"></i><?= isset($color) ? 'Color Details' : 'New Color Details' ?></h5>
    </div>
    <form action="<?= isset($color) ? site_url('production/colors/update/'.$color['id']) : site_url('production/colors/store') ?>" method="post">
        <?= csrf_field() ?>
        <div class="card-body p-4 bg-white">
            <?php if(session()->has('errors')): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                    <?php foreach(session('errors') as $e): ?>
                        <li><?= esc($e) ?></li>
                    <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label fw-semibold">Color Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="<?= old('name', isset($color) ? $color['name'] : '') ?>" required placeholder="e.g. Royal Blue, Crimson">
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Color Code</label>
                <input type="text" name="code" class="form-control" value="<?= old('code', isset($color) ? $color['code'] : '') ?>" placeholder="e.g. BL-002, RD-105">
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold d-block">Color Palette Picker</label>
                <div class="d-flex align-items-center gap-3">
                    <input type="color" name="color_palette" class="form-control form-control-color border-secondary-subtle" id="palettePicker" value="<?= old('color_palette', isset($color) ? $color['color_palette'] : '#3498db') ?>" title="Choose color" style="width: 54px; height: 38px; padding: 2px;">
                    <span class="font-monospace text-muted" id="paletteHex"><?= old('color_palette', isset($color) ? $color['color_palette'] : '#3498db') ?></span>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                <select name="status" class="form-select" required>
                    <option value="Active" <?= old('status', isset($color) ? $color['status'] : '') === 'Active' ? 'selected' : '' ?>>Active</option>
                    <option value="Inactive" <?= old('status', isset($color) ? $color['status'] : '') === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
        </div>
        <div class="card-footer text-end bg-light border-top p-3">
            <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm"><i class="fas fa-check-circle me-1"></i> Save Color</button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var picker = document.getElementById('palettePicker');
        var hexSpan = document.getElementById('paletteHex');
        if (picker && hexSpan) {
            picker.addEventListener('input', function() {
                hexSpan.textContent = picker.value.toUpperCase();
            });
        }
    });
</script>
<?= $this->endSection() ?>
