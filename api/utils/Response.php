<?php
/**
 * Response Utility Class
 * Standardized API response formatting
 */

class Response
{
    /**
     * Send success response
     */
    public static function success($data = null, $message = 'Success', $statusCode = 200)
    {
        // Clear any output buffer to prevent HTML errors
        if (ob_get_level()) {
            ob_clean();
        }
        
        http_response_code($statusCode);
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }
    
    /**
     * Send error response
     */
    public static function error($message = 'An error occurred', $statusCode = 400, $errors = null)
    {
        // Clear any output buffer to prevent HTML errors
        if (ob_get_level()) {
            ob_clean();
        }
        
        http_response_code($statusCode);
        echo json_encode([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }
    
    /**
     * Send validation error response
     */
    public static function validationError($errors, $message = 'Validation failed')
    {
        self::error($message, 422, $errors);
    }
    
    /**
     * Send unauthorized response
     */
    public static function unauthorized($message = 'Unauthorized access')
    {
        self::error($message, 401);
    }
    
    /**
     * Send not found response
     */
    public static function notFound($message = 'Resource not found')
    {
        self::error($message, 404);
    }
    
    /**
     * Send internal server error response
     */
    public static function serverError($message = 'Internal server error')
    {
        self::error($message, 500);
    }
}
