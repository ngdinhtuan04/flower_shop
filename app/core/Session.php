<?php
/**
 * Class Session - Quản lý session
 */
class Session
{
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_start();
        }
    }

    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public static function get($key, $default = null)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    public static function has($key)
    {
        return isset($_SESSION[$key]);
    }

    public static function delete($key)
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public static function destroy()
    {
        session_destroy();
        $_SESSION = [];
    }

    public static function setFlash($key, $value)
    {
        $_SESSION['flash'][$key] = $value;
    }

    public static function getFlash($key, $default = null)
    {
        if (isset($_SESSION['flash'][$key])) {
            $value = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $value;
        }
        return $default;
    }

    public static function hasFlash($key)
    {
        return isset($_SESSION['flash'][$key]);
    }

    public static function isLoggedIn()
    {
        return self::has('user_id');
    }

    public static function isAdmin()
    {
        return self::get('user_role') === 'admin' || self::get('user_role') === 'superadmin';
    }

    public static function isSuperAdmin()
    {
        return self::get('user_role') === 'superadmin';
    }

    public static function getUser()
    {
        if (!self::isLoggedIn()) {
            return null;
        }

        return [
            'id' => self::get('user_id'),
            'username' => self::get('username'),
            'email' => self::get('user_email'),
            'full_name' => self::get('user_full_name'),
            'role' => self::get('user_role'),
            'avatar' => self::get('user_avatar')
        ];
    }

    public static function login($user)
    {
        self::set('user_id', $user['id']);
        self::set('username', $user['username']);
        self::set('user_email', $user['email']);
        self::set('user_full_name', $user['full_name']);
        self::set('user_role', $user['role']);
        self::set('user_avatar', $user['avatar'] ?? 'default-avatar.png');
        self::set('logged_in', true);
    }

    public static function logout()
    {
        self::delete('user_id');
        self::delete('username');
        self::delete('user_email');
        self::delete('user_full_name');
        self::delete('user_role');
        self::delete('user_avatar');
        self::delete('logged_in');
    }

    public static function regenerate()
    {
        session_regenerate_id(true);
    }
}