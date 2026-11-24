<?php

namespace App\Services;

use App\Models\UserModel;

class AuthService
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function validateLogin(string $email, string $password)
    {
        $user = $this->userModel->where('email', $email)->first();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] === 'active') {
                return $user;
            }
        }

        return null;
    }

    public function loginUser(array $user)
    {
        $session = session();
        
        // Fetch permissions for the user's role
        // For now, we assume we can get it via a join or updated query in UserModel
        // But to keep it simple, we'll fetch role details
        $roleModel = new \App\Models\RoleModel();
        $role = $roleModel->find($user['role_id']);
        
        $rolePermissionModel = new \App\Models\RolePermissionModel();
        $permissions = $rolePermissionModel->getPermissionsByRole((int)$user['role_id']);
        $permissionKeys = array_column($permissions, 'permission_key');
        
        $sessionData = [
            'user_id'       => $user['id'],
            'name'          => $user['name'],
            'email'         => $user['email'],
            'role_id'       => $user['role_id'],
            'role'          => $role['role_name'] ?? 'staff',
            'isLoggedIn'    => true,
            'permissions'   => $permissionKeys
        ];

        $session->set($sessionData);
        return true;
    }

    public const REMEMBER_COOKIE = 'rht_remember';
    public const REMEMBER_DAYS   = 30;

    public function logout()
    {
        $this->clearRememberToken();
        session()->destroy();
    }

    public function issueRememberToken(int $userId): void
    {
        $selector  = bin2hex(random_bytes(8));
        $validator = bin2hex(random_bytes(32));
        $expiresTs = time() + (86400 * self::REMEMBER_DAYS);

        $db = \Config\Database::connect();
        // Clean up expired tokens opportunistically
        $db->table('user_remember_tokens')->where('expires_at <', date('Y-m-d H:i:s'))->delete();

        $db->table('user_remember_tokens')->insert([
            'user_id'        => $userId,
            'selector'       => $selector,
            'validator_hash' => hash('sha256', $validator),
            'expires_at'     => date('Y-m-d H:i:s', $expiresTs),
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        $this->setRememberCookie($selector . ':' . $validator, $expiresTs);
    }

    public function attemptRememberLogin(): bool
    {
        $cookie = $_COOKIE[self::REMEMBER_COOKIE] ?? '';
        if (!is_string($cookie) || !str_contains($cookie, ':')) {
            return false;
        }

        [$selector, $validator] = explode(':', $cookie, 2);
        if ($selector === '' || $validator === '') {
            return false;
        }

        $db  = \Config\Database::connect();
        $row = $db->table('user_remember_tokens')
            ->where('selector', $selector)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->get()
            ->getRowArray();

        if (!$row || !hash_equals($row['validator_hash'], hash('sha256', $validator))) {
            $db->table('user_remember_tokens')->where('selector', $selector)->delete();
            $this->expireRememberCookie();
            return false;
        }

        $user = $this->userModel->find((int) $row['user_id']);
        if (!$user || ($user['status'] ?? '') !== 'active') {
            $db->table('user_remember_tokens')->where('selector', $selector)->delete();
            $this->expireRememberCookie();
            return false;
        }

        // Restore session
        $this->loginUser($user);

        // Rotate validator to prevent token replay
        $newValidator = bin2hex(random_bytes(32));
        $expiresTs    = time() + (86400 * self::REMEMBER_DAYS);
        $db->table('user_remember_tokens')
            ->where('id', $row['id'])
            ->update([
                'validator_hash' => hash('sha256', $newValidator),
                'expires_at'     => date('Y-m-d H:i:s', $expiresTs),
            ]);
        $this->setRememberCookie($selector . ':' . $newValidator, $expiresTs);

        // Restore admin maintenance bypass cookie if applicable
        $flagFile = WRITEPATH . 'maintenance.flag';
        if (file_exists($flagFile) && session()->get('role') === 'admin') {
            $maintData = json_decode((string) @file_get_contents($flagFile), true) ?: [];
            if (!empty($maintData['secret'])) {
                setcookie('rht_maintenance_bypass', $maintData['secret'], [
                    'expires'  => time() + (86400 * 7),
                    'path'     => '/',
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
            }
        }

        return true;
    }

    public function clearRememberToken(): void
    {
        $cookie = $_COOKIE[self::REMEMBER_COOKIE] ?? '';
        if (is_string($cookie) && str_contains($cookie, ':')) {
            [$selector] = explode(':', $cookie, 2);
            if ($selector !== '') {
                \Config\Database::connect()->table('user_remember_tokens')->where('selector', $selector)->delete();
            }
        }
        $this->expireRememberCookie();
    }

    private function setRememberCookie(string $value, int $expiresTs): void
    {
        setcookie(self::REMEMBER_COOKIE, $value, [
            'expires'  => $expiresTs,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        $_COOKIE[self::REMEMBER_COOKIE] = $value;
    }

    private function expireRememberCookie(): void
    {
        if (isset($_COOKIE[self::REMEMBER_COOKIE])) {
            setcookie(self::REMEMBER_COOKIE, '', [
                'expires'  => time() - 3600,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            unset($_COOKIE[self::REMEMBER_COOKIE]);
        }
    }
}
