<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Restore Database<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0">
            <i class="fas fa-undo text-danger me-2"></i>Database Restore Console
        </h1>
    </div>
    <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <a href="<?= site_url('settings/backup') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Backup Management
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">

    <!-- Warning Banner -->
    <div class="alert alert-danger border-0 shadow-sm mb-3">
        <div class="d-flex align-items-start">
            <div class="flex-shrink-0 me-3">
                <i class="fas fa-exclamation-triangle fa-2x"></i>
            </div>
            <div class="flex-grow-1">
                <h5 class="alert-heading fw-bold mb-1">Destructive Action Warning</h5>
                <p class="mb-0">
                    Restoring a backup will <strong>completely drop and recreate</strong> all tables and data in target database
                    <code class="bg-white text-danger px-2 py-1 rounded fw-bold"><?= esc($dbName) ?></code>.
                    Please take a fresh backup prior to proceeding if you need to retain recent transactions.
                </p>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- LEFT: Upload & Restore Card -->
        <div class="col-lg-5 mb-4">
            <div class="card card-outline card-primary shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="card-title fw-bold m-0">
                        <i class="fas fa-file-archive text-warning me-2"></i>Upload Backup File
                    </h5>
                </div>
                <div class="card-body">
                    <form id="restoreUploadForm" enctype="multipart/form-data">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label for="backup_file" class="form-label fw-bold">Select Compressed Backup (.zip) or SQL (.sql) <span class="text-danger">*</span></label>
                            <input class="form-control" type="file" id="backup_file" name="backup_file" accept=".zip,.sql" required>
                            <div class="form-text text-muted">
                                <i class="fas fa-info-circle me-1"></i>Supports <code>.zip</code> (auto-extracted) and raw <code>.sql</code> files up to 512MB.
                            </div>
                        </div>

                        <div class="alert alert-warning py-2 small mb-3 border-0 bg-warning bg-opacity-10 text-dark">
                            <i class="fas fa-shield-alt text-warning me-1"></i>
                            A confirmation prompt will appear to verify before restore begins.
                        </div>

                        <button type="submit" class="btn btn-danger w-100 py-2 fw-bold" id="btnStartRestore">
                            <i class="fas fa-upload me-1"></i> Upload & Restore Database
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- RIGHT: Live Progress & Terminal Output Card -->
        <div class="col-lg-7 mb-4">
            <div class="card card-outline card-dark shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold m-0">
                        <i class="fas fa-terminal text-info me-2"></i>Restore Progress & Live Logs
                    </h5>
                    <span class="badge bg-secondary" id="restoreStatusBadge">Waiting to Start</span>
                </div>
                <div class="card-body">

                    <!-- Progress Bar -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold small" id="progressLabel">Progress: 0%</span>
                            <span class="text-muted small" id="progressStage">Idle</span>
                        </div>
                        <div class="progress" style="height: 22px;">
                            <div id="restoreProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                0%
                            </div>
                        </div>
                    </div>

                    <!-- Live Terminal Output Console -->
                    <label class="form-label fw-bold small text-muted text-uppercase mb-1">Execution Log</label>
                    <div id="terminalLog" class="p-3 bg-dark text-light rounded font-monospace small" style="min-height: 220px; max-height: 260px; overflow-y: auto; font-size: 13px; line-height: 1.5;">
                        <div class="text-muted"><i class="fas fa-chevron-right me-1"></i> Waiting for file selection and confirmation...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BOTTOM: Restored History Table -->
    <div class="card card-outline card-secondary shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="card-title fw-bold m-0">
                <i class="fas fa-history text-secondary me-2"></i>Database Restore History
            </h5>
            <small class="text-muted">Log of all restores performed on this system</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0" id="restoreHistoryTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Original File</th>
                            <th>Restored Stored As</th>
                            <th>File Size</th>
                            <th>Tables / Rows</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Restored By</th>
                            <th>Restored At</th>
                            <th class="text-center" style="width: 100px;">Audit Log</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($restores)): ?>
                            <tr id="emptyHistoryRow">
                                <td colspan="10" class="text-center py-4 text-muted">
                                    <i class="fas fa-info-circle fa-lg mb-2 d-block"></i>
                                    No database restores have been recorded yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($restores as $idx => $r): ?>
                                <tr>
                                    <td><?= $idx + 1 ?></td>
                                    <td>
                                        <div class="fw-semibold text-primary">
                                            <i class="fas fa-file-archive me-1 text-secondary"></i>
                                            <?= esc($r['original_filename'] ?: $r['filename']) ?>
                                        </div>
                                    </td>
                                    <td><small class="text-muted font-monospace"><?= esc($r['filename']) ?></small></td>
                                    <td><?= round(($r['file_size'] ?? 0) / (1024 * 1024), 2) ?> MB</td>
                                    <td>
                                        <small>
                                            <i class="fas fa-table me-1"></i><?= number_format($r['tables_count'] ?? 0) ?> tables<br>
                                            <i class="fas fa-bars me-1"></i><?= number_format($r['rows_count'] ?? 0) ?> rows
                                        </small>
                                    </td>
                                    <td><?= number_format((float)($r['duration_seconds'] ?? 0), 2) ?>s</td>
                                    <td>
                                        <?php if ($r['status'] === 'completed'): ?>
                                            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Completed</span>
                                        <?php elseif ($r['status'] === 'failed'): ?>
                                            <span class="badge bg-danger" title="<?= esc($r['error_message'] ?? 'Failed') ?>"><i class="fas fa-times-circle me-1"></i> Failed</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark"><i class="fas fa-spinner fa-spin me-1"></i> In Progress</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($r['user_name'] ?? 'System / Admin') ?></td>
                                    <td><?= date('d M Y, h:i:s A', strtotime($r['created_at'])) ?></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-info btn-view-log" data-id="<?= $r['id'] ?>" data-file="<?= esc($r['original_filename'] ?: $r['filename']) ?>" title="View Complete Restore Audit Log">
                                            <i class="fas fa-file-alt me-1"></i> View
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modern Confirmation Modal -->
    <div class="modal fade" id="confirmRestoreModal" tabindex="-1" aria-labelledby="confirmRestoreModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold" id="confirmRestoreModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirm Database Restore
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="mb-3">
                        <span class="rounded-circle bg-danger bg-opacity-10 text-danger p-3 d-inline-block">
                            <i class="fas fa-database fa-3x"></i>
                        </span>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Are you sure you want to proceed?</h5>
                    <p class="text-muted mb-3">
                        Restoring will <strong class="text-danger">permanently overwrite and recreate</strong> all tables and records in target database <code class="fw-bold text-danger"><?= esc($dbName) ?></code> with the contents of:
                    </p>
                    <div class="p-2 bg-light rounded font-monospace small text-primary mb-3 text-truncate" id="confirmModalFileName">
                        filename.zip
                    </div>
                    <p class="text-secondary small mb-0">
                        <i class="fas fa-info-circle me-1"></i>Ensure no active transactions are underway. This operation cannot be undone.
                    </p>
                </div>
                <div class="modal-footer bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-danger px-4 fw-bold" id="btnConfirmProceed">
                        <i class="fas fa-undo me-1"></i> Yes, Restore Now
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Log Modal -->
    <div class="modal fade" id="restoreLogModal" tabindex="-1" aria-labelledby="restoreLogModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="restoreLogModalLabel">
                        <i class="fas fa-file-alt text-info me-2"></i>Restore Audit Log
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <pre id="modalLogContent" class="m-0 p-3 bg-dark text-light font-monospace" style="font-size: 13px; max-height: 450px; line-height: 1.6; white-space: pre-wrap; word-break: break-all;"></pre>
                </div>
                <div class="modal-footer bg-light">
                    <a id="btnDownloadLogRaw" href="#" target="_blank" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-external-link-alt me-1"></i> Open Raw Log
                    </a>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('restoreUploadForm');
    const btn = document.getElementById('btnStartRestore');
    const progressBar = document.getElementById('restoreProgressBar');
    const progressLabel = document.getElementById('progressLabel');
    const progressStage = document.getElementById('progressStage');
    const terminal = document.getElementById('terminalLog');
    const statusBadge = document.getElementById('restoreStatusBadge');

    function appendLog(time, text, type = 'info') {
        const line = document.createElement('div');
        line.className = 'mb-1';
        let colorClass = 'text-light';
        let icon = '<i class="fas fa-angle-right text-info me-1"></i>';
        
        if (type === 'success') {
            colorClass = 'text-success fw-bold';
            icon = '<i class="fas fa-check text-success me-1"></i>';
        } else if (type === 'danger') {
            colorClass = 'text-danger fw-bold';
            icon = '<i class="fas fa-exclamation-circle text-danger me-1"></i>';
        } else if (type === 'warning') {
            colorClass = 'text-warning';
            icon = '<i class="fas fa-clock text-warning me-1"></i>';
        }

        line.innerHTML = `<span class="text-secondary me-2">[${time}]</span>${icon}<span class="${colorClass}">${text}</span>`;
        terminal.appendChild(line);
        terminal.scrollTop = terminal.scrollHeight;
    }

    function setProgress(pct, stage) {
        pct = Math.min(100, Math.max(0, pct));
        progressBar.style.width = pct + '%';
        progressBar.setAttribute('aria-valuenow', pct);
        progressBar.textContent = pct + '%';
        progressLabel.textContent = `Progress: ${pct}%`;
        if (stage) {
            progressStage.textContent = stage;
        }
    }

    const confirmModalElem = document.getElementById('confirmRestoreModal');
    const confirmModal = confirmModalElem ? new bootstrap.Modal(confirmModalElem) : null;
    const confirmFileNameElem = document.getElementById('confirmModalFileName');
    const btnProceed = document.getElementById('btnConfirmProceed');

    let pendingFormData = null;
    let pendingFile = null;

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const fileInput = document.getElementById('backup_file');
            if (!fileInput.files || fileInput.files.length === 0) {
                // Modern shake or alert feedback
                fileInput.classList.add('is-invalid');
                setTimeout(() => fileInput.classList.remove('is-invalid'), 2000);
                return;
            }

            pendingFile = fileInput.files[0];
            pendingFormData = new FormData(form);

            // Populate file details in modern confirmation modal
            const sizeMb = (pendingFile.size / (1024 * 1024)).toFixed(2);
            confirmFileNameElem.innerHTML = `<i class="fas fa-file-archive me-1 text-secondary"></i><strong>${pendingFile.name}</strong> (${sizeMb} MB)`;

            // Open modern Bootstrap confirmation modal
            if (confirmModal) {
                confirmModal.show();
            }
        });
    }

    if (btnProceed) {
        btnProceed.addEventListener('click', function() {
            if (confirmModal) {
                confirmModal.hide();
            }

            if (!pendingFormData || !pendingFile) {
                return;
            }

            const sizeMb = (pendingFile.size / (1024 * 1024)).toFixed(2);

            // Lock UI
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Processing Restore...';
            statusBadge.className = 'badge bg-warning text-dark';
            statusBadge.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Running';

            terminal.innerHTML = '';
            const now = new Date().toLocaleTimeString();
            appendLog(now, `Starting database restore process with file: ${pendingFile.name} (${sizeMb} MB)...`, 'warning');
            setProgress(5, 'Uploading to server...');

            const xhr = new XMLHttpRequest();

            // Track upload progress (0% - 30%)
            xhr.upload.addEventListener('progress', function(evt) {
                if (evt.lengthComputable) {
                    const uploadPct = Math.round((evt.loaded / evt.total) * 30);
                    setProgress(uploadPct, `Uploading: ${Math.round((evt.loaded / evt.total) * 100)}%`);
                }
            });

            xhr.upload.addEventListener('load', function() {
                setProgress(35, 'Server received file. Extracting and restoring...');
                appendLog(new Date().toLocaleTimeString(), 'Upload completed. Handing over to MySQL database engine...', 'info');
            });

            xhr.open('POST', '<?= site_url('settings/backup/upload-restore-ajax') ?>', true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            xhr.onload = function() {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-upload me-1"></i> Upload & Restore Database';

                try {
                    const res = JSON.parse(xhr.responseText);

                    // Print server execution logs
                    if (res.logs && Array.isArray(res.logs)) {
                        res.logs.forEach(l => {
                            appendLog(l.time, l.message, l.pct >= 100 ? 'success' : 'info');
                            setProgress(l.pct, l.message);
                        });
                    }

                    if (res.success) {
                        setProgress(100, 'Completed');
                        progressBar.className = 'progress-bar bg-success';
                        statusBadge.className = 'badge bg-success';
                        statusBadge.innerHTML = '<i class="fas fa-check me-1"></i> Success';
                        appendLog(new Date().toLocaleTimeString(), res.message, 'success');

                        setTimeout(() => {
                            window.location.reload();
                        }, 2500);
                    } else {
                        progressBar.className = 'progress-bar bg-danger';
                        statusBadge.className = 'badge bg-danger';
                        statusBadge.innerHTML = '<i class="fas fa-times me-1"></i> Failed';
                        appendLog(new Date().toLocaleTimeString(), 'Restore Error: ' + (res.error || 'Unknown error occurred'), 'danger');
                    }
                } catch (err) {
                    progressBar.className = 'progress-bar bg-danger';
                    statusBadge.className = 'badge bg-danger';
                    statusBadge.innerHTML = '<i class="fas fa-times me-1"></i> Error';
                    appendLog(new Date().toLocaleTimeString(), 'Failed to parse response: ' + xhr.responseText.substring(0, 300), 'danger');
                }
            };

            xhr.onerror = function() {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-upload me-1"></i> Upload & Restore Database';
                progressBar.className = 'progress-bar bg-danger';
                statusBadge.className = 'badge bg-danger';
                statusBadge.innerHTML = '<i class="fas fa-times me-1"></i> Connection Error';
                appendLog(new Date().toLocaleTimeString(), 'Network error occurred during restore upload.', 'danger');
            };

            xhr.send(pendingFormData);
        });
    }

    // Handle View Audit Log Button Click
    const logModalElem = document.getElementById('restoreLogModal');
    const logModal = logModalElem ? new bootstrap.Modal(logModalElem) : null;
    const modalContent = document.getElementById('modalLogContent');
    const modalTitle = document.getElementById('restoreLogModalLabel');
    const btnRaw = document.getElementById('btnDownloadLogRaw');

    document.querySelectorAll('.btn-view-log').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const file = this.getAttribute('data-file');

            modalTitle.innerHTML = `<i class="fas fa-file-alt text-info me-2"></i>Restore Audit Log: <small class="text-warning">${file}</small>`;
            modalContent.textContent = 'Loading audit log...';
            btnRaw.href = `<?= site_url('settings/backup/restore-log/') ?>${id}`;

            fetch(`<?= site_url('settings/backup/restore-log/') ?>${id}?json=1`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                modalContent.textContent = data.log || 'No log details available.';
                if (logModal) logModal.show();
            })
            .catch(err => {
                modalContent.textContent = 'Failed to load log: ' + err;
                if (logModal) logModal.show();
            });
        });
    });
});
</script>

<?= $this->endSection() ?>

