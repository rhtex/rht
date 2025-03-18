<?php namespace App\Models;

use CodeIgniter\Model;

class OAuthTokenModel extends Model
{
    protected $table = 'oauth_tokens';
    protected $primaryKey = 'id';
    protected $allowedFields = ['access_token', 'refresh_token', 'expires_in', 'created_at'];

    public function saveToken($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');

        // Delete old tokens (if any)
        $this->truncate();

        return $this->insert($data);
    }

    public function getToken()
    {
        return $this->orderBy('created_at', 'DESC')->first();
    }
}
