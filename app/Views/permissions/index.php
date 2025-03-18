<!-- permissions/index.php -->
<?php include __DIR__ . '/../layouts/head.php'; ?>
<?php include __DIR__ . '/../layouts/layout-vertical.php'; ?>


    
        <h1 class="mb-4">
            Manage Permissions
            <?php if (!empty($role_name)): ?>
                for <?= esc($role_name) ?>
            <?php endif; ?>
        </h1>
        <?php if ($message == 'Permission Updated Successfully'): ?>
            <div class="alert alert-success" role="alert">Permissions updated Successfully.</div>
        <?php endif; ?>
        
        <!-- Fetch Role Permissions Form -->
        <?php if (empty($permissions)): ?>
            <form action="<?= base_url('/permissions/fetchPermissions') ?>" method="post">
                <div class="mb-3">
                    <label for="role_id" class="form-label">Select role to fetch permissions</label>
                    <select name="role_id" id="role_id" class="form-select">
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= $role['id'] ?>" <?= $role['id'] == $selected_role_id ? 'selected' : '' ?>>
                                <?= esc($role['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Fetch Permissions</button>
            </form>
        <?php endif; ?>

        <!-- Permissions Table for Displaying and Updating -->
        <?php if (!empty($permissions)): ?>
            <form action="<?= base_url('/permissions/updatePermissions') ?>" method="post">
                <input type="hidden" name="role_id" value="<?= $selected_role_id ?>">
                <div class="table-responsive mt-4">
                    <table class="display table table-bordered">
                        <thead>
                            <tr>
                                <th>Module</th>
                                <th>Create</th>
                                <th>Read</th>
                                <th>Update</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($modules as $module): ?>
                                <?php
                                $modulePermission = array_filter($permissions, function ($perm) use ($module) {
                                    return $perm['module_id'] == $module['id'];
                                });

                                $modulePermission = !empty($modulePermission) ? reset($modulePermission) : [
                                    'create_permission' => false,
                                    'read_permission' => false,
                                    'update_permission' => false,
                                    'delete_permission' => false,
                                ];
                                ?>
                                <tr>
                                    <td><?= esc($module['name']) ?></td>
                                    <td><input type="checkbox" name="permissions[<?= $module['id'] ?>][create]"
                                               <?= $modulePermission['create_permission'] ? 'checked' : '' ?>></td>
                                    <td><input type="checkbox" name="permissions[<?= $module['id'] ?>][read]"
                                               <?= $modulePermission['read_permission'] ? 'checked' : '' ?>></td>
                                    <td><input type="checkbox" name="permissions[<?= $module['id'] ?>][update]"
                                               <?= $modulePermission['update_permission'] ? 'checked' : '' ?>></td>
                                    <td><input type="checkbox" name="permissions[<?= $module['id'] ?>][delete]"
                                               <?= $modulePermission['delete_permission'] ? 'checked' : '' ?>></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="btn btn-success">Save Permissions</button>
                <a href="/permissions"class="btn btn-secondary">Back to List</a>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
