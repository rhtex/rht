<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Database Backup & Restore<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-database text-warning me-2"></i>Database Backup & Restore</h1>
    </div>
    <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <form action="<?= site_url('settings/backup/create') ?>" method="post" class="d-inline" id="manualBackupForm">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-primary" id="btnCreateBackup">
                <i class="fas fa-plus-circle me-1"></i> Create Backup Now
            </button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">

    <!-- Flash Alerts -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Summary Cards -->
    <div class="row g-2 mb-3">
        <div class="col-6 col-md-3">
            <div class="card border shadow-none mb-0">
                <div class="card-body p-2 d-flex align-items-center">
                    <div class="flex-shrink-0 bg-primary bg-opacity-10 text-primary rounded p-2 me-2">
                        <i class="fas fa-server fa-lg"></i>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="text-muted small text-truncate">Database</div>
                        <div class="fw-bold fs-6 text-truncate"><?= esc($dbName) ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border shadow-none mb-0">
                <div class="card-body p-2 d-flex align-items-center">
                    <div class="flex-shrink-0 bg-info bg-opacity-10 text-info rounded p-2 me-2">
                        <i class="fas fa-table fa-lg"></i>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="text-muted small text-truncate">Total Tables</div>
                        <div class="fw-bold fs-6 text-truncate"><?= number_format($tablesCount) ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border shadow-none mb-0">
                <div class="card-body p-2 d-flex align-items-center">
                    <div class="flex-shrink-0 bg-success bg-opacity-10 text-success rounded p-2 me-2">
                        <i class="fas fa-archive fa-lg"></i>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="text-muted small text-truncate">Saved Backups</div>
                        <div class="fw-bold fs-6 text-truncate"><?= count($backups) ?> files</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border shadow-none mb-0">
                <div class="card-body p-2 d-flex align-items-center">
                    <div class="flex-shrink-0 bg-warning bg-opacity-10 text-warning rounded p-2 me-2">
                        <i class="fas fa-clock fa-lg"></i>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="text-muted small text-truncate">Last Auto-Backup</div>
                        <div class="fw-bold fs-6 text-truncate"><?= !empty($settings['backup_last_run']) ? esc(date('d M Y H:i', strtotime($settings['backup_last_run']))) : 'Never' ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="card card-outline card-primary card-outline-tabs">
        <div class="card-header p-0 border-bottom-0">
            <ul class="nav nav-tabs" id="backupTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="backups-tab" data-bs-toggle="pill" href="#tab-backups" role="tab" aria-controls="tab-backups" aria-selected="true">
                        <i class="fas fa-list me-1"></i> Backups List
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="auto-backup-tab" data-bs-toggle="pill" href="#tab-auto-backup" role="tab" aria-controls="tab-auto-backup" aria-selected="false">
                        <i class="fas fa-calendar-alt me-1"></i> Auto-Backup (Midnight Cron)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="email-tab" data-bs-toggle="pill" href="#tab-email" role="tab" aria-controls="tab-email" aria-selected="false">
                        <i class="fas fa-envelope me-1"></i> Email Backup
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="gdrive-tab" data-bs-toggle="pill" href="#tab-gdrive" role="tab" aria-controls="tab-gdrive" aria-selected="false">
                        <i class="fab fa-google-drive me-1 text-success"></i> Google Drive Upload
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="backupTabContent">

                <!-- TAB 1: BACKUPS LIST -->
                <div class="tab-pane fade show active" id="tab-backups" role="tabpanel" aria-labelledby="backups-tab">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Filename</th>
                                    <th>Size</th>
                                    <th>Tables / Rows</th>
                                    <th>Source</th>
                                    <th>Email Status</th>
                                    <th>Google Drive</th>
                                    <th>Created At</th>
                                    <th class="text-center" style="width: 250px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($backups)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">
                                            <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                                            No backup files found yet. Click <strong>"Create Backup Now"</strong> above to generate your first complete SQL backup.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($backups as $index => $b): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td>
                                                <div class="fw-semibold text-primary">
                                                    <i class="fas fa-file-code me-1 text-secondary"></i>
                                                    <?= esc($b['filename']) ?>
                                                </div>
                                                <small class="text-muted"><?= esc($b['file_path']) ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?= number_format(($b['file_size'] ?? 0) / (1024 * 1024), 2) ?> MB
                                                </span>
                                            </td>
                                            <td>
                                                <small>
                                                    <i class="fas fa-table me-1"></i><?= $b['tables_count'] ?? '-' ?> tables<br>
                                                    <i class="fas fa-bars me-1"></i><?= isset($b['rows_count']) ? number_format($b['rows_count']) : '-' ?> rows
                                                </small>
                                            </td>
                                            <td>
                                                <?php if (($b['source'] ?? '') === 'auto_cron'): ?>
                                                    <span class="badge bg-info"><i class="fas fa-robot me-1"></i> Auto Midnight</span>
                                                <?php else: ?>
                                                    <span class="badge bg-primary"><i class="fas fa-user me-1"></i> Manual</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (($b['email_status'] ?? '') === 'sent'): ?>
                                                    <span class="badge bg-success" title="Delivered via email"><i class="fas fa-check me-1"></i> Sent</span>
                                                <?php elseif (($b['email_status'] ?? '') === 'failed'): ?>
                                                    <span class="badge bg-danger" title="<?= esc($b['email_error'] ?? 'Failed') ?>"><i class="fas fa-times me-1"></i> Failed</span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-muted border">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (($b['gdrive_status'] ?? '') === 'uploaded'): ?>
                                                    <span class="badge bg-success" title="Uploaded to Google Drive ID: <?= esc($b['gdrive_file_id'] ?? '') ?>">
                                                        <i class="fab fa-google-drive me-1"></i> Uploaded
                                                    </span>
                                                <?php elseif (($b['gdrive_status'] ?? '') === 'failed'): ?>
                                                    <span class="badge bg-danger" title="<?= esc($b['gdrive_error'] ?? 'Failed') ?>">
                                                        <i class="fas fa-times me-1"></i> Failed
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-muted border">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?= date('d M Y H:i:s', strtotime($b['created_at'])) ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <!-- Download -->
                                                    <a href="<?= site_url('settings/backup/download/' . $b['id']) ?>" class="btn btn-outline-primary" title="Download SQL File">
                                                        <i class="fas fa-download"></i>
                                                    </a>

                                                    <!-- Email Send -->
                                                    <form action="<?= site_url('settings/backup/email/' . $b['id']) ?>" method="post" class="d-inline">
                                                        <?= csrf_field() ?>
                                                        <button type="submit" class="btn btn-outline-info" title="Send via Email" onclick="return confirm('Send this backup to configured email recipients?');">
                                                            <i class="fas fa-paper-plane"></i>
                                                        </button>
                                                    </form>

                                                    <!-- Google Drive Upload -->
                                                    <form action="<?= site_url('settings/backup/gdrive/' . $b['id']) ?>" method="post" class="d-inline">
                                                        <?= csrf_field() ?>
                                                        <button type="submit" class="btn btn-outline-success" title="Upload to Google Drive" onclick="return confirm('Upload this backup file to Google Drive?');">
                                                            <i class="fab fa-google-drive"></i>
                                                        </button>
                                                    </form>

                                                    <!-- Restore -->
                                                    <button type="button" class="btn btn-outline-warning" title="Restore Database" data-bs-toggle="modal" data-bs-target="#restoreModal<?= $b['id'] ?>">
                                                        <i class="fas fa-undo"></i>
                                                    </button>

                                                    <!-- Delete -->
                                                    <form action="<?= site_url('settings/backup/delete/' . $b['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this backup file? This action cannot be undone.');">
                                                        <?= csrf_field() ?>
                                                        <button type="submit" class="btn btn-outline-danger" title="Delete Backup">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                </div>

                                                <!-- Restore Confirmation Modal -->
                                                <div class="modal fade" id="restoreModal<?= $b['id'] ?>" tabindex="-1" aria-labelledby="restoreModalLabel<?= $b['id'] ?>" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <form action="<?= site_url('settings/backup/restore/' . $b['id']) ?>" method="post">
                                                                <?= csrf_field() ?>
                                                                <div class="modal-header bg-warning text-dark">
                                                                    <h5 class="modal-title" id="restoreModalLabel<?= $b['id'] ?>">
                                                                        <i class="fas fa-exclamation-triangle me-2"></i>Confirm Database Restore
                                                                    </h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body text-start">
                                                                    <div class="alert alert-danger">
                                                                        <strong>WARNING!</strong> Restoring will <u>overwrite</u> all current tables and data in <strong><?= esc($dbName) ?></strong> with the contents of:
                                                                        <br><strong><?= esc($b['filename']) ?></strong>
                                                                    </div>
                                                                    <p>Before proceeding:</p>
                                                                    <ul>
                                                                        <li>Take a fresh backup of the current database right now.</li>
                                                                        <li>Ensure no active users are submitting forms or transactions.</li>
                                                                    </ul>
                                                                    <div class="form-check mt-3">
                                                                        <input class="form-check-input" type="checkbox" id="confirmCheckbox<?= $b['id'] ?>" required>
                                                                        <label class="form-check-label fw-bold text-danger" for="confirmCheckbox<?= $b['id'] ?>">
                                                                            I understand that all current database contents will be overwritten.
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-danger">
                                                                        <i class="fas fa-undo me-1"></i> Overwrite & Restore Now
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 2: AUTO-BACKUP (MIDNIGHT CRON) -->
                <div class="tab-pane fade" id="tab-auto-backup" role="tabpanel" aria-labelledby="auto-backup-tab">
                    <form action="<?= site_url('settings/backup/config') ?>" method="post">
                        <?= csrf_field() ?>
                        <div class="row">
                            <div class="col-md-7">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" role="switch" id="backup_auto_enabled" name="backup_auto_enabled" value="1" <?= (!empty($settings['backup_auto_enabled']) && $settings['backup_auto_enabled'] == '1') ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-bold" for="backup_auto_enabled">
                                        Enable Automatic Daily Database Backup
                                    </label>
                                </div>

                                <div class="mb-3">
                                    <label for="backup_cron_time" class="form-label fw-semibold">Scheduled Daily Run Time (Default: Midnight)</label>
                                    <input type="time" class="form-control" style="max-width: 200px;" id="backup_cron_time" name="backup_cron_time" value="<?= esc($settings['backup_cron_time'] ?? '00:00') ?>">
                                    <small class="text-muted">Set the target daily execution time (24-hour format). Midnight is 00:00.</small>
                                </div>

                                <div class="alert alert-secondary">
                                    <h6 class="alert-heading fw-bold"><i class="fas fa-info-circle me-1"></i> How Scheduled Auto-Backup Runs</h6>
                                    <p class="mb-2">Automated backups are executed by the CodeIgniter Spark CLI command. It generates full SQL dumps, uploads to Google Drive (if enabled), and sends to email (if enabled).</p>
                                    <div class="bg-dark text-white p-2 rounded font-monospace small mb-2">
                                        php <?= FCPATH ?>../spark db:backup --cron
                                    </div>
                                    <p class="mb-0 text-muted small">Backups will be stored in <code><?= realpath(FCPATH . '../backup') ?: (FCPATH . '../backup') ?></code>.</p>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Save Auto-Backup Settings
                                </button>
                            </div>

                            <div class="col-md-5">
                                <div class="card bg-light border">
                                    <div class="card-header bg-white fw-bold">
                                        <i class="fas fa-terminal me-1"></i> Cron / Task Scheduler Setup
                                    </div>
                                    <div class="card-body small">
                                        <h6 class="fw-bold"><i class="fab fa-linux text-primary me-1"></i> Linux / cPanel Crontab (Midnight):</h6>
                                        <pre class="bg-dark text-white p-2 rounded mb-3">0 0 * * * cd <?= realpath(FCPATH . '..') ?> && php spark db:backup --cron > /dev/null 2>&1</pre>

                                        <h6 class="fw-bold"><i class="fab fa-windows text-info me-1"></i> Windows Task Scheduler:</h6>
                                        <ol class="ps-3 mb-0">
                                            <li>Open <strong>Task Scheduler</strong> &rarr; Create Basic Task.</li>
                                            <li>Trigger: <strong>Daily</strong> at <strong>12:00:00 AM</strong> (Midnight).</li>
                                            <li>Action: <strong>Start a program</strong>:
                                                <ul>
                                                    <li>Program/script: <code>php.exe</code></li>
                                                    <li>Arguments: <code>spark db:backup --cron</code></li>
                                                    <li>Start in: <code><?= realpath(FCPATH . '..') ?></code></li>
                                                </ul>
                                            </li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- TAB 3: EMAIL BACKUP SETTINGS -->
                <div class="tab-pane fade" id="tab-email" role="tabpanel" aria-labelledby="email-tab">
                    <form action="<?= site_url('settings/backup/config') ?>" method="post">
                        <?= csrf_field() ?>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" role="switch" id="backup_email_enabled" name="backup_email_enabled" value="1" <?= (!empty($settings['backup_email_enabled']) && $settings['backup_email_enabled'] == '1') ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-bold" for="backup_email_enabled">
                                        Send Database Backup File as Email Attachment
                                    </label>
                                </div>

                                <div class="mb-3">
                                    <label for="backup_email_recipients" class="form-label fw-semibold">Recipient Email Address(es) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="backup_email_recipients" name="backup_email_recipients" value="<?= esc($settings['backup_email_recipients'] ?? '') ?>" placeholder="admin@domain.com, backup@domain.com">
                                    <small class="text-muted">Separate multiple email addresses with commas.</small>
                                </div>

                                <hr class="my-4">
                                <h5 class="fw-bold text-primary mb-3"><i class="fas fa-mail-bulk me-2"></i>Custom SMTP Server Configuration (Optional)</h5>
                                <p class="text-muted small">Leave empty if using server default <code>mail</code> protocol or standard CodeIgniter Email config.</p>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-8">
                                        <label for="backup_email_smtp_host" class="form-label">SMTP Host</label>
                                        <input type="text" class="form-control" id="backup_email_smtp_host" name="backup_email_smtp_host" value="<?= esc($settings['backup_email_smtp_host'] ?? '') ?>" placeholder="smtp.gmail.com">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="backup_email_smtp_port" class="form-label">SMTP Port</label>
                                        <input type="number" class="form-control" id="backup_email_smtp_port" name="backup_email_smtp_port" value="<?= esc($settings['backup_email_smtp_port'] ?? '587') ?>" placeholder="587">
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label for="backup_email_smtp_user" class="form-label">SMTP Username / Email</label>
                                        <input type="text" class="form-control" id="backup_email_smtp_user" name="backup_email_smtp_user" value="<?= esc($settings['backup_email_smtp_user'] ?? '') ?>" placeholder="user@gmail.com">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="backup_email_smtp_pass" class="form-label">SMTP Password / App Password</label>
                                        <input type="password" class="form-control" id="backup_email_smtp_pass" name="backup_email_smtp_pass" value="<?= esc($settings['backup_email_smtp_pass'] ?? '') ?>" placeholder="••••••••">
                                    </div>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label for="backup_email_smtp_crypto" class="form-label">Encryption</label>
                                        <select class="form-select" id="backup_email_smtp_crypto" name="backup_email_smtp_crypto">
                                            <option value="tls" <?= (($settings['backup_email_smtp_crypto'] ?? 'tls') === 'tls') ? 'selected' : '' ?>>TLS (Port 587)</option>
                                            <option value="ssl" <?= (($settings['backup_email_smtp_crypto'] ?? '') === 'ssl') ? 'selected' : '' ?>>SSL (Port 465)</option>
                                            <option value="" <?= (empty($settings['backup_email_smtp_crypto'])) ? 'selected' : '' ?>>None</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="backup_email_from_email" class="form-label">From Email Address</label>
                                        <input type="email" class="form-control" id="backup_email_from_email" name="backup_email_from_email" value="<?= esc($settings['backup_email_from_email'] ?? '') ?>" placeholder="noreply@domain.com">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="backup_email_from_name" class="form-label">From Sender Name</label>
                                        <input type="text" class="form-control" id="backup_email_from_name" name="backup_email_from_name" value="<?= esc($settings['backup_email_from_name'] ?? 'RHT DB Backup') ?>">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Save Email Configuration
                                </button>
                            </div>
                            <div class="col-md-4">
                                <div class="alert alert-info">
                                    <h6 class="fw-bold"><i class="fas fa-lightbulb me-1"></i> Tip for Gmail SMTP</h6>
                                    <p class="small mb-1">If sending with Gmail:</p>
                                    <ol class="small ps-3 mb-0">
                                        <li>Host: <code>smtp.gmail.com</code></li>
                                        <li>Port: <code>587</code> with <code>TLS</code></li>
                                        <li>Generate an <strong>App Password</strong> in Google Account Security (requires 2FA enabled).</li>
                                        <li>Paste your 16-character App Password above.</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- TAB 4: GOOGLE DRIVE UPLOAD SETTINGS -->
                <div class="tab-pane fade" id="tab-gdrive" role="tabpanel" aria-labelledby="gdrive-tab">
                    <form action="<?= site_url('settings/backup/config') ?>" method="post">
                        <?= csrf_field() ?>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" role="switch" id="backup_gdrive_enabled" name="backup_gdrive_enabled" value="1" <?= (!empty($settings['backup_gdrive_enabled']) && $settings['backup_gdrive_enabled'] == '1') ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-bold" for="backup_gdrive_enabled">
                                        Enable Google Drive Auto-Upload for Backups
                                    </label>
                                </div>

                                <div class="mb-3">
                                    <label for="backup_gdrive_client_id" class="form-label fw-semibold">Google OAuth Client ID <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="backup_gdrive_client_id" name="backup_gdrive_client_id" value="<?= esc($settings['backup_gdrive_client_id'] ?? '') ?>" placeholder="xxxxxx-xxxxxx.apps.googleusercontent.com">
                                </div>

                                <div class="mb-3">
                                    <label for="backup_gdrive_client_secret" class="form-label fw-semibold">Google OAuth Client Secret <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="backup_gdrive_client_secret" name="backup_gdrive_client_secret" value="<?= esc($settings['backup_gdrive_client_secret'] ?? '') ?>" placeholder="GOCSPX-xxxxxxxxxxxxxxxxxxxx">
                                </div>

                                <div class="mb-3">
                                    <label for="backup_gdrive_refresh_token" class="form-label fw-semibold">OAuth Refresh Token <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="backup_gdrive_refresh_token" name="backup_gdrive_refresh_token" value="<?= esc($settings['backup_gdrive_refresh_token'] ?? '') ?>" placeholder="1//0xxxxxxxxxxxxxxxxxxxxxxxx">
                                    <small class="text-muted">Long-lived refresh token authorized for scope <code>https://www.googleapis.com/auth/drive.file</code>.</small>
                                </div>

                                <div class="mb-4">
                                    <label for="backup_gdrive_folder_id" class="form-label fw-semibold">Target Google Drive Folder ID (Optional)</label>
                                    <input type="text" class="form-control" id="backup_gdrive_folder_id" name="backup_gdrive_folder_id" value="<?= esc($settings['backup_gdrive_folder_id'] ?? '') ?>" placeholder="1bAxxxxxx_yyyyyy-zzzzzz">
                                    <small class="text-muted">The ID from your Google Drive folder URL (e.g. <code>drive.google.com/drive/folders/<strong>FOLDER_ID</strong></code>). If left blank, backups will be stored in your root Drive.</small>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Save Google Drive Configuration
                                </button>
                            </div>

                            <div class="col-md-4">
                                <div class="card bg-light border">
                                    <div class="card-header bg-white fw-bold">
                                        <i class="fab fa-google text-danger me-1"></i> How to get Google Drive Credentials
                                    </div>
                                    <div class="card-body small">
                                        <ol class="ps-3 mb-0">
                                            <li class="mb-2">Go to <a href="https://console.cloud.google.com/" target="_blank" rel="noopener">Google Cloud Console</a> and create a project.</li>
                                            <li class="mb-2">Enable the <strong>Google Drive API</strong> in <em>APIs & Services &rarr; Library</em>.</li>
                                            <li class="mb-2">Configure <strong>OAuth Consent Screen</strong> (External, add scope <code>drive.file</code>).</li>
                                            <li class="mb-2">Create credentials &rarr; <strong>OAuth 2.0 Client ID</strong> (Web application).</li>
                                            <li class="mb-2">Authorize redirect URI: <code>https://developers.google.com/oauthplayground</code></li>
                                            <li class="mb-2">In <a href="https://developers.google.com/oauthplayground" target="_blank" rel="noopener">OAuth Playground</a>, check <code>https://www.googleapis.com/auth/drive.file</code>, exchange for tokens to get your <strong>Refresh Token</strong>.</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const backupForm = document.getElementById('manualBackupForm');
    const btn = document.getElementById('btnCreateBackup');
    if (backupForm && btn) {
        backupForm.addEventListener('submit', function() {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Dumping Database...';
        });
    }
});
</script>
<?= $this->endSection() ?>

