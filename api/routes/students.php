<?php
/**
 * Student Routes
 * Handle student-related API endpoints
 */

require_once __DIR__ . '/../controllers/StudentController.php';

$studentController = new StudentController();
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathSegments = explode('/', trim($path, '/'));

// Debug logging
error_log("Student route - Path: " . $path);
error_log("Student route - Path segments: " . print_r($pathSegments, true));

// Find the 'api' segment and get the endpoint after 'students'
$apiIndex = array_search('api', $pathSegments);
$studentsIndex = array_search('students', $pathSegments);

if ($studentsIndex !== false) {
    $endpoint = $pathSegments[$studentsIndex + 1] ?? '';
    $id = $pathSegments[$studentsIndex + 2] ?? null;
} elseif ($apiIndex !== false) {
    // Path like /msc-website-backend-main/api/students/dashboard
    $endpoint = $pathSegments[$apiIndex + 2] ?? '';
    $id = $pathSegments[$apiIndex + 3] ?? null;
} else {
    // Direct path like /students/dashboard (after rewrite)
    $endpoint = $pathSegments[1] ?? '';
    $id = $pathSegments[2] ?? null;
}

error_log("Student route - Endpoint: " . $endpoint);
error_log("Student route - ID: " . ($id ?? 'null'));

switch ($method) {
    case 'GET':
        if ($endpoint === 'dashboard') {
            $studentController->getDashboardData();
        } elseif ($endpoint === 'search') {
            $studentController->search();
        } elseif ($endpoint && is_numeric($endpoint)) {
            // endpoint is actually the ID
            $studentController->getById($endpoint);
        } elseif ($endpoint === '' || $endpoint === 'all') {
            $studentController->getAll();
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Student endpoint not found: ' . $endpoint]);
        }
        break;
    
    case 'PUT':
        if ($endpoint && $id === 'profile') {
            $studentController->updateProfile($endpoint);
        } elseif ($endpoint && $id === 'toggle-active') {
            $studentController->toggleActive($endpoint);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'PUT endpoint not found']);
        }
        break;
    
    case 'POST':
        if ($endpoint && $id === 'toggle-active') {
            $studentController->toggleActive($endpoint);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'POST endpoint not found']);
        }
        break;
    
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
