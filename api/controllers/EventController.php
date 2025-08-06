<?php
/**
 * Event Controller
 * Handle event-related operations
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../models/Event.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

CorsConfig::setup();

class EventController
{
    private $eventModel;
    
    public function __construct()
    {
        $this->eventModel = new Event();
    }
    
    /**
     * Create new event (Officer only)
     */
    public function create()
    {
        try {
            AuthMiddleware::requireOfficer();
            
            $data = json_decode(file_get_contents("php://input"), true);
            
            // Required fields for event creation
            $requiredFields = [
                'event_name', 'event_date', 'event_time_start', 'event_time_end',
                'location', 'description'
            ];
            
            // Validate required fields
            $errors = Validator::validateRequired($data, $requiredFields);
            
            // Validate date format
            if (isset($data['event_date']) && !Validator::validateDate($data['event_date'])) {
                $errors['event_date'] = 'Invalid date format. Use YYYY-MM-DD';
            }
            
            // Validate time formats
            if (isset($data['event_time_start']) && !Validator::validateTime($data['event_time_start'])) {
                $errors['event_time_start'] = 'Invalid time format. Use HH:MM';
            }
            
            if (isset($data['event_time_end']) && !Validator::validateTime($data['event_time_end'])) {
                $errors['event_time_end'] = 'Invalid time format. Use HH:MM';
            }
            
            // Validate enum values
            if (isset($data['event_type']) && !Validator::validateEnum($data['event_type'], ['onsite', 'online', 'hybrid'])) {
                $errors['event_type'] = 'Invalid event type';
            }
            
            if (isset($data['event_status']) && !Validator::validateEnum($data['event_status'], ['upcoming', 'canceled', 'completed'])) {
                $errors['event_status'] = 'Invalid event status';
            }
            
            if (isset($data['event_restriction']) && !Validator::validateEnum($data['event_restriction'], ['public', 'members', 'officers'])) {
                $errors['event_restriction'] = 'Invalid event restriction';
            }
            
            if (!empty($errors)) {
                Response::validationError($errors);
            }
            
            // Set defaults
            $data['event_type'] = $data['event_type'] ?? 'onsite';
            $data['event_status'] = $data['event_status'] ?? 'upcoming';
            $data['event_restriction'] = $data['event_restriction'] ?? 'public';
            $data['registration_required'] = $data['registration_required'] ?? false;
            
            // Sanitize input data
            $sanitizedData = Validator::sanitize($data);
            
            // Create event
            $event = $this->eventModel->create($sanitizedData);
            
            Response::success($event, 'Event created successfully', 201);
            
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    /**
     * Get all events
     */
    public function getAll()
    {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            
            // Build filters
            $filters = [];
            if (isset($_GET['status'])) {
                $filters['status'] = $_GET['status'];
            }
            if (isset($_GET['type'])) {
                $filters['type'] = $_GET['type'];
            }
            if (isset($_GET['restriction'])) {
                $filters['restriction'] = $_GET['restriction'];
            }
            if (isset($_GET['date_from'])) {
                $filters['date_from'] = $_GET['date_from'];
            }
            if (isset($_GET['date_to'])) {
                $filters['date_to'] = $_GET['date_to'];
            }
            
            $events = $this->eventModel->getAll($page, $limit, $filters);
            
            Response::success([
                'events' => $events,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit
                ],
                'filters' => $filters
            ]);
            
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    /**
     * Get event by ID
     */
    public function getById($id)
    {
        try {
            $event = $this->eventModel->findById($id);
            
            if (!$event) {
                Response::notFound('Event not found');
            }
            
            Response::success($event);
            
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    /**
     * Update event (Officer only)
     */
    public function update($id)
    {
        try {
            AuthMiddleware::requireOfficer();
            
            $event = $this->eventModel->findById($id);
            if (!$event) {
                Response::notFound('Event not found');
            }
            
            $data = json_decode(file_get_contents("php://input"), true);
            
            // Required fields for event update
            $requiredFields = [
                'event_name', 'event_date', 'event_time_start', 'event_time_end',
                'location', 'description', 'event_status'
            ];
            
            // Validate required fields
            $errors = Validator::validateRequired($data, $requiredFields);
            
            // Validate date format
            if (isset($data['event_date']) && !Validator::validateDate($data['event_date'])) {
                $errors['event_date'] = 'Invalid date format. Use YYYY-MM-DD';
            }
            
            // Validate time formats
            if (isset($data['event_time_start']) && !Validator::validateTime($data['event_time_start'])) {
                $errors['event_time_start'] = 'Invalid time format. Use HH:MM';
            }
            
            if (isset($data['event_time_end']) && !Validator::validateTime($data['event_time_end'])) {
                $errors['event_time_end'] = 'Invalid time format. Use HH:MM';
            }
            
            // Validate enum values
            if (isset($data['event_type']) && !Validator::validateEnum($data['event_type'], ['onsite', 'online', 'hybrid'])) {
                $errors['event_type'] = 'Invalid event type';
            }
            
            if (isset($data['event_status']) && !Validator::validateEnum($data['event_status'], ['upcoming', 'canceled', 'completed'])) {
                $errors['event_status'] = 'Invalid event status';
            }
            
            if (isset($data['event_restriction']) && !Validator::validateEnum($data['event_restriction'], ['public', 'members', 'officers'])) {
                $errors['event_restriction'] = 'Invalid event restriction';
            }
            
            if (!empty($errors)) {
                Response::validationError($errors);
            }
            
            // Sanitize input data
            $sanitizedData = Validator::sanitize($data);
            
            // Update event
            $result = $this->eventModel->update($id, $sanitizedData);
            
            if ($result) {
                $updatedEvent = $this->eventModel->findById($id);
                Response::success($updatedEvent, 'Event updated successfully');
            } else {
                Response::serverError('Failed to update event');
            }
            
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    /**
     * Delete event (Officer only)
     */
    public function delete($id)
    {
        try {
            AuthMiddleware::requireOfficer();
            
            $event = $this->eventModel->findById($id);
            if (!$event) {
                Response::notFound('Event not found');
            }
            
            $result = $this->eventModel->delete($id);
            
            if ($result) {
                Response::success(null, 'Event deleted successfully');
            } else {
                Response::serverError('Failed to delete event');
            }
            
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    /**
     * Get upcoming events
     */
    public function getUpcoming()
    {
        try {
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $events = $this->eventModel->getUpcoming($limit);
            
            Response::success($events);
            
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    /**
     * Get calendar events
     */
    public function getCalendarEvents()
    {
        try {
            $startDate = $_GET['start'] ?? date('Y-m-01');
            $endDate = $_GET['end'] ?? date('Y-m-t');
            
            // Validate date formats
            if (!Validator::validateDate($startDate) || !Validator::validateDate($endDate)) {
                Response::validationError(['date' => 'Invalid date format. Use YYYY-MM-DD']);
            }
            
            $events = $this->eventModel->getCalendarEvents($startDate, $endDate);
            
            Response::success($events);
            
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    /**
     * Register for event
     */
    public function register($id)
    {
        try {
            $userId = AuthMiddleware::authenticate();
            
            $event = $this->eventModel->findById($id);
            if (!$event) {
                Response::notFound('Event not found');
            }
            
            $result = $this->eventModel->registerStudent($id, $userId);
            
            if ($result) {
                Response::success(null, 'Successfully registered for event');
            } else {
                Response::serverError('Failed to register for event');
            }
            
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'already registered') !== false) {
                Response::error($e->getMessage(), 409);
            } else {
                Response::serverError($e->getMessage());
            }
        }
    }
    
    /**
     * Get event registrations (Officer only)
     */
    public function getRegistrations($id)
    {
        try {
            AuthMiddleware::requireOfficer();
            
            $event = $this->eventModel->findById($id);
            if (!$event) {
                Response::notFound('Event not found');
            }
            
            $registrations = $this->eventModel->getRegistrations($id);
            
            Response::success([
                'event' => $event,
                'registrations' => $registrations,
                'total_registered' => count($registrations)
            ]);
            
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    /**
     * Update attendance status (Officer only)
     */
    public function updateAttendance($eventId, $studentId)
    {
        try {
            AuthMiddleware::requireOfficer();
            
            $data = json_decode(file_get_contents("php://input"), true);
            
            // Validate required fields
            $errors = Validator::validateRequired($data, ['attendance_status']);
            if (!empty($errors)) {
                Response::validationError($errors);
            }
            
            // Validate status enum
            if (!Validator::validateEnum($data['attendance_status'], ['registered', 'attended', 'absent'])) {
                Response::validationError(['attendance_status' => 'Invalid attendance status']);
            }
            
            $result = $this->eventModel->updateAttendanceStatus($eventId, $studentId, $data['attendance_status']);
            
            if ($result) {
                Response::success(null, 'Attendance status updated successfully');
            } else {
                Response::serverError('Failed to update attendance status');
            }
            
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
}
