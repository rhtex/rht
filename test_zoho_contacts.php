<?php
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
chdir(__DIR__);
require 'app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/Boot.php';
\CodeIgniter\Boot::bootTest($paths);

$client = new \App\Services\Zoho\ZohoApiClient();
$res = $client->get('/contacts', ['page' => 1, 'per_page' => 10], ['module' => 'contacts', 'operation' => 'TEST_CONTACTS']);
if (!empty($res['success'])) {
    $contacts = $res['data']['contacts'] ?? [];
    echo "Total returned: " . count($contacts) . "\n";
    foreach ($contacts as $c) {
        echo "ID: " . $c['contact_id'] . " | Name: " . $c['contact_name'] . " | Type: " . ($c['contact_type'] ?? 'N/A') . "\n";
    }
} else {
    echo "ERR: " . ($res['message'] ?? 'Failed') . "\n";
}
unlink(__FILE__);
