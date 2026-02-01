<?php
$base = __DIR__ . '/../';
$patterns = [
    '/\bdie\s*\(/' => 'safe_die(',
    '/\bprint_r\s*\(/' => 'safe_print_r(',
    '/\bvar_dump\s*\(/' => 'safe_var_dump(',
    '/\bdd\s*\(/' => 'safe_dd(',
    '/\bdump\s*\(/' => 'safe_dump(',
];

$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base));
$patched = [];
foreach ($it as $file) {
    if (!$file->isFile()) continue;
    $path = $file->getPathname();
    if (strpos($path, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR) !== false) continue;
    if (strpos($path, DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR) !== false) continue;
    if (substr($path, -4) !== '.php') continue;
    $contents = file_get_contents($path);
    $orig = $contents;
    foreach ($patterns as $regex => $replacement) {
        $contents = preg_replace($regex, $replacement, $contents);
    }
    if ($contents !== $orig) {
        copy($path, $path . '.bak');
        file_put_contents($path, "<?php require_once __DIR__ . '/debug_helpers.php'; ?>\n" . substr($contents, 5));
        $patched[] = $path;
    }
}
file_put_contents($base . 'tools/patch_debug_report.txt', implode("\n", $patched));
echo "Patched " . count($patched) . " files. Report: tools/patch_debug_report.txt\n";
