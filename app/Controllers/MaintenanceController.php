<?php

namespace App\Controllers;

class MaintenanceController extends BaseController
{
    protected string $flagFile;

    public function __construct()
    {
        $this->flagFile = WRITEPATH . 'maintenance.flag';
    }

    /**
     * Maintenance Mode Dashboard View
     */
    public function index()
    {
        $isDown = file_exists($this->flagFile);
        $data = [
            'status'     => 'up',
            'message'    => 'Database and application updates are currently in progress.',
            'duration'   => '15 minutes',
            'retry'      => 300,
            'secret'     => bin2hex(random_bytes(8)),
            'started_at' => null,
        ];

        if ($isDown) {
            $raw = @file_get_contents($this->flagFile);
            $parsed = json_decode((string) $raw, true);
            if (is_array($parsed)) {
                $data = array_merge($data, $parsed);
            }
        }

        $viewData = [
            'title'      => 'Maintenance Mode Control',
            'isDown'     => $isDown,
            'config'     => $data,
            'bypassLink' => site_url('?bypass=' . ($data['secret'] ?? '')),
        ];

        return view('settings/maintenance/index', $viewData);
    }

    /**
     * Toggle Maintenance Mode (Enable / Disable)
     */
    public function toggle()
    {
        $post = $this->request->getPost();
        $enable = !empty($post['enable_maintenance']) && $post['enable_maintenance'] === '1';

        if ($enable) {
            $secret = !empty($post['secret']) ? trim($post['secret']) : bin2hex(random_bytes(8));
            $message = !empty($post['message']) ? trim($post['message']) : 'Database and application updates are currently in progress.';
            $duration = !empty($post['duration']) ? trim($post['duration']) : '15 minutes';
            $retry = (int) ($post['retry'] ?? 300);

            $data = [
                'status'     => 'down',
                'started_at' => date('d M Y, h:i:s A'),
                'message'    => $message,
                'duration'   => $duration,
                'retry'      => $retry,
                'secret'     => $secret,
            ];

            @file_put_contents($this->flagFile, json_encode($data, JSON_PRETTY_PRINT));

            // Set bypass cookie for current admin browser session so the admin never gets locked out
            setcookie('rht_maintenance_bypass', $secret, [
                'expires'  => time() + (86400 * 7),
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);

            return redirect()->to('settings/maintenance')->with('success', 'Maintenance Mode has been ENABLED. Regular visitors will see the 503 screen, while your browser session has administrator bypass.');
        } else {
            if (file_exists($this->flagFile)) {
                @unlink($this->flagFile);
            }

            // Remove bypass cookie
            setcookie('rht_maintenance_bypass', '', [
                'expires' => time() - 3600,
                'path'    => '/',
            ]);

            return redirect()->to('settings/maintenance')->with('success', 'Maintenance Mode has been DISABLED. The application is now live and accessible to all users.');
        }
    }
}

