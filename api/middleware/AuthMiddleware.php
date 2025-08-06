<?php
/**
 * Authentication Middleware
 * Handle session-based authentication
 */

require_once __DIR__ . '/../utils/Response.php';

class AuthMiddleware
{
    /**
     * Check if user is authenticated
     */
    public static function authenticate()
    {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            Response::unauthorized('Authentication required');
        }
        
        return $_SESSION['user_id'];
    }
    
    /**
     * Check if user has specific role
     */
    public static function requireRole($requiredRole)
    {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            Response::unauthorized('Authentication required');
        }
        
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== $requiredRole) {
            Response::error('Insufficient privileges', 403);
        }
        
        return $_SESSION['user_id'];
    }
    
    /**
     * Check if user is officer
     */
    public static function requireOfficer()
    {
        return self::requireRole('officer');
    }
    
    /**
     * Get current user info from session
     */
    public static function getCurrentUser()
    {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'] ?? null,
            'role' => $_SESSION['role'] ?? null,
            'email' => $_SESSION['email'] ?? null
        ];
    }
    
    /**
     * Set user session
     */
    public static function setUserSession($user)
    {
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['msc_id'] = $user['msc_id'];
    }
    
    /**
     * Clear user session
     */
    public static function clearUserSession()
    {
        session_start();
        session_destroy();
    }
}
