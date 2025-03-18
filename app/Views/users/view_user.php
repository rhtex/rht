<?php include __DIR__ . '/../layouts/head.php'; ?>
<?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>


<div class="row mb-3">
    <div class="col-md-6">
        <h4> View Users </h4>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= session()->getFlashdata('success'); ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= session()->getFlashdata('error'); ?>
        </div>
    <?php endif; ?>
    <div class="card mt-3">
        <div class="card-header">
            User Information
        </div>
        <div class="card-body">
            <p><strong>ID:</strong>
                <?= $user['id']; ?>
            </p>
            <p><strong>Name:</strong>
                <?= $user['name']; ?>
            </p>
            <p><strong>Email:</strong>
                <?= $user['email']; ?>
            </p>
            <p><strong>Employee ID:</strong>
                <?= $user['employee_id']; ?>
            </p>
            <p><strong>Created At:</strong>
                <?= $user['created_at']; ?>
            </p>
        </div>
    </div>
    <div class="card mt-3">
        <div class="card-header">
            Assign Role
        </div>
        <div class="card-body">
            <form action="<?= site_url('users/assignRole/' . $user['id']); ?>" method="post">
                <div class="form-group">
                    <label for="role">Role</label>
                    <select name="role" id="role" class="form-control">
                        <option value="">-- Select Role --</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= $role['id']; ?>" <?= $assignedRoleId == $role['id'] ? 'selected' : ''; ?>>
                                <?= $role['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Assign Role</button>
            </form>
        </div>
    </div>
    <!-- Button to go back to the users list -->
    <a href="<?= site_url('users'); ?>" class="btn btn-primary mt-3">Back to Users List</a>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>