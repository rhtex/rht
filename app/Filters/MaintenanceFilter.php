<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class MaintenanceFilter implements FilterInterface
{
    protected string $flagFile;

    public function __construct()
    {
        $this->flagFile = WRITEPATH . 'maintenance.flag';
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        // Allow Preview mode if explicitly previewing from Admin Settings
        if ($request->getGet('preview') === '1' && session()->get('role') === 'admin') {
            return $this->renderMaintenanceResponse([
                'message'    => 'Preview: System upgrades in progress.',
                'started_at' => date('d M Y, h:i A'),
                'duration'   => '15 minutes',
            ]);
        }

        if (!file_exists($this->flagFile)) {
            return null; // Not in maintenance mode, allow request
        }

        $raw = @file_get_contents($this->flagFile);
        $data = json_decode((string) $raw, true) ?: [];

        $secret = $data['secret'] ?? '';

        // 1. Check for Secret Bypass Query Parameter (e.g. ?bypass=SECRET_TOKEN)
        $queryBypass = $request->getGet('bypass');
        if (!empty($secret) && $queryBypass === $secret) {
            // Set cookie so admin can browse entire site during maintenance
            setcookie('rht_maintenance_bypass', $secret, [
                'expires'  => time() + (86400 * 7),
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            return null;
        }

        // 2. Check for Bypass Cookie
        $cookieBypass = $_COOKIE['rht_maintenance_bypass'] ?? '';
        if (!empty($secret) && $cookieBypass === $secret) {
            return null;
        }

        // 3. Check for Allowed IPs
        $allowedIps = $data['allowed_ips'] ?? [];
        $clientIp = $request->getIPAddress();
        if (in_array($clientIp, $allowedIps, true)) {
            return null;
        }

        return $this->renderMaintenanceResponse($data);
    }

    /**
     * Render 503 Maintenance Response
     */
    protected function renderMaintenanceResponse(array $data)
    {
        $response = Services::response();
        $response->setStatusCode(503, 'Service Unavailable');
        $response->setHeader('Retry-After', (string) ($data['retry'] ?? 300));
        $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');

        $html = view('errors/html/maintenance', [
            'message'    => $data['message'] ?? 'Database and application updates are currently in progress.',
            'started_at' => $data['started_at'] ?? date('d M Y, h:i A'),
            'duration'   => $data['duration'] ?? '10-15 minutes',
        ]);

        return $response->setBody($html);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No post-processing needed
    }
}

