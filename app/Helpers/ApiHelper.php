<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class ApiHelper
{
    /**
     * Base URL untuk API
     */
    private static function baseUrl()
    {
        return env('API_BASE_URL', 'http://localhost:8000');
    }

    /**
     * Get auth token dari session
     */
    private static function getToken()
    {
        return session('auth_token');
    }

    /**
     * Make GET request ke API dengan token
     */
    public static function getWithToken($endpoint)
    {
        $token = self::getToken();
        
        if (!$token) {
            return null;
        }

        try {
            $response = Http::withToken($token)
                ->get(self::baseUrl() . $endpoint);

            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Make POST request ke API dengan token
     */
    public static function postWithToken($endpoint, $data = [])
    {
        $token = self::getToken();
        
        if (!$token) {
            return null;
        }

        try {
            $response = Http::withToken($token)
                ->post(self::baseUrl() . $endpoint, $data);

            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Make POST request tanpa token (untuk login/register)
     */
    public static function post($endpoint, $data = [])
    {
        try {
            $response = Http::post(self::baseUrl() . $endpoint, $data);

            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get user from session
     */
    public static function getUser()
    {
        return session('user');
    }

    /**
     * Get user roles
     */
    public static function getRoles()
    {
        return session('roles', []);
    }

    /**
     * Check if user has specific role
     */
    public static function hasRole($role)
    {
        return in_array($role, self::getRoles());
    }

    /**
     * Get user permissions
     */
    public static function getPermissions()
    {
        return session('permissions', []);
    }

    /**
     * Check if user has specific permission
     */
    public static function hasPermission($permission)
    {
        return in_array($permission, self::getPermissions());
    }

    /**
     * Check if user is authenticated
     */
    public static function isAuthenticated()
    {
        return session('auth_token') !== null;
    }
}
