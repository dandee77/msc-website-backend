<?php
/**
 * Announcement Routes
 * Handle announcement-related API endpoints
 */

require_once __DIR__ . '/../controllers/AnnouncementController.php';

$announcementController = new AnnouncementController();
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathSegments = explode('/', trim($path, '/'));

// Debug logging
error_log("Announcement route - Path: " . $path);
error_log("Announcement route - Path segments: " . print_r($pathSegments, true));

// Find the 'api' segment and get the endpoint after 'announcements'
$apiIndex = array_search('api', $pathSegments);
$announcementsIndex = array_search('announcements', $pathSegments);

if ($announcementsIndex !== false) {
    $endpoint = $pathSegments[$announcementsIndex + 1] ?? '';
    $id = $pathSegments[$announcementsIndex + 2] ?? null;
    $action = $pathSegments[$announcementsIndex + 3] ?? null;
} elseif ($apiIndex !== false) {
    // Path like /msc-website-backend-main/api/announcements/recent
    $endpoint = $pathSegments[$apiIndex + 2] ?? '';
    $id = $pathSegments[$apiIndex + 3] ?? null;
    $action = $pathSegments[$apiIndex + 4] ?? null;
} else {
    // Direct path like /announcements/recent (after rewrite)
    $endpoint = $pathSegments[1] ?? '';
    $id = $pathSegments[2] ?? null;
    $action = $pathSegments[3] ?? null;
}

error_log("Announcement route - Endpoint: " . $endpoint);
error_log("Announcement route - ID: " . ($id ?? 'null'));
error_log("Announcement route - Action: " . ($action ?? 'null'));

switch ($method) {
    case 'GET':
        if ($endpoint === 'recent') {
            $announcementController->getRecent();
        } elseif ($endpoint === 'search') {
            $announcementController->search();
        } elseif ($endpoint && is_numeric($endpoint)) {
            // endpoint is actually the ID
            $announcementController->getById($endpoint);
        } elseif ($endpoint === '' || $endpoint === 'all') {
            $announcementController->getAll();
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Announcement endpoint not found: ' . $endpoint]);
        }
        break;
    
    case 'POST':
        if ($endpoint === '' || $endpoint === 'create') {
            $announcementController->create();
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'POST endpoint not found: ' . $endpoint]);
        }
        break;
    
    case 'PUT':
        if ($endpoint && is_numeric($endpoint) && $id === 'archive') {
            // endpoint is actually the announcement ID, id is 'archive'
            $announcementController->archive($endpoint);
        } elseif ($endpoint && is_numeric($endpoint) && $id === 'unarchive') {
            // endpoint is actually the announcement ID, id is 'unarchive'
            $announcementController->unarchive($endpoint);
        } elseif ($endpoint && is_numeric($endpoint)) {
            // endpoint is actually the ID
            $announcementController->update($endpoint);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'PUT endpoint not found']);
        }
        break;
    
    case 'DELETE':
        if ($endpoint && is_numeric($endpoint)) {
            // endpoint is actually the ID
            $announcementController->delete($endpoint);
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
