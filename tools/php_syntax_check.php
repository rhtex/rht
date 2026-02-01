<?php
$base = __DIR__ . '/../';
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base));
$out = [];
foreach ($it as $file) {
    if (!$file->isFile()) continue;
    if (substr($file->getFilename(), -4) !== '.php') continue;
    $path = $file->getPathname();
    if (strpos($path, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR) !== false) continue;
    $cmd = 'php -l ' . escapeshellarg($path) . ' 2>&1';
    $res = shell_exec($cmd);
    if ($res === null) $res = "ERROR running php -l on $path";
    $out[] = $res;
}
file_put_contents($base . 'php_syntax_check.txt', implode("\n", $out));
echo "Done. Wrote php_syntax_check.txt\n";
