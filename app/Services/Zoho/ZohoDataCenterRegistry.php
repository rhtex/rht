<?php

namespace App\Services\Zoho;

class ZohoDataCenterRegistry
{
    public static function getAll(): array
    {
        return self::all();
    }

    public static function all(): array
    {
        return [
            'in' => [
                'code'          => 'in',
                'label'         => 'India (.zoho.in)',
                'accounts_url'  => 'https://accounts.zoho.in',
                'authorize_url' => 'https://accounts.zoho.in/oauth/v2/auth',
                'token_url'     => 'https://accounts.zoho.in/oauth/v2/token',
                'revoke_url'    => 'https://accounts.zoho.in/oauth/v2/token/revoke',
                'api_base_url'  => 'https://www.zohoapis.in/books/v3',
            ],
            'com' => [
                'code'          => 'com',
                'label'         => 'United States / Global (.zoho.com)',
                'accounts_url'  => 'https://accounts.zoho.com',
                'authorize_url' => 'https://accounts.zoho.com/oauth/v2/auth',
                'token_url'     => 'https://accounts.zoho.com/oauth/v2/token',
                'revoke_url'    => 'https://accounts.zoho.com/oauth/v2/token/revoke',
                'api_base_url'  => 'https://www.zohoapis.com/books/v3',
            ],
            'eu' => [
                'code'          => 'eu',
                'label'         => 'Europe (.zoho.eu)',
                'accounts_url'  => 'https://accounts.zoho.eu',
                'authorize_url' => 'https://accounts.zoho.eu/oauth/v2/auth',
                'token_url'     => 'https://accounts.zoho.eu/oauth/v2/token',
                'revoke_url'    => 'https://accounts.zoho.eu/oauth/v2/token/revoke',
                'api_base_url'  => 'https://www.zohoapis.eu/books/v3',
            ],
            'com.au' => [
                'code'          => 'com.au',
                'label'         => 'Australia (.zoho.com.au)',
                'accounts_url'  => 'https://accounts.zoho.com.au',
                'authorize_url' => 'https://accounts.zoho.com.au/oauth/v2/auth',
                'token_url'     => 'https://accounts.zoho.com.au/oauth/v2/token',
                'revoke_url'    => 'https://accounts.zoho.com.au/oauth/v2/token/revoke',
                'api_base_url'  => 'https://www.zohoapis.com.au/books/v3',
            ],
            'jp' => [
                'code'          => 'jp',
                'label'         => 'Japan (.zoho.jp)',
                'accounts_url'  => 'https://accounts.zoho.jp',
                'authorize_url' => 'https://accounts.zoho.jp/oauth/v2/auth',
                'token_url'     => 'https://accounts.zoho.jp/oauth/v2/token',
                'revoke_url'    => 'https://accounts.zoho.jp/oauth/v2/token/revoke',
                'api_base_url'  => 'https://www.zohoapis.jp/books/v3',
            ],
            'ca' => [
                'code'          => 'ca',
                'label'         => 'Canada (.zohocloud.ca)',
                'accounts_url'  => 'https://accounts.zohocloud.ca',
                'authorize_url' => 'https://accounts.zohocloud.ca/oauth/v2/auth',
                'token_url'     => 'https://accounts.zohocloud.ca/oauth/v2/token',
                'revoke_url'    => 'https://accounts.zohocloud.ca/oauth/v2/token/revoke',
                'api_base_url'  => 'https://www.zohoapis.ca/books/v3',
            ],
            'sa' => [
                'code'          => 'sa',
                'label'         => 'Saudi Arabia (.zoho.sa)',
                'accounts_url'  => 'https://accounts.zoho.sa',
                'authorize_url' => 'https://accounts.zoho.sa/oauth/v2/auth',
                'token_url'     => 'https://accounts.zoho.sa/oauth/v2/token',
                'revoke_url'    => 'https://accounts.zoho.sa/oauth/v2/token/revoke',
                'api_base_url'  => 'https://www.zohoapis.sa/books/v3',
            ],
            'uk' => [
                'code'          => 'uk',
                'label'         => 'United Kingdom (.zoho.uk)',
                'accounts_url'  => 'https://accounts.zoho.uk',
                'authorize_url' => 'https://accounts.zoho.uk/oauth/v2/auth',
                'token_url'     => 'https://accounts.zoho.uk/oauth/v2/token',
                'revoke_url'    => 'https://accounts.zoho.uk/oauth/v2/token/revoke',
                'api_base_url'  => 'https://www.zohoapis.uk/books/v3',
            ],
        ];
    }

    public static function get(string $code): array
    {
        $all = self::getAll();
        return $all[$code] ?? $all['in'];
    }
}
