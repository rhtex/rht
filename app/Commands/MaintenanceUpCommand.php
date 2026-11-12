<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class MaintenanceUpCommand extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'up';
    protected $description = 'Bring the application out of maintenance mode.';
    protected $usage       = 'up';

    public function run(array $params)
    {
        $flagFile = WRITEPATH . 'maintenance.flag';

        if (file_exists($flagFile)) {
            @unlink($flagFile);
            CLI::newLine();
            CLI::write("==================================================", 'green');
            CLI::write("       APPLICATION IS NOW LIVE & ACTIVE!          ", 'white', 'green');
            CLI::write("==================================================", 'green');
            CLI::newLine();
            CLI::write("Maintenance flag removed. All users can now access the ERP.", 'white');
            CLI::newLine();
        } else {
            CLI::write("Application is already live (maintenance mode is not active).", 'yellow');
        }
    }
}
