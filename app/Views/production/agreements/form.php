<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= isset($agreement) ? '<?= lang("App.edit") ?> Agreement' : '<?= lang("App.add_new") ?> Agreement' ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1><?= isset($agreement) ? '<?= lang("App.edit") ?> Agreement' : '<?= lang("App.add_new") ?> Agreement' ?></h1>
    </div>
    <div class="col-sm-6">
        <a href="<?= site_url('production/agreements') ?>" class="btn btn-secondary float-sm-end"><?= lang("App.back") ?></a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <form action="<?= isset($agreement) ? site_url('production/agreements/update/'.$agreement['id']) : site_url('production/agreements/store') ?>" method="post" enctype="multipart/form-data">
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
                // Logic to determine if the existing party_name is custom (Other)
                $currentParty = old('party_name', $agreement['party_name'] ?? '');
                $isPredefined = false;
                
                if (!empty($currentParty)) {
                    foreach ($weavers as $weaver) {
                        if ($weaver['name'] === $currentParty) {
                            $isPredefined = true;
                            break;
                        }
                    }
                    if (!$isPredefined) {
                        foreach ($vendors as $vendor) {
                            if ($vendor['name'] === $currentParty) {
                                $isPredefined = true;
                                break;
                            }
                        }
                    }
                }
                
                $selectedPartyOption = '';
                $customPartyValue = '';
                if (!empty($currentParty)) {
                    if ($isPredefined) {
                        $selectedPartyOption = $currentParty;
                    } else {
                        $selectedPartyOption = 'Other';
                        $customPartyValue = $currentParty;
                    }
                }
            ?>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Agreement Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" value="<?= old('title', $agreement['title'] ?? '') ?>" required placeholder="e.g. Weaver Contract, Rent Agreement">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Party Name <span class="text-danger">*</span></label>
                    <select name="party_name" class="form-select select2" required>
                        <option value="">Select Party</option>
                        <optgroup label="Weavers">
                            <?php foreach ($weavers as $weaver): ?>
                                <option value="<?= esc($weaver['name']) ?>" <?= ($selectedPartyOption === $weaver['name']) ? 'selected' : '' ?>>
                                    <?= esc($weaver['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Job Work Vendors">
                            <?php foreach ($vendors as $vendor): ?>
                                <option value="<?= esc($vendor['name']) ?>" <?= ($selectedPartyOption === $vendor['name']) ? 'selected' : '' ?>>
                                    <?= esc($vendor['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <option value="Other" <?= ($selectedPartyOption === 'Other') ? 'selected' : '' ?>>-- Other / Custom Party --</option>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3" id="custom_party_container" style="display: none;">
                    <label class="form-label">Custom Party Name <span class="text-danger">*</span></label>
                    <input type="text" name="custom_party_name" class="form-control" value="<?= esc($customPartyValue) ?>" placeholder="Enter custom party name">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" <?= (old('status', $agreement['status'] ?? '') === 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="expired" <?= (old('status', $agreement['status'] ?? '') === 'expired') ? 'selected' : '' ?>>Expired</option>
                        <option value="terminated" <?= (old('status', $agreement['status'] ?? '') === 'terminated') ? 'selected' : '' ?>>Terminated</option>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?= old('start_date', $agreement['start_date'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?= old('end_date', $agreement['end_date'] ?? '') ?>">
                </div>
                
                <div class="col-md-12 mb-3">
                    <label class="form-label">Agreement Image / File</label>
                    <input type="file" name="agreement_file" class="form-control" accept="image/*,application/pdf">
                    <small class="text-muted">Accepted formats: Images (JPG, PNG, WebP) or PDF</small>
                    <?php if (!empty($agreement['agreement_file'])) : ?>
                        <div class="mt-2">
                            <a href="<?= base_url($agreement['agreement_file']) ?>" target="_blank" class="btn btn-xs btn-outline-info"><i class="fas fa-eye"></i> View Current File</a>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Description / Details</label>
                    <textarea name="description" class="form-control" rows="3"><?= old('description', $agreement['description'] ?? '') ?></textarea>
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
        var $partySelect = $('select[name="party_name"]');
        var $customContainer = $('#custom_party_container');
        var $customInput = $('input[name="custom_party_name"]');

        function toggleCustomParty() {
            if ($partySelect.val() === 'Other') {
                $customContainer.show();
                $customInput.prop('required', true);
            } else {
                $customContainer.hide();
                $customInput.prop('required', false);
            }
        }

        $partySelect.on('change', toggleCustomParty);
        
        // Initial call on page load
        toggleCustomParty();
    });
</script>
<?= $this->endSection() ?>
