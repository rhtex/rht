<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= isset($agent) ? 'Edit Agent' : 'Add New Agent' ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><?= $title ?></h1>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <?php $isEdit = isset($agent); ?>
                <form action="<?= $isEdit ? site_url('agents/update/'.$agent['id']) : site_url('agents/store') ?>" method="post" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Agent Name</label>
                            <input type="text" name="agent_name" class="form-control" value="<?= old('agent_name', $isEdit ? $agent['agent_name'] : '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone_number" class="form-control" value="<?= old('phone_number', $isEdit ? $agent['phone_number'] : '') ?>" required>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Address Proof Type</label>
                            <input type="text" name="address_proof_type" class="form-control" value="<?= old('address_proof_type', $isEdit ? $agent['address_proof_type'] : '') ?>" required placeholder="e.g. Aadhaar, PAN">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Address Proof ID</label>
                            <input type="text" name="address_proof_id" class="form-control" value="<?= old('address_proof_id', $isEdit ? $agent['address_proof_id'] : '') ?>" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Proof Front</label>
                            <input type="file" name="address_proof_front" class="form-control">
                            <?php if($isEdit && $agent['address_proof_front']): ?>
                                <small><a href="<?= base_url('uploads/agents/'.$agent['address_proof_front']) ?>" target="_blank">View Current</a></small>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Proof Back</label>
                            <input type="file" name="address_proof_back" class="form-control">
                            <?php if($isEdit && $agent['address_proof_back']): ?>
                                <small><a href="<?= base_url('uploads/agents/'.$agent['address_proof_back']) ?>" target="_blank">View Current</a></small>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Address Line 1</label>
                            <input type="text" name="address_1" class="form-control" value="<?= old('address_1', $isEdit ? $agent['address_1'] : '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Address Line 2</label>
                            <input type="text" name="address_2" class="form-control" value="<?= old('address_2', $isEdit ? $agent['address_2'] : '') ?>">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Village</label>
                            <input type="text" name="village" class="form-control" value="<?= old('village', $isEdit ? $agent['village'] : '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" value="<?= old('city', $isEdit ? $agent['city'] : '') ?>" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">State</label>
                            <select name="state_id" id="state_id" class="form-control select2">
                                <option value="">Select State</option>
                                <?php foreach($states as $state): ?>
                                <option value="<?= $state['id'] ?>" <?= old('state_id', ($isEdit ? $agent['state_id'] : '')) == $state['id'] ? 'selected' : '' ?>>
                                    <?= $state['name'] ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Country</label>
                            <select name="country_id" id="country_id" class="form-control select2">
                                <option value="">Select Country</option>
                                <?php foreach($countries as $country): ?>
                                <option value="<?= $country['id'] ?>" <?= old('country_id', ($isEdit ? $agent['country_id'] : ($default_country_id ?? ''))) == $country['id'] ? 'selected' : '' ?>>
                                    <?= $country['name'] ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Pincode</label>
                            <input type="text" name="pincode" class="form-control" value="<?= old('pincode', $isEdit ? $agent['pincode'] : '') ?>" required>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Save' ?></button>
                        <a href="<?= site_url('agents') ?>" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    $('#country_id').change(function() {
        const countryId = $(this).val();
        if (countryId) {
            $.ajax({
                url: '<?= site_url('master-data/states/') ?>' + countryId,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    $('#state_id').empty();
                    $('#state_id').append('<option value="">Select State</option>');
                    $.each(data, function(key, value) {
                        $('#state_id').append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                }
            });
        } else {
            $('#state_id').empty();
            $('#state_id').append('<option value="">Select State</option>');
        }
    });
});
</script>
<?= $this->endSection() ?>
