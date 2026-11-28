<?php
$copyPath = trim((string) ($bill['bill_copy'] ?? ''));
$hasCopy  = $copyPath !== '';
$copyUrl  = $hasCopy ? base_url(ltrim($copyPath, '/')) : '';
$isImage  = $hasCopy && preg_match('/\.(jpg|jpeg|png|webp)$/i', $copyPath);
?>
<div class="card card-outline card-secondary shadow-sm mb-3">
    <div class="card-header py-2 bg-light d-flex justify-content-between align-items-center">
        <h6 class="card-title m-0 fw-bold"><i class="fas fa-paperclip text-primary me-1"></i> Bill Copy (Supplier Invoice)</h6>
        <?php if ($hasCopy): ?>
            <div class="d-flex gap-2">
                <a href="<?= esc($copyUrl) ?>" target="_blank" class="btn btn-xs btn-outline-primary">
                    <i class="fas fa-external-link-alt me-1"></i> View / Download
                </a>
                <?php if (can('bill.edit')): ?>
                <form action="<?= site_url('bills/delete-copy/' . $bill['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Remove this bill copy?')">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-xs btn-outline-danger"><i class="fas fa-trash-alt me-1"></i> Remove</button>
                </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-body py-2">
        <?php if ($hasCopy): ?>
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2">
                    <?php if ($isImage): ?>
                        <a href="<?= esc($copyUrl) ?>" target="_blank">
                            <img src="<?= esc($copyUrl) ?>" alt="Bill Copy" class="img-thumbnail" style="max-height: 70px;">
                        </a>
                    <?php else: ?>
                        <a href="<?= esc($copyUrl) ?>" target="_blank" class="text-danger fw-semibold text-decoration-none">
                            <i class="fas fa-file-pdf fa-lg me-1"></i> <?= esc(basename($copyPath)) ?>
                        </a>
                    <?php endif; ?>
                </div>
                <?php if (can('bill.edit')): ?>
                <form action="<?= site_url('bills/upload-copy/' . $bill['id']) ?>" method="post" enctype="multipart/form-data" class="d-flex align-items-center gap-2">
                    <?= csrf_field() ?>
                    <input type="file" name="bill_copy" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png,.webp" required style="max-width: 240px;">
                    <button type="submit" class="btn btn-sm btn-outline-primary text-nowrap"><i class="fas fa-upload me-1"></i> Replace</button>
                </form>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php if (can('bill.edit')): ?>
            <form action="<?= site_url('bills/upload-copy/' . $bill['id']) ?>" method="post" enctype="multipart/form-data" class="d-flex flex-wrap align-items-center gap-2">
                <?= csrf_field() ?>
                <input type="file" name="bill_copy" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png,.webp" required style="max-width: 280px;">
                <button type="submit" class="btn btn-sm btn-primary text-nowrap"><i class="fas fa-upload me-1"></i> Upload Bill Copy</button>
                <small class="text-muted">PDF, JPG, PNG, or WEBP (max 10 MB)</small>
            </form>
            <?php else: ?>
                <span class="text-muted small">No bill copy uploaded.</span>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
