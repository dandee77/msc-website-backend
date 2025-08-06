<?php
/**
 * Student Controller
 * Handle student-related operations
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

CorsConfig::setup();

class StudentController
{
    private $studentModel;
    
    public function __construct()
    {
        $this->studentModel = new Student();
    }
    
    /**
     * Get all students (Officer only)
     */
    public function getAll()
    {
        try {
            AuthMiddleware::requireOfficer();
            
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $role = isset($_GET['role']) ? $_GET['role'] : null;
            
            // Validate role if provided
            if ($role && !Validator::validateEnum($role, ['member', 'officer'])) {
                Response::validationError(['role' => 'Invalid role value']);
            }
            
            $students = $this->studentModel->getAll($page, $limit, $role);
            
            // Remove passwords from response
            $students = array_map(function($student) {
                unset($student['password']);
                return $student;
            }, $students);
            
            Response::success([
                'students' => $students,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit
                ]
            ]);
            
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    /**
     * Get student by ID
     */
    public function getById($id)
    {
        try {
            $currentUserId = AuthMiddleware::authenticate();
            
            // Students can only view their own profile, officers can view any
            $currentUser = AuthMiddleware::getCurrentUser();
            if ($currentUser['role'] !== 'officer' && $currentUserId != $id) {
                Response::error('Insufficient privileges', 403);
            }
            
            $student = $this->studentModel->findById($id);
            
            if (!$student) {
                Response::notFound('Student not found');
            }
            
            // Remove password from response
            unset($student['password']);
            
            Response::success($student);
            
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    /**
     * Update student profile
     */
    public function updateProfile($id)
    {
        try {
            $currentUserId = AuthMiddleware::authenticate();
            
            // Students can only update their own profile, officers can update any
            $currentUser = AuthMiddleware::getCurrentUser();
            if ($currentUser['role'] !== 'officer' && $currentUserId != $id) {
                Response::error('Insufficient privileges', 403);
            }
            
            $data = json_decode(file_get_contents("php://input"), true);
            
            // Required fields for profile update
            $requiredFields = [
                'first_name', 'last_name', 'birthdate', 'gender',
                'student_no', 'year_level', 'college', 'program'
            ];
            
            // Validate required fields
            $errors = Validator::validateRequired($data, $requiredFields);
            
            // Validate date format
            if (isset($data['birthdate']) && !Validator::validateDate($data['birthdate'])) {
                $errors['birthdate'] = 'Invalid date format. Use YYYY-MM-DD';
            }
            
            // Validate gender enum
            if (isset($data['gender']) && !Validator::validateEnum($data['gender'], ['Male', 'Female', 'Other'])) {
                $errors['gender'] = 'Invalid gender value';
            }
            
            // Validate phone if provided
            if (!empty($data['phone']) && !Validator::validatePhone($data['phone'])) {
                $errors['phone'] = 'Invalid phone number format';
            }
            
            if (!empty($errors)) {
                Response::validationError($errors);
            }
            
            // Sanitize input data
            $sanitizedData = Validator::sanitize($data);
            
            // Update profile
            $result = $this->studentModel->updateProfile($id, $sanitizedData);
            
            if ($result) {
                $updatedStudent = $this->studentModel->findById($id);
                unset($updatedStudent['password']);
                Response::success($updatedStudent, 'Profile updated successfully');
            } else {
                Response::serverError('Failed to update profile');
            }
            
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    /**
     * Toggle student active status (Officer only)
     */
    public function toggleActive($id)
    {
        try {
            AuthMiddleware::requireOfficer();
            
            $student = $this->studentModel->findById($id);
            if (!$student) {
                Response::notFound('Student not found');
            }
            
            $result = $this->studentModel->toggleActive($id);
            
            if ($result) {
                $updatedStudent = $this->studentModel->findById($id);
                unset($updatedStudent['password']);
                
                $status = $updatedStudent['is_active'] ? 'activated' : 'deactivated';
                Response::success($updatedStudent, "Student account {$status} successfully");
            } else {
                Response::serverError('Failed to update student status');
            }
            
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    /**
     * Get dashboard data for current user
     */
    public function getDashboardData()
    {
        try {
            $userId = AuthMiddleware::authenticate();
            $currentUser = AuthMiddleware::getCurrentUser();
            
            $dashboardData = [];
            
            if ($currentUser['role'] === 'officer') {
                // Officer dashboard data
                // Get total counts
                $totalMembers = count($this->studentModel->getAll(1, 1000, 'member'));
                $totalOfficers = count($this->studentModel->getAll(1, 1000, 'officer'));
                
                $dashboardData = [
                    'total_members' => $totalMembers,
                    'total_officers' => $totalOfficers,
                    'total_students' => $totalMembers + $totalOfficers,
                    'user_role' => 'officer'
                ];
            } else {
                // Member dashboard data
                $student = $this->studentModel->findById($userId);
                unset($student['password']);
                
                $dashboardData = [
                    'profile' => $student,
                    'user_role' => 'member'
                ];
            }
            
            Response::success($dashboardData);
            
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
    
    /**
     * Search students (Officer only)
     */
    public function search()
    {
        try {
            AuthMiddleware::requireOfficer();
            
            $query = isset($_GET['q']) ? trim($_GET['q']) : '';
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            
            if (empty($query)) {
                Response::validationError(['q' => 'Search query is required']);
            }
            
            // Simple search implementation - can be enhanced with full-text search
            $offset = ($page - 1) * $limit;
            
            // This would need to be implemented in the Student model
            // For now, returning empty results
            Response::success([
                'students' => [],
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'query' => $query
                ]
            ], 'Search functionality coming soon');
            
        } catch (Exception $e) {
            Response::serverError($e->getMessage());
        }
    }
}
