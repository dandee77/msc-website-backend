<?php
/**
 * API Router
 * Main entry point for API requests with professional routing
 */

// Enable error reporting for development (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Handle CORS first (this will set Content-Type)
require_once __DIR__ . '/config/cors.php';
CorsConfig::setup();

// Get the request path and method
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Remove base path and split into segments
$pathSegments = explode('/', trim($path, '/'));

// Debug: log the path for troubleshooting
error_log("Request path: " . $path);
error_log("Path segments: " . print_r($pathSegments, true));
error_log("Original REQUEST_URI: " . $_SERVER['REQUEST_URI']);

// For path like /msc-website-backend-main/api/auth/login
// Remove base project path and get API segments
$apiIndex = array_search('api', $pathSegments);

// Alternative approach: if we can't find 'api', check if we're already in the API directory
if ($apiIndex === false) {
    // We might be in the API directory already due to .htaccess rewrite
    // Check if the first segment could be a resource
    if (!empty($pathSegments[0])) {
        $resource = $pathSegments[0];
        error_log("Using first segment as resource: " . $resource);
    } else {
        // This is the root API call
        $resource = '';
        error_log("Root API call");
    }
} else {
    // Get the resource type (segment after 'api')
    $resource = $pathSegments[$apiIndex + 1] ?? '';
    error_log("Found API index, resource: " . $resource);
}

// Route to appropriate handler
try {
    switch ($resource) {
        case 'auth':
            require_once __DIR__ . '/routes/auth.php';
            break;
            
        case 'students':
            require_once __DIR__ . '/routes/students.php';
            break;
            
        case 'events':
            require_once __DIR__ . '/routes/events.php';
            break;
            
        case 'announcements':
            require_once __DIR__ . '/routes/announcements.php';
            break;
            
        case 'health':
            // Health check endpoint
            echo json_encode([
                'success' => true,
                'message' => 'API is running',
                'version' => '1.0.0',
                'path' => $path,
                'resource' => $resource,
                'method' => $method,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
            
        case '':
            // Root API endpoint - show available routes
            echo json_encode([
                'success' => true,
                'message' => 'MSC Student Portal API',
                'version' => '1.0.0',
                'available_routes' => [
                    '/auth' => 'Authentication endpoints',
                    '/students' => 'Student management',
                    '/events' => 'Event management',
                    '/announcements' => 'Announcement management',
                    '/health' => 'Health check'
                ],
                'path_info' => [
                    'request_path' => $path,
                    'segments' => $pathSegments,
                    'resource' => $resource
                ],
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
            
        default:
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => "Resource '{$resource}' not found",
                'available_resources' => ['auth', 'students', 'events', 'announcements'],
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
    }
} catch (Exception $e) {
    // Global error handler
    error_log("API Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error',
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} catch (Error $e) {
    // Handle fatal errors
    error_log("PHP Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Fatal error',
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
