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

// Remove 'api' and 'students' from path segments to get the actual endpoint
$endpoint = $pathSegments[2] ?? '';
$id = $pathSegments[3] ?? null;

switch ($method) {
    case 'GET':
        if ($endpoint === 'dashboard') {
            $studentController->getDashboardData();
        } elseif ($endpoint === 'search') {
            $studentController->search();
        } elseif ($id) {
            $studentController->getById($id);
        } else {
            $studentController->getAll();
        }
        break;
    
    case 'PUT':
        if ($endpoint && $id === 'profile') {
            $studentController->updateProfile($endpoint);
        } elseif ($endpoint && $id === 'toggle-active') {
            $studentController->toggleActive($endpoint);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
        }
        break;
    
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
