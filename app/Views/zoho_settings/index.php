<?= $this->extend('layouts/master') ?>

<?= $this->section('title') ?>Zoho Integration Settings<?= $this->endSection() ?>

<?= $this->section('header') ?>
<div class="row mb-2">
    <div class="col-sm-6">
        <h1>Zoho Books Integration Settings</h1>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-8">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-cog"></i> API Credentials</h3>
            </div>
            <form action="<?= site_url('zoho-settings/update') ?>" method="post">
                <?= csrf_field() ?>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Organization ID</label>
                            <input type="text" name="organization_id" class="form-control" value="<?= esc($settings['organization_id'] ?? '') ?>" placeholder="e.g. 60001234567">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Client ID</label>
                            <input type="text" name="client_id" class="form-control" value="<?= esc($settings['client_id'] ?? '') ?>">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Client Secret</label>
                            <input type="password" name="client_secret" class="form-control" value="<?= esc($settings['client_secret'] ?? '') ?>">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Refresh Token</label>
                            <textarea name="refresh_token" class="form-control" rows="3"><?= esc($settings['refresh_token'] ?? '') ?></textarea>
                            <small class="text-muted">Generated from Zoho API Console with <code>ZohoBooks.contacts.ALL</code>, <code>ZohoBooks.settings.READ</code> scopes.</small>
                        </div>
                        <hr>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">API Base URL</label>
                            <input type="text" name="api_base_url" class="form-control" value="<?= esc($settings['api_base_url'] ?? 'https://books.zoho.in/api/v3') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Accounts (OAuth) URL</label>
                            <input type="text" name="accounts_url" class="form-control" value="<?= esc($settings['accounts_url'] ?? 'https://accounts.zoho.in/oauth/v2/token') ?>">
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                    <span class="ms-3 text-muted last-updated">Last Updated: <?= ($settings['updated_at'] ?? 'Never') ?></span>
                </div>
            </form>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Setup Instructions</h3>
            </div>
            <div class="card-body">
                <ol>
                    <li>Go to <a href="https://api-console.zoho.in/" target="_blank">Zoho API Console</a>.</li>
                    <li>Create a <strong>Server-based Application</strong>.</li>
                    <li>Copy Client ID and Client Secret.</li>
                    <li>Generate a <strong>Refresh Token</strong> using the self-client or via OAuth flow.</li>
                    <li>Ensure scopes include <code>ZohoBooks.contacts.ALL</code>.</li>
                    <li>Enter details here and save.</li>
                </ol>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> <strong>Important:</strong> These credentials allow deep access to your Zoho Books data. Keep them secure.
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
