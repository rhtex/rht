<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= isset($category) ? '<?= lang("App.edit") ?> Category' : '<?= lang("App.add_new") ?> Category' ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1><?= isset($category) ? '<?= lang("App.edit") ?> Category' : '<?= lang("App.add_new") ?> Category' ?></h1>
    </div>
    <div class="col-sm-6">
        <a href="<?= site_url('expense_categories') ?>" class="btn btn-secondary float-sm-end"><?= lang("App.back") ?></a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <form action="<?= isset($category) ? site_url('expense_categories/update/'.$category['id']) : site_url('expense_categories/store') ?>" method="post">
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

            <div class="mb-3">
                <label class="form-label">Category Name</label>
                <input type="text" name="category_name" class="form-control" value="<?= old('category_name', $category['category_name'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"><?= old('description', $category['description'] ?? '') ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="active" <?= (old('status', $category['status'] ?? '') === 'active') ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= (old('status', $category['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary"><?= lang("App.save") ?> Category</button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
