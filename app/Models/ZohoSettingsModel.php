<?php

namespace App\Models;

use CodeIgniter\Model;

class ZohoSettingsModel extends Model
{
    protected $table            = 'zoho_settings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'client_id', 'client_secret', 'refresh_token', 'organization_id', 
        'access_token', 'token_expires_at', 'api_base_url', 'accounts_url'
    ];

    protected $useTimestamps = true;
    protected $updatedField  = 'updated_at';
    protected $createdField  = ''; // No created_at needed

    /**
     * Get the single settings record
     */
    public function getSettings()
    {
        return $this->find(1);
    }
}
