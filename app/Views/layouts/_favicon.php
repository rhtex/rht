<?php
$faviconUrl = trim((string) get_setting('company_logo_url', ''));
if ($faviconUrl === '') {
    $logoPath = trim((string) get_setting('company_logo', ''));
    if ($logoPath !== '') {
        $faviconUrl = preg_match('#^https?://#i', $logoPath) ? $logoPath : base_url(ltrim($logoPath, '/'));
    }
}
if ($faviconUrl !== ''): ?>
    <link rel="icon" href="<?= esc($faviconUrl) ?>">
    <link rel="shortcut icon" href="<?= esc($faviconUrl) ?>">
    <link rel="apple-touch-icon" href="<?= esc($faviconUrl) ?>">
<?php endif; ?>
