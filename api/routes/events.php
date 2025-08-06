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

// Debug logging
error_log("Event route - Path: " . $path);
error_log("Event route - Path segments: " . print_r($pathSegments, true));

// Find the 'api' segment and get the endpoint after 'events'
$apiIndex = array_search('api', $pathSegments);
$eventsIndex = array_search('events', $pathSegments);

if ($eventsIndex !== false) {
    $endpoint = $pathSegments[$eventsIndex + 1] ?? '';
    $id = $pathSegments[$eventsIndex + 2] ?? null;
    $action = $pathSegments[$eventsIndex + 3] ?? null;
} elseif ($apiIndex !== false) {
    // Path like /msc-website-backend-main/api/events/upcoming
    $endpoint = $pathSegments[$apiIndex + 2] ?? '';
    $id = $pathSegments[$apiIndex + 3] ?? null;
    $action = $pathSegments[$apiIndex + 4] ?? null;
} else {
    // Direct path like /events/upcoming (after rewrite)
    $endpoint = $pathSegments[1] ?? '';
    $id = $pathSegments[2] ?? null;
    $action = $pathSegments[3] ?? null;
}

error_log("Event route - Endpoint: " . $endpoint);
error_log("Event route - ID: " . ($id ?? 'null'));
error_log("Event route - Action: " . ($action ?? 'null'));

switch ($method) {
    case 'GET':
        if ($endpoint === 'upcoming') {
            $eventController->getUpcoming();
        } elseif ($endpoint === 'calendar') {
            $eventController->getCalendarEvents();
        } elseif ($endpoint && is_numeric($endpoint) && $id === 'registrations') {
            // endpoint is actually the event ID, id is 'registrations'
            $eventController->getRegistrations($endpoint);
        } elseif ($endpoint && is_numeric($endpoint)) {
            // endpoint is actually the ID
            $eventController->getById($endpoint);
        } elseif ($endpoint === '' || $endpoint === 'all') {
            $eventController->getAll();
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Event endpoint not found: ' . $endpoint]);
        }
        break;
    
    case 'POST':
        if ($endpoint && is_numeric($endpoint) && $id === 'register') {
            // endpoint is actually the event ID, id is 'register'
            $eventController->register($endpoint);
        } elseif ($endpoint === '' || $endpoint === 'create') {
            $eventController->create();
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'POST endpoint not found: ' . $endpoint]);
        }
        break;
    
    case 'PUT':
        if ($endpoint && is_numeric($endpoint) && $id === 'attendance' && $action) {
            // endpoint is event ID, id is 'attendance', action is student ID
            $eventController->updateAttendance($endpoint, $action);
        } elseif ($endpoint && is_numeric($endpoint)) {
            // endpoint is actually the ID
            $eventController->update($endpoint);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'PUT endpoint not found']);
        }
        break;
    
    case 'DELETE':
        if ($endpoint && is_numeric($endpoint)) {
            // endpoint is actually the ID
            $eventController->delete($endpoint);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'DELETE endpoint not found']);
        }
        break;
    
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}
