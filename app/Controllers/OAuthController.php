<?php namespace App\Controllers;

use App\Models\OAuthTokenModel;

class OAuthController extends BaseController
{
    private $client_id;
    private $client_secret;
    private $redirect_uri;
    private $token_url;
    private $auth_url;

    public function __construct()
    {
        $this->client_id = '1000.HI4NUWA54WOC5O9IZS8DLW47VGPGGQ';
        $this->client_secret = '6c8384f52fcdc2d431648857b21e57daffbebc9cc8';
        $this->redirect_uri = 'http://localhost:8080';
        $this->token_url = 'https://accounts.zoho.com/oauth/v2/token';
        $this->auth_url = 'https://accounts.zoho.com/oauth/v2/auth';
    }

    public function authorize()
    {
        log_message('error', $this->client_id);
        log_message('error', $this->client_secret);
        log_message('error', $this->redirect_uri);
        $url = $this->auth_url . '?' . http_build_query([
            'response_type' => 'code',
            'client_id' => $this->client_id,
            'redirect_uri' => $this->redirect_uri,
            'scope' => 'ZohoBooks.fullaccess.all',
            'access_type' => 'offline',
            'prompt' => 'consent',
        ]);

        return redirect()->to($url);
    }

    public function callback()
    {
        //$code = $this->request->getGet('code');
        // if (!$code) {
        //     return 'Authorization failed. No code provided.';
        // }

        $client = \Config\Services::curlrequest();
        $response = $client->post($this->token_url, [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'redirect_uri' => $this->redirect_uri,                
                'code' => '1000.c4c920b27b6d6bbc1721215942f19806.fb6fe94234209f53eb0f9ee70c6df5be',
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        if (isset($data['error'])) {
            return 'Error: ' . $data['error'];
        }

        $tokenModel = new OAuthTokenModel();
        $tokenSaved = $tokenModel->saveToken([
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'],
            'expires_in' => $data['expires_in'],
        ]);

        if ($tokenSaved === false) {
            return 'Failed to save token.';
        }

        return view('oauth/success');
    }

    public function refreshToken()
    {
        $tokenModel = new OAuthTokenModel();
        $token = $tokenModel->getToken();

        if (!$token) {
            return 'No refresh token available.';
        }

        $client = \Config\Services::curlrequest();
        $response = $client->post($this->token_url, [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $token['refresh_token'],
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'redirect_uri' => $this->redirect_uri,
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        if (isset($data['error'])) {
            return 'Error: ' . $data['error'];
        }

        $tokenSaved = $tokenModel->saveToken([
            'access_token' => $data['access_token'],
            'refresh_token' => $token['refresh_token'],
            'expires_in' => $data['expires_in'],
        ]);

        if ($tokenSaved === false) {
            return 'Failed to save refreshed token.';
        }

        return 200;
    }

    public function listTokens()
    {
        $tokenModel = new OAuthTokenModel();
        $tokens = $tokenModel->findAll();
        
        echo '<pre>';
        print_r($tokens);
        echo '</pre>';
    }
}
