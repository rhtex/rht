<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class ApiAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $apiKey = $request->getHeaderLine('X-API-Key');

        if (!$apiKey) {
            $apiKey = $request->getGet('api_key');
        }

        if (!$apiKey) {
            return service('response')
                ->setJSON([
                    'success' => false,
                    'error' => 'API key is required'
                ])
                ->setStatusCode(401);
        }

        // Get API key from environment or database
        $validApiKey = getenv('API_KEY') ?: env('API_KEY');

        if ($apiKey !== $validApiKey) {
            return service('response')
                ->setJSON([
                    'success' => false,
                    'error' => 'Invalid API key'
                ])
                ->setStatusCode(403);
        }

        // API key is valid, allow request to continue
        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
        return $response;
    }
}
