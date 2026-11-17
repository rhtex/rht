<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?><?= esc($title) ?><?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-3 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-undo-alt text-primary me-2"></i>Vendor Credit Details</h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('vendor_credits') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <?= view('vendor_credits/detail_pane', ['credit' => $credit]) ?>
    </div>
</div>
<?= $this->endSection() ?>
