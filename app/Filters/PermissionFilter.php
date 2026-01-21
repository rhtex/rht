<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class PermissionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // $arguments contains the required permissions passed from Routes
        // e.g. ['employee.create']
        
        if (empty($arguments)) {
            return;
        }

        $session = session();
        $userPermissions = $session->get('permissions') ?? [];
        $userRole = $session->get('role'); // e.g. 'admin'

        // Admin has all permissions
        if ($userRole === 'admin') {
            return;
        }

        foreach ($arguments as $permission) {
            if (!in_array($permission, $userPermissions)) {
                // Unauthorized
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Access Denied');
                // Or redirect to a 403 page
                // return redirect()->to('unauthorized'); 
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing here
    }
}
