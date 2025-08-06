<?php
/**
 * Announcement Controller
 * Handle announcement-related operations
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../models/Announcement.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

CorsConfig::setup();

class AnnouncementController
{
    private $announcementModel;
    
    public function __construct()
    {
        $this->announcementModel = new Announcement();
    }
    
    /**
     * Create new announcement (Officer only)
     */
    public function create()
    {
        try {
            AuthMiddleware::requireOfficer();
            
            $data = json_decode(file_get_contents("php://input"), true);
            
            // Required fields for announcement creation
            $requiredFields = ['title', 'content'];
            
            // Validate required fields
            $errors = Validator::validateRequired($data, $requiredFields);
            if (!empty($errors)) {
                Response::validationError($errors);
            }
            
            // Get current user info for posted_by
            $currentUser = AuthMiddleware::getCurrentUser();
            $data['posted_by'] = $currentUser['username'] ?? 'Admin';
            
            // Sanitize input data
            $sanitizedData = Validator::sanitize($data);
            
            // Create announcement
            $announcement = $this->announcementModel->create($sanitizedData);
            
            Response::success($announcement, 'Announcement created successfully', 201);
            
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    /**
     * Get all announcements
     */
    public function getAll()
    {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $includeArchived = isset($_GET['include_archived']) && $_GET['include_archived'] === 'true';
            
            // Only officers can view archived announcements
            if ($includeArchived) {
                AuthMiddleware::requireOfficer();
            }
            
            $announcements = $this->announcementModel->getAll($page, $limit, $includeArchived);
            $totalCount = $this->announcementModel->getTotalCount($includeArchived);
            
            Response::success([
                'announcements' => $announcements,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $totalCount,
                    'total_pages' => ceil($totalCount / $limit)
                ]
            ]);
            
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    /**
     * Get announcement by ID
     */
    public function getById($id)
    {
        try {
            $announcement = $this->announcementModel->findById($id);
            
            if (!$announcement) {
                Response::notFound('Announcement not found');
            }
            
            // Check if announcement is archived and user has permission
            if ($announcement['is_archived']) {
                AuthMiddleware::requireOfficer();
            }
            
            Response::success($announcement);
            
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    /**
     * Update announcement (Officer only)
     */
    public function update($id)
    {
        try {
            AuthMiddleware::requireOfficer();
            
            $announcement = $this->announcementModel->findById($id);
            if (!$announcement) {
                Response::notFound('Announcement not found');
            }
            
            $data = json_decode(file_get_contents("php://input"), true);
            
            // Required fields for announcement update
            $requiredFields = ['title', 'content'];
            
            // Validate required fields
            $errors = Validator::validateRequired($data, $requiredFields);
            if (!empty($errors)) {
                Response::validationError($errors);
            }
            
            // Sanitize input data
            $sanitizedData = Validator::sanitize($data);
            
            // Update announcement
            $result = $this->announcementModel->update($id, $sanitizedData);
            
            if ($result) {
                $updatedAnnouncement = $this->announcementModel->findById($id);
                Response::success($updatedAnnouncement, 'Announcement updated successfully');
            } else {
                Response::serverError('Failed to update announcement');
            }
            
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    /**
     * Archive announcement (Officer only)
     */
    public function archive($id)
    {
        try {
            AuthMiddleware::requireOfficer();
            
            $announcement = $this->announcementModel->findById($id);
            if (!$announcement) {
                Response::notFound('Announcement not found');
            }
            
            if ($announcement['is_archived']) {
                Response::error('Announcement is already archived', 400);
            }
            
            $result = $this->announcementModel->archive($id);
            
            if ($result) {
                Response::success(null, 'Announcement archived successfully');
            } else {
                Response::serverError('Failed to archive announcement');
            }
            
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    /**
     * Unarchive announcement (Officer only)
     */
    public function unarchive($id)
    {
        try {
            AuthMiddleware::requireOfficer();
            
            $announcement = $this->announcementModel->findById($id);
            if (!$announcement) {
                Response::notFound('Announcement not found');
            }
            
            if (!$announcement['is_archived']) {
                Response::error('Announcement is not archived', 400);
            }
            
            $result = $this->announcementModel->unarchive($id);
            
            if ($result) {
                Response::success(null, 'Announcement unarchived successfully');
            } else {
                Response::serverError('Failed to unarchive announcement');
            }
            
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    /**
     * Delete announcement (Officer only)
     */
    public function delete($id)
    {
        try {
            AuthMiddleware::requireOfficer();
            
            $announcement = $this->announcementModel->findById($id);
            if (!$announcement) {
                Response::notFound('Announcement not found');
            }
            
            $result = $this->announcementModel->delete($id);
            
            if ($result) {
                Response::success(null, 'Announcement deleted successfully');
            } else {
                Response::serverError('Failed to delete announcement');
            }
            
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    /**
     * Get recent announcements
     */
    public function getRecent()
    {
        try {
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
            $announcements = $this->announcementModel->getRecent($limit);
            
            Response::success($announcements);
            
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    /**
     * Search announcements
     */
    public function search()
    {
        try {
            $query = isset($_GET['q']) ? trim($_GET['q']) : '';
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            
            if (empty($query)) {
                Response::validationError(['q' => 'Search query is required']);
            }
            
            $announcements = $this->announcementModel->search($query, $page, $limit);
            
            Response::success([
                'announcements' => $announcements,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'query' => $query
                ]
            ]);
            
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
}
