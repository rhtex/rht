    <?php include __DIR__ . '/../layouts/head.php'; ?>
    <?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>

    <h4 class="mt-2">Add New Agent</h4>

    <form action="<?= base_url('/agents/store') ?>" method="post">
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" name="name" id="name" required>
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" name="email" id="email" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="mobile" class="form-label">Mobile</label>
                <input type="text" class="form-control" name="mobile" id="mobile" required>
            </div>
            <div class="col-md-6">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" class="form-control" name="phone" id="phone">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="remarks" class="form-label">Remarks</label>
                <textarea class="form-control" name="remarks" id="remarks" rows="3" required></textarea>
            </div>
            <div class="col-md-6">
                <label for="remarks" class="form-label">Address</label>
                <textarea class="form-control" name="address" id="address" rows="3" required></textarea>
            </div>

        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="status" class="form-label">Status</label>
                <select class="form-control" name="status" id="status">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Save Agent</button>
    </form>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>