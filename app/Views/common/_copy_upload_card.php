<?php
/**
 * Shared Document Copy Upload + Camera Capture Card.
 * Expects: $title, $filePath, $uploadUrl, $deleteUrl, $fieldName, $canEdit, $emptyText
 */
$copyPath = trim((string) ($filePath ?? ''));
$hasCopy  = $copyPath !== '';
$copyUrl  = $hasCopy ? base_url(ltrim($copyPath, '/')) : '';
$isImage  = $hasCopy && preg_match('/\.(jpg|jpeg|png|webp)$/i', $copyPath);
$uid      = preg_replace('/[^a-zA-Z0-9_]/', '_', $fieldName . '_' . uniqid());
?>
<div class="card card-outline card-secondary shadow-sm mb-3">
    <div class="card-header py-2 bg-light d-flex justify-content-between align-items-center">
        <h6 class="card-title m-0 fw-bold"><i class="fas fa-paperclip text-primary me-1"></i> <?= esc($title) ?></h6>
        <?php if ($hasCopy): ?>
            <div class="d-flex gap-2">
                <a href="<?= esc($copyUrl) ?>" target="_blank" class="btn btn-xs btn-outline-primary">
                    <i class="fas fa-external-link-alt me-1"></i> View / Download
                </a>
                <?php if ($canEdit): ?>
                <form action="<?= $deleteUrl ?>" method="post" class="d-inline" onsubmit="return confirm('Remove this uploaded copy?')">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-xs btn-outline-danger"><i class="fas fa-trash-alt me-1"></i> Remove</button>
                </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-body py-2">
        <?php if ($hasCopy): ?>
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                <div class="d-flex align-items-center gap-2">
                    <?php if ($isImage): ?>
                        <a href="<?= esc($copyUrl) ?>" target="_blank">
                            <img src="<?= esc($copyUrl) ?>" alt="<?= esc($title) ?>" class="img-thumbnail" style="max-height: 70px;">
                        </a>
                    <?php else: ?>
                        <a href="<?= esc($copyUrl) ?>" target="_blank" class="text-danger fw-semibold text-decoration-none">
                            <i class="fas fa-file-pdf fa-lg me-1"></i> <?= esc(basename($copyPath)) ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($canEdit): ?>
            <form action="<?= $uploadUrl ?>" method="post" enctype="multipart/form-data" id="form_<?= $uid ?>" class="d-flex flex-wrap align-items-center gap-2">
                <?= csrf_field() ?>
                <input type="file" name="<?= esc($fieldName) ?>" id="file_<?= $uid ?>" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png,.webp" required style="max-width: 250px;">
                <input type="file" id="cam_fallback_<?= $uid ?>" accept="image/*" capture="environment" class="d-none">
                <button type="submit" class="btn btn-sm <?= $hasCopy ? 'btn-outline-primary' : 'btn-primary' ?> text-nowrap">
                    <i class="fas fa-upload me-1"></i> <?= $hasCopy ? 'Replace' : 'Upload' ?>
                </button>
                <button type="button" class="btn btn-sm btn-outline-success text-nowrap" onclick="openDocCamera('<?= $uid ?>')">
                    <i class="fas fa-camera me-1"></i> Camera
                </button>
                <small class="text-muted">PDF, JPG, PNG, or WEBP (max 10 MB)</small>
            </form>
        <?php elseif (!$hasCopy): ?>
            <span class="text-muted small"><?= esc($emptyText ?? 'No copy uploaded.') ?></span>
        <?php endif; ?>
    </div>
</div>

<?php if ($canEdit && empty($GLOBALS['_doc_camera_modal_rendered'])): $GLOBALS['_doc_camera_modal_rendered'] = true; ?>
<div class="modal fade" id="docCameraModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-bold"><i class="fas fa-camera text-success me-1"></i> Capture Document Copy</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-2 bg-dark">
                <video id="docCamVideo" autoplay playsinline class="w-100 rounded" style="max-height: 60vh;"></video>
                <canvas id="docCamCanvas" class="d-none"></canvas>
            </div>
            <div class="modal-footer py-2 justify-content-between">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-success px-3" id="docCamCaptureBtn">
                    <i class="fas fa-camera me-1"></i> Capture &amp; Upload
                </button>
            </div>
        </div>
    </div>
</div>
<script>
(function() {
    let activeStream = null;
    let activeUid = null;
    const modalEl = document.getElementById('docCameraModal');
    const video = document.getElementById('docCamVideo');
    const canvas = document.getElementById('docCamCanvas');

    function stopStream() {
        if (activeStream) {
            activeStream.getTracks().forEach(t => t.stop());
            activeStream = null;
        }
        if (video) video.srcObject = null;
    }

    if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', stopStream);
    }

    window.openDocCamera = function(uid) {
        activeUid = uid;
        const fallback = document.getElementById('cam_fallback_' + uid);
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            if (fallback) {
                fallback.onchange = function() {
                    if (!fallback.files || !fallback.files[0]) return;
                    const dt = new DataTransfer();
                    dt.items.add(fallback.files[0]);
                    document.getElementById('file_' + uid).files = dt.files;
                    document.getElementById('form_' + uid).submit();
                };
                fallback.click();
            }
            return;
        }

        navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' } }, audio: false })
            .then(function(stream) {
                activeStream = stream;
                video.srcObject = stream;
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            })
            .catch(function() {
                // Permission denied or no camera available: fall back to native capture/file picker
                if (fallback) {
                    fallback.onchange = function() {
                        if (!fallback.files || !fallback.files[0]) return;
                        const dt = new DataTransfer();
                        dt.items.add(fallback.files[0]);
                        document.getElementById('file_' + uid).files = dt.files;
                        document.getElementById('form_' + uid).submit();
                    };
                    fallback.click();
                }
            });
    };

    document.getElementById('docCamCaptureBtn')?.addEventListener('click', function() {
        if (!activeUid || !video.videoWidth) return;
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        canvas.toBlob(function(blob) {
            if (!blob) return;
            const file = new File([blob], 'capture_' + Date.now() + '.jpg', { type: 'image/jpeg' });
            const dt = new DataTransfer();
            dt.items.add(file);
            document.getElementById('file_' + activeUid).files = dt.files;
            stopStream();
            bootstrap.Modal.getOrCreateInstance(modalEl).hide();
            document.getElementById('form_' + activeUid).submit();
        }, 'image/jpeg', 0.9);
    });
})();
</script>
<?php endif; ?>
