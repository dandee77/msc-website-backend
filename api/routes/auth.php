<?php
/**
 * Authentication Routes
 * Handle authentication-related API endpoints
 */

require_once __DIR__ . '/../controllers/AuthController.php';

$authController = new AuthController();
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathSegments = explode('/', trim($path, '/'));

// Find the 'api' segment and get the endpoint after 'auth'
$apiIndex = array_search('api', $pathSegments);
$authIndex = array_search('auth', $pathSegments);

// Handle both original and rewritten paths
if ($authIndex !== false) {
    $endpoint = $pathSegments[$authIndex + 1] ?? '';
} elseif ($apiIndex !== false) {
    // Path like /msc-website-backend-main/api/auth/login
    $endpoint = $pathSegments[$apiIndex + 2] ?? '';
} else {
    // Direct path like /auth/login (after rewrite)
    $endpoint = $pathSegments[1] ?? '';
}

// Debug logging
error_log("Auth route - Path: " . $path);
error_log("Auth route - Path segments: " . print_r($pathSegments, true));
error_log("Auth route - API Index: " . ($apiIndex !== false ? $apiIndex : 'not found'));
error_log("Auth route - Auth Index: " . ($authIndex !== false ? $authIndex : 'not found'));
error_log("Auth route - Endpoint: " . $endpoint);

switch ($method) {
    case 'POST':
        switch ($endpoint) {
            case 'login':
                $authController->login();
                break;
            case 'register':
                $authController->register();
                break;
            case 'logout':
                $authController->logout();
                break;
            case 'change-password':
                $authController->changePassword();
                break;
            case 'forgot-password':
                $authController->forgotPassword();
                break;
            default:
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
                break;
        }
        break;
    
    case 'GET':
        switch ($endpoint) {
            case 'profile':
                $authController->getProfile();
                break;
            case '':
                // Show available auth endpoints
                echo json_encode([
                    'success' => true,
                    'message' => 'Authentication API',
                    'endpoints' => [
                        'POST /auth/login' => 'User login',
                        'POST /auth/register' => 'User registration',
                        'POST /auth/logout' => 'User logout',
                        'GET /auth/profile' => 'Get user profile',
                        'POST /auth/change-password' => 'Change password',
                        'POST /auth/forgot-password' => 'Forgot password'
                    ],
                    'debug_info' => [
                        'endpoint' => $endpoint,
                        'method' => $method,
                        'path' => $path
                    ]
                ]);
                break;
            default:
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
                break;
        }
        break;
    
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
