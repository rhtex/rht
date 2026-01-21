<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Zoho extends BaseConfig
{
    /**
     * Zoho API Credentials
     * These should be defined in your .env file
     */
    public $clientId       = '';
    public $clientSecret   = '';
    public $refreshToken   = '';
    public $organizationId = '';
    
    /**
     * Zoho API Endpoints
     */
    public $accountsUrl = 'https://accounts.zoho.in/oauth/v2/token'; // .in for India, change if needed
    public $baseUrl     = 'https://books.zoho.in/api/v3';

    public function __construct()
    {
        parent::__construct();
        
        $this->clientId       = env('ZOHO_CLIENT_ID', '');
        $this->clientSecret   = env('ZOHO_CLIENT_SECRET', '');
        $this->refreshToken   = env('ZOHO_REFRESH_TOKEN', '');
        $this->organizationId = env('ZOHO_ORGANIZATION_ID', '');
    }
}
