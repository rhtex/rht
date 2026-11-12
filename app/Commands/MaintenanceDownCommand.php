<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class MaintenanceDownCommand extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'down';
    protected $description = 'Put the application into maintenance mode.';
    protected $usage       = 'down [options]';
    protected $options     = [
        '--message'  => 'Custom message to display on the maintenance screen',
        '--retry'    => 'HTTP Retry-After header value in seconds (default: 300)',
        '--secret'   => 'Secret bypass key to allow administrators to access the site',
        '--duration' => 'Estimated duration text (e.g. "15 minutes")',
        '--ip'       => 'Comma-separated IP addresses allowed to bypass maintenance mode',
    ];

    public function run(array $params)
    {
        $flagFile = WRITEPATH . 'maintenance.flag';

        $message  = CLI::getOption('message') ?? 'Database and application updates are currently in progress.';
        $retry    = (int) (CLI::getOption('retry') ?? 300);
        $duration = CLI::getOption('duration') ?? '10-15 minutes';
        $ips      = CLI::getOption('ip') ? explode(',', CLI::getOption('ip')) : [];
        $secret   = CLI::getOption('secret') ?? bin2hex(random_bytes(8));

        $data = [
            'status'      => 'down',
            'started_at'  => date('d M Y, h:i:s A'),
            'message'     => $message,
            'retry'       => $retry,
            'duration'    => $duration,
            'allowed_ips' => array_map('trim', $ips),
            'secret'      => $secret,
        ];

        @file_put_contents($flagFile, json_encode($data, JSON_PRETTY_PRINT));

        CLI::newLine();
        CLI::write("==================================================", 'red');
        CLI::write("   APPLICATION IS NOW IN MAINTENANCE MODE (503)   ", 'white', 'red');
        CLI::write("==================================================", 'red');
        CLI::newLine();
        CLI::write("Notice:      " . $message, 'yellow');
        CLI::write("Started At:  " . $data['started_at'], 'white');
        CLI::write("Duration:    " . $duration, 'white');
        CLI::newLine();
        CLI::write("ADMIN BYPASS LINK:", 'cyan');
        CLI::write("You can bypass maintenance mode by visiting:", 'white');
        CLI::write("  " . site_url('?bypass=' . $secret), 'green');
        CLI::newLine();
        CLI::write("To bring the application back online, run:", 'white');
        CLI::write("  php spark up", 'yellow');
        CLI::newLine();
    }
}
