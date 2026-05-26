<?php
?>
<?= view('layouts/header') ?>
<h2 class="mt-4 mb-3">Bulk Upload Result</h2>
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <strong>There were errors in the uploaded file:</strong>
        <ul>
            <?php foreach ($errors as $line => $msg): ?>
                <li>Row <?= esc($line); ?>: <?= esc($msg); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
<div class="alert alert-success">
    Processed <?= esc($total); ?> rows. Inserted <?= esc($inserted); ?> records.
</div>
<p><a href="<?= base_url('bulk-upload/form'); ?>" class="btn btn-primary">Upload Another File</a></p>
<?php echo view('templates/footer'); ?>
<?php
?>
