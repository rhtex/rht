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
                <li class="breadcrumb-item"><a href="<?= base_url('settings/taxes') ?>">Taxes</a></li>
                <li class="breadcrumb-item active"><?= isset($tax) ? 'Edit' : 'New' ?></li>
            </ol>
        </div>
    </div>

    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title"><?= isset($tax) ? '<?= lang("App.edit") ?> Tax' : 'New Tax' ?></h3>
        </div>
        
        <?php $action = isset($tax) ? base_url('settings/taxes/update/' . $tax['id']) : base_url('settings/taxes/create'); ?>
        <form action="<?= $action ?>" method="post">
            <?= csrf_field() ?>
            <div class="card-body">
                <div class="form-group mb-3">
                    <label for="name">Tax Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Enter tax name (e.g. GST 18%)" value="<?= old('name', $tax['name'] ?? '') ?>" required>
                </div>
                
                <div class="form-group mb-3">
                    <label for="percentage">Percentage (%) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control" id="percentage" name="percentage" placeholder="Enter percentage (e.g. 18.00)" value="<?= old('percentage', $tax['percentage'] ?? '') ?>" required>
                </div>

                <div class="form-group mb-3">
                    <label for="status">Status</label>
                    <select class="form-control" id="status" name="status">
                        <option value="Active" <?= (old('status', $tax['status'] ?? '') == 'Active') ? 'selected' : '' ?>>Active</option>
                        <option value="Inactive" <?= (old('status', $tax['status'] ?? '') == 'Inactive') ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            </div>
            <!-- /.card-body -->

            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><?= lang("App.save") ?></button>
                <a href="<?= base_url('settings/taxes') ?>" class="btn btn-secondary"><?= lang("App.cancel") ?></a>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
