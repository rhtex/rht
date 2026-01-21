<?php

// Path to the front controller (this file)
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);

// Location of the Paths config file.
// This is the line that might need to be changed, depending on your
// public folder structure.
$pathsPath = FCPATH . '../app/Config/Paths.php';

// ^^^ Change this if you move your application folder
require FCPATH . '../app/Config/Paths.php';
$paths = new \Config\Paths();

// Location of the framework bootstrap file.
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

// Load environment settings from .env file into $_SERVER and $_ENV
require_once FCPATH . '../vendor/codeigniter4/framework/system/Config/DotEnv.php';
(new \CodeIgniter\Config\DotEnv(FCPATH . '..'))->load();

// Bootstrap the application
$app = \CodeIgniter\Config\Services::codeigniter();
$app->initialize();
$app->setContext('web');

use App\Models\UserModel;

$userModel = new UserModel();
$email = 'admin@rasidev.com';
$password = '12345678';

echo "Checking user for email: $email\n";

$user = $userModel->where('email', $email)->first();

if (!$user) {
    echo "User NOT FOUND.\n";
    
    // Check if distinct user table count is 0
    echo "Total users in DB: " . $userModel->countAll() . "\n";
    
} else {
    echo "User FOUND.\n";
    echo "ID: " . $user['id'] . "\n";
    echo "Role ID: " . $user['role_id'] . "\n";
    echo "Stored Hash: " . $user['password'] . "\n";
    
    if (password_verify($password, $user['password'])) {
        echo "Password verification PASSED.\n";
    } else {
        echo "Password verification FAILED.\n";
        
        // Try re-hashing
        $newHash = password_hash($password, PASSWORD_BCRYPT);
        echo "Expected Hash (example): " . $newHash . "\n";
    }
}
