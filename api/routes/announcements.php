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

// Remove 'api' and 'announcements' from path segments to get the actual endpoint
$endpoint = $pathSegments[2] ?? '';
$id = $pathSegments[3] ?? null;
$action = $pathSegments[4] ?? null;

switch ($method) {
    case 'GET':
        if ($endpoint === 'recent') {
            $announcementController->getRecent();
        } elseif ($endpoint === 'search') {
            $announcementController->search();
        } elseif ($id) {
            $announcementController->getById($id);
        } else {
            $announcementController->getAll();
        }
        break;
    
    case 'POST':
        if (!$id) {
            $announcementController->create();
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
        }
        break;
    
    case 'PUT':
        if ($id && $action === 'archive') {
            $announcementController->archive($id);
        } elseif ($id && $action === 'unarchive') {
            $announcementController->unarchive($id);
        } elseif ($id) {
            $announcementController->update($id);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
        }
        break;
    
    case 'DELETE':
        if ($id) {
            $announcementController->delete($id);
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
