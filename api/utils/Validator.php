<?php
/**
 * Validator Utility Class
 * Input validation and sanitization
 */

class Validator
{
    /**
     * Validate required fields
     */
    public static function validateRequired($data, $requiredFields)
    {
        $errors = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $errors[$field] = "The {$field} field is required.";
            }
        }
        
        return $errors;
    }
    
    /**
     * Validate email format
     */
    public static function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate password strength
     */
    public static function validatePassword($password)
    {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long.";
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter.";
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter.";
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number.";
        }
        
        return $errors;
    }
    
    /**
     * Validate date format
     */
    public static function validateDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    
    /**
     * Validate time format
     */
    public static function validateTime($time, $format = 'H:i')
    {
        $t = DateTime::createFromFormat($format, $time);
        return $t && $t->format($format) === $time;
    }
    
    /**
     * Sanitize input data
     */
    public static function sanitize($data)
    {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }
        
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate enum values
     */
    public static function validateEnum($value, $allowedValues)
    {
        return in_array($value, $allowedValues);
    }
    
    /**
     * Validate phone number
     */
    public static function validatePhone($phone)
    {
        // Basic phone validation - adjust pattern as needed
        return preg_match('/^[\+]?[0-9\-\(\)\s]+$/', $phone);
    }
}
