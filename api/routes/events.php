<?php
/**
 * Event Routes
 * Handle event-related API endpoints
 */

require_once __DIR__ . '/../controllers/EventController.php';

$eventController = new EventController();
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathSegments = explode('/', trim($path, '/'));

// Remove 'api' and 'events' from path segments to get the actual endpoint
$endpoint = $pathSegments[2] ?? '';
$id = $pathSegments[3] ?? null;
$action = $pathSegments[4] ?? null;

switch ($method) {
    case 'GET':
        if ($endpoint === 'upcoming') {
            $eventController->getUpcoming();
        } elseif ($endpoint === 'calendar') {
            $eventController->getCalendarEvents();
        } elseif ($id && $action === 'registrations') {
            $eventController->getRegistrations($id);
        } elseif ($id) {
            $eventController->getById($id);
        } else {
            $eventController->getAll();
        }
        break;
    
    case 'POST':
        if ($id && $action === 'register') {
            $eventController->register($id);
        } elseif (!$id) {
            $eventController->create();
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
        }
        break;
    
    case 'PUT':
        if ($id && $action === 'attendance' && isset($pathSegments[5])) {
            $studentId = $pathSegments[5];
            $eventController->updateAttendance($id, $studentId);
        } elseif ($id) {
            $eventController->update($id);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
        }
        break;
    
    case 'DELETE':
        if ($id) {
            $eventController->delete($id);
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
