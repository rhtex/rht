<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>View Agreement Details<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>View Agreement Details</h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="<?= site_url('production/agreements') ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <!-- Details Column -->
        <div class="col-md-5">
            <div class="card card-outline card-primary mb-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-contract me-1"></i> Agreement Information</h3>
                    <div class="card-tools">
                        <?php if(in_array('weaver.edit', session('permissions') ?? [])): ?>
                        <a href="<?= site_url('production/agreements/edit/' . $agreement['id']) ?>" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0">
                        <tr>
                            <th width="40%">Agreement Title</th>
                            <td><strong><?= esc($agreement['title']) ?></strong></td>
                        </tr>
                        <tr>
                            <th>Party Name</th>
                            <td><?= esc($agreement['party_name']) ?></td>
                        </tr>
                        <tr>
                            <th>Start Date</th>
                            <td><?= esc($agreement['start_date'] ?: '-') ?></td>
                        </tr>
                        <tr>
                            <th>End Date</th>
                            <td><?= esc($agreement['end_date'] ?: '-') ?></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge bg-<?php 
                                    if ($agreement['status'] === 'active') echo 'success';
                                    elseif ($agreement['status'] === 'expired') echo 'warning';
                                    else echo 'danger';
                                ?>">
                                    <?= ucfirst($agreement['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Created At</th>
                            <td><?= esc($agreement['created_at']) ?></td>
                        </tr>
                        <tr>
                            <th>Last Updated</th>
                            <td><?= esc($agreement['updated_at']) ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h5 class="card-title mb-0">Description / Details</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0"><?= nl2br(esc($agreement['description'] ?: 'No details provided.')) ?></p>
                </div>
            </div>
        </div>

        <!-- Document Preview Column -->
        <div class="col-md-7">
            <div class="card card-outline card-info">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0"><i class="fas fa-image me-1"></i> Agreement Document Preview</h3>
                    <?php if (!empty($agreement['agreement_file'])): ?>
                        <div class="card-tools d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-secondary" id="btn-maximize" title="Maximize Preview">
                                <i class="fas fa-expand"></i> Maximize
                            </button>
                            <a href="<?= base_url($agreement['agreement_file']) ?>" download class="btn btn-sm btn-primary">
                                <i class="fas fa-download"></i> Download File
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-body text-center bg-light">
                    <?php if (!empty($agreement['agreement_file'])): ?>
                        <?php 
                            $extension = pathinfo($agreement['agreement_file'], PATHINFO_EXTENSION);
                            $isPdf = strtolower($extension) === 'pdf';
                        ?>
                        
                        <?php if ($isPdf): ?>
                            <object data="<?= base_url($agreement['agreement_file']) ?>" type="application/pdf" width="100%" height="550px">
                                <p>It appears you don't have a PDF plugin for this browser. No biggie... you can <a href="<?= base_url($agreement['agreement_file']) ?>">click here to download the PDF file.</a></p>
                            </object>
                        <?php else: ?>
                            <a href="<?= base_url($agreement['agreement_file']) ?>" target="_blank">
                                <img src="<?= base_url($agreement['agreement_file']) ?>" alt="Agreement Document" class="img-fluid img-thumbnail" style="max-height: 550px; object-fit: contain;">
                            </a>
                            <div class="mt-2 text-muted text-sm-label">
                                <small>Click on the image to view it full size in a new tab.</small>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="py-5">
                            <i class="fas fa-file-upload fa-4x text-muted mb-3"></i>
                            <p class="text-muted">No agreement document has been uploaded.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $('#btn-maximize').on('click', function() {
            var $card = $(this).closest('.card');
            $card.toggleClass('card-maximized');
            if ($card.hasClass('card-maximized')) {
                $card.css({
                    'position': 'fixed',
                    'top': '0',
                    'left': '0',
                    'width': '100%',
                    'height': '100%',
                    'z-index': '9999',
                    'margin': '0',
                    'border-radius': '0'
                });
                $card.find('.card-body').css({
                    'height': 'calc(100vh - 60px)',
                    'overflow-y': 'auto'
                });
                $card.find('object').css('height', 'calc(100vh - 100px)');
                $card.find('img').css('max-height', 'calc(100vh - 100px)');
                $(this).html('<i class="fas fa-compress"></i> Minimize');
            } else {
                $card.removeAttr('style');
                $card.find('.card-body').removeAttr('style');
                $card.find('object').css('height', '550px');
                $card.find('img').css('max-height', '550px');
                $(this).html('<i class="fas fa-expand"></i> Maximize');
            }
        });
    });
</script>
<?= $this->endSection() ?>
