<?php include __DIR__ . '/../layouts/head.php'; ?>
<?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>

<h4 class="mt-2">Edit Agent</h4>

<form action="<?= base_url('/agents/update/' . $agent['id']) ?>" method="post">
    <!-- Hidden Field for ID -->
    <input type="hidden" name="id" value="<?= esc($agent['id']) ?>">

    <div class="row mb-3">
        <div class="col-md-6">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" name="name" id="name" value="<?= esc($agent['name']) ?>" required>
        </div>
        <div class="col-md-6">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" name="email" id="email" value="<?= esc($agent['email']) ?>" required>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label for="mobile" class="form-label">Mobile</label>
            <input type="text" class="form-control" name="mobile" id="mobile" value="<?= esc($agent['mobile']) ?>" required>
        </div>
        <div class="col-md-6">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" class="form-control" name="phone" id="phone" value="<?= esc($agent['phone']) ?>">
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label for="remarks" class="form-label">Remarks</label>
            <textarea class="form-control" name="remarks" id="remarks" rows="3"><?= esc($agent['remarks']) ?></textarea>
        </div>
        <div class="col-md-6">
            <label for="address" class="form-label">Address</label>
            <textarea class="form-control" name="address" id="address" rows="3"><?= esc($agent['address']) ?></textarea>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label for="status" class="form-label">Status</label>
            <select class="form-control" name="status" id="status">
                <option value="active" <?= ($agent['status'] == 'active') ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= ($agent['status'] == 'inactive') ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Update Agent</button>
</form>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
