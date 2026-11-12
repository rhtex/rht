<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Maintenance Mode Control<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2 align-items-center">
    <div class="col-sm-6">
        <h1 class="m-0">
            <i class="fas fa-tools text-warning me-2"></i>System Maintenance Mode
        </h1>
    </div>
    <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
        <a href="<?= site_url('settings') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Settings
        </a>
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

    <div class="row">
        <!-- Main Form Column -->
        <div class="col-lg-8">
            <div class="card card-outline <?= $isDown ? 'card-danger' : 'card-success' ?> shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold m-0">
                        <i class="fas <?= $isDown ? 'fa-pause-circle text-danger' : 'fa-check-circle text-success' ?> me-2"></i>
                        Application Status: 
                        <span class="badge <?= $isDown ? 'bg-danger' : 'bg-success' ?>">
                            <?= $isDown ? 'MAINTENANCE MODE (OFFLINE)' : 'SYSTEM LIVE & ACTIVE' ?>
                        </span>
                    </h5>
                    <?php if ($isDown && !empty($config['started_at'])): ?>
                        <small class="text-muted">Enabled at: <?= esc($config['started_at']) ?></small>
                    <?php endif; ?>
                </div>

                <div class="card-body">
                    <form action="<?= site_url('settings/maintenance/toggle') ?>" method="post" id="maintenanceForm">
                        <?= csrf_field() ?>

                        <!-- Toggle Switch -->
                        <div class="p-3 mb-4 rounded border <?= $isDown ? 'bg-danger bg-opacity-10 border-danger' : 'bg-light border-secondary-subtle' ?>">
                            <div class="form-check form-switch form-switch-lg d-flex align-items-center">
                                <input class="form-check-input me-3" type="checkbox" role="switch" id="enable_maintenance" name="enable_maintenance" value="1" <?= $isDown ? 'checked' : '' ?> style="width: 3.5rem; height: 1.8rem;">
                                <div>
                                    <label class="form-check-label fw-bold fs-6 mb-0" for="enable_maintenance">
                                        <?= $isDown ? 'Disable Maintenance (Bring Site Live)' : 'Enable Maintenance Mode' ?>
                                    </label>
                                    <div class="text-muted small">
                                        <?= $isDown ? 'Turning this OFF will instantly restore public access for all employees and customers.' : 'Turning this ON will immediately show a 503 maintenance screen to all visitors.' ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Maintenance Notice Message -->
                        <div class="mb-3">
                            <label for="message" class="form-label fw-semibold">Public Maintenance Notice Message</label>
                            <textarea class="form-control" id="message" name="message" rows="3" placeholder="Database and application updates are currently in progress."><?= esc($config['message']) ?></textarea>
                            <small class="text-muted">This message will be prominently displayed on the maintenance screen.</small>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="duration" class="form-label fw-semibold">Estimated Downtime Duration</label>
                                <input type="text" class="form-control" id="duration" name="duration" value="<?= esc($config['duration']) ?>" placeholder="e.g. 15 minutes">
                                <small class="text-muted">Displayed to users (e.g., "15 minutes", "Until 06:00 PM").</small>
                            </div>

                            <div class="col-md-6">
                                <label for="retry" class="form-label fw-semibold">Auto-Retry Header (Seconds)</label>
                                <input type="number" class="form-control" id="retry" name="retry" value="<?= esc($config['retry']) ?>" placeholder="300">
                                <small class="text-muted">Tells browsers and web crawlers when to retry (Default: 300s = 5 mins).</small>
                            </div>
                        </div>

                        <!-- Secret Admin Bypass -->
                        <div class="mb-4">
                            <label for="secret" class="form-label fw-semibold">Admin Secret Bypass Token</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                                <input type="text" class="form-control font-monospace" id="secret" name="secret" value="<?= esc($config['secret']) ?>">
                                <button class="btn btn-outline-secondary" type="button" id="btnGenSecret">Generate New</button>
                            </div>
                            <small class="text-muted">Your browser is automatically authenticated. You can share this bypass link with trusted colleagues.</small>
                        </div>

                        <?php if ($isDown): ?>
                            <!-- Active Bypass Link Card -->
                            <div class="alert alert-info border-0 mb-4">
                                <div class="fw-bold mb-1"><i class="fas fa-link me-1"></i> Your Admin Bypass Link:</div>
                                <div class="bg-white p-2 rounded text-truncate font-monospace small border">
                                    <a href="<?= esc($bypassLink) ?>" target="_blank" class="text-decoration-none">
                                        <?= esc($bypassLink) ?>
                                    </a>
                                </div>
                                <small class="text-muted d-block mt-1">Visiting this link sets a secure cookie allowing access through the maintenance screen.</small>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between align-items-center">
                            <button type="submit" class="btn <?= $isDown ? 'btn-danger' : 'btn-primary' ?> px-4 py-2 fw-bold" id="btnSubmit">
                                <i class="fas fa-save me-1"></i> <?= $isDown ? 'Update Maintenance Settings' : 'Save & Turn ON Maintenance' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Informational Sidebar Column -->
        <div class="col-lg-4">
            <div class="card bg-light border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-bold">
                    <i class="fas fa-info-circle text-primary me-2"></i>How It Works on Shared Hosting
                </div>
                <div class="card-body small">
                    <ol class="ps-3 mb-0">
                        <li class="mb-2"><strong>Zero Terminal Required:</strong> Works directly from this web page on cPanel or any shared hosting without SSH.</li>
                        <li class="mb-2"><strong>Database-Free Safety:</strong> The maintenance check runs before any database query is executed, making it 100% safe to run database restores and migrations.</li>
                        <li class="mb-2"><strong>Admin Auto-Bypass:</strong> When you enable it, your admin session is granted access automatically so you can test updates freely.</li>
                        <li class="mb-0"><strong>File Manager Fallback:</strong> If you ever cannot log in, delete <code>writable/maintenance.flag</code> via cPanel File Manager to disable it instantly.</li>
                    </ol>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold">
                    <i class="fas fa-desktop text-info me-2"></i>Preview Maintenance Screen
                </div>
                <div class="card-body p-2 text-center">
                    <p class="small text-muted mb-2">Check how users see the screen during maintenance:</p>
                    <a href="<?= site_url('errors/html/maintenance.php') ?>" target="_blank" class="btn btn-outline-info btn-sm w-100" onclick="window.open('<?= site_url('settings/maintenance') ?>?preview=1', '_blank'); return false;">
                        <i class="fas fa-external-link-alt me-1"></i> Preview 503 Screen
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnGen = document.getElementById('btnGenSecret');
    const secretInput = document.getElementById('secret');
    if (btnGen && secretInput) {
        btnGen.addEventListener('click', function() {
            const chars = 'abcdef0123456789';
            let res = '';
            for (let i = 0; i < 16; i++) {
                res += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            secretInput.value = res;
        });
    }
});
</script>
<?= $this->endSection() ?>
