<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= isset($yarn) ? '<?= lang("App.edit") ?> Yarn Stock' : '<?= lang("App.add_new") ?> Yarn Stock' ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1><?= isset($yarn) ? '<?= lang("App.edit") ?> Yarn Stock' : '<?= lang("App.add_new") ?> Yarn Stock' ?></h1>
    </div>
    <div class="col-sm-6">
        <a href="<?= site_url('production/yarns') ?>" class="btn btn-secondary float-sm-end"><?= lang("App.back") ?></a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <form action="<?= isset($yarn) ? site_url('production/yarns/update/'.$yarn['id']) : site_url('production/yarns/store') ?>" method="post">
        <?= csrf_field() ?>
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

            <?php 
                $currentName = old('name', $yarn['name'] ?? '');
                $isPredefined = false;
                if (!empty($currentName)) {
                    foreach ($masterYarns as $my) {
                        if ($my['name'] === $currentName) {
                            $isPredefined = true;
                            break;
                        }
                    }
                }
                
                $selectedNameOption = '';
                $customNameValue = '';
                if (!empty($currentName)) {
                    if ($isPredefined) {
                        $selectedNameOption = $currentName;
                    } else {
                        $selectedNameOption = 'Other';
                        $customNameValue = $currentName;
                    }
                }
            ?>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Yarn Name <span class="text-danger">*</span></label>
                    <select name="name" class="form-select select2" required>
                        <option value="">Select Yarn</option>
                        <?php foreach ($masterYarns as $my): ?>
                            <option value="<?= esc($my['name']) ?>" 
                                    data-type="<?= esc($my['yarn_type']) ?>" 
                                    data-count="<?= esc($my['yarn_count']) ?>"
                                    <?= ($selectedNameOption === $my['name']) ? 'selected' : '' ?>>
                                <?= esc($my['name']) ?> (<?= esc($my['yarn_count']) ?> - <?= esc($my['yarn_type']) ?>)
                            </option>
                        <?php endforeach; ?>
                        <option value="Other" <?= ($selectedNameOption === 'Other') ? 'selected' : '' ?>>-- Add Custom Yarn --</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3" id="custom_name_container" style="display: none;">
                    <label class="form-label">Custom Yarn Name <span class="text-danger">*</span></label>
                    <input type="text" name="custom_name" class="form-control" value="<?= esc($customNameValue) ?>" placeholder="Enter custom yarn name">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Yarn Count <span class="text-danger">*</span></label>
                    <input type="text" name="yarn_count" class="form-control" value="<?= old('yarn_count', $yarn['yarn_count'] ?? '') ?>" required placeholder="e.g. 40s, 60s, 2/40s">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Yarn Type <span class="text-danger">*</span></label>
                    <select name="yarn_type" class="form-select" required>
                        <option value="Dyed" <?= (old('yarn_type', $yarn['yarn_type'] ?? '') === 'Dyed') ? 'selected' : '' ?>>Dyed</option>
                        <option value="Raw" <?= (old('yarn_type', $yarn['yarn_type'] ?? '') === 'Raw') ? 'selected' : '' ?>>Raw</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Color (Optional)</label>
                    <input type="text" name="color" class="form-control" value="<?= old('color', $yarn['color'] ?? '') ?>" placeholder="e.g. Bleached White, Red, Blue">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Brand / Mill (Optional)</label>
                    <input type="text" name="brand" class="form-control" value="<?= old('brand', $yarn['brand'] ?? '') ?>" placeholder="e.g. Birla, Raymond">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label"><?= isset($yarn) ? 'Total KGs in Stock' : 'New Stock in KGs' ?> <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="stock_kg" class="form-control" value="<?= old('stock_kg', $yarn['stock_kg'] ?? '0.00') ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= (old('status', $yarn['status'] ?? '') === 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= (old('status', $yarn['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        var $nameSelect = $('select[name="name"]');
        var $customContainer = $('#custom_name_container');
        var $customInput = $('input[name="custom_name"]');
        var $yarnCountInput = $('input[name="yarn_count"]');
        var $yarnTypeSelect = $('select[name="yarn_type"]');

        function handleYarnSelection() {
            var selectedVal = $nameSelect.val();
            
            if (selectedVal === 'Other') {
                $customContainer.show();
                $customInput.prop('required', true);
            } else {
                $customContainer.hide();
                $customInput.prop('required', false);
                
                // Auto-populate type and count from selected option's data attributes
                var $selectedOption = $nameSelect.find('option:selected');
                var type = $selectedOption.data('type');
                var count = $selectedOption.data('count');
                
                if (type) {
                    $yarnTypeSelect.val(type);
                }
                if (count) {
                    $yarnCountInput.val(count);
                }
            }
        }

        $nameSelect.on('change', handleYarnSelection);
        
        // Run on page load
        if ($nameSelect.val() === 'Other') {
            $customContainer.show();
            $customInput.prop('required', true);
        }
    });
</script>
<?= $this->endSection() ?>
