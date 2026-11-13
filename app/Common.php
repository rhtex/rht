<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */

if (!function_exists('has_permission')) {
    /**
     * Check if currently logged in user has permission.
     * Admin role has all permissions by default.
     *
     * @param string|array $permission Single key or array of keys (returns true if ANY matches)
     * @return bool
     */
    function has_permission($permission): bool
    {
        $session = session();
        if (!$session || !$session->get('isLoggedIn')) {
            return false;
        }

        if ($session->get('role') === 'admin') {
            return true;
        }

        $userPermissions = $session->get('permissions') ?? [];
        if (!is_array($userPermissions)) {
            return false;
        }

        if (is_array($permission)) {
            foreach ($permission as $p) {
                if (in_array($p, $userPermissions, true)) {
                    return true;
                }
            }
            return false;
        }

        return in_array($permission, $userPermissions, true);
    }
}

if (!function_exists('can')) {
    function can($permission): bool
    {
        return has_permission($permission);
    }
}
