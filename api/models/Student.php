<?php
/**
 * Student Model
 * Handle all student-related database operations
 */

require_once __DIR__ . '/../config/database.php';

class Student
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Create a new student
     */
    public function create($data)
    {
        try {
            // Check if username or email already exists
            $checkStmt = $this->db->prepare("SELECT id FROM students WHERE username = :username OR email = :email");
            $checkStmt->execute([
                'username' => $data['username'],
                'email' => $data['email']
            ]);
            
            if ($checkStmt->fetch()) {
                throw new Exception("Username or email already exists");
            }
            
            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Insert student
            $sql = "INSERT INTO students (
                username, email, password, first_name, middle_name, last_name, name_suffix,
                birthdate, gender, student_no, year_level, college, program,
                section, address, phone, facebook_link, role, is_active
            ) VALUES (
                :username, :email, :password, :first_name, :middle_name, :last_name, :name_suffix,
                :birthdate, :gender, :student_no, :year_level, :college, :program,
                :section, :address, :phone, :facebook_link, :role, :is_active
            )";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $hashedPassword,
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'],
                'last_name' => $data['last_name'],
                'name_suffix' => $data['name_suffix'],
                'birthdate' => $data['birthdate'],
                'gender' => $data['gender'],
                'student_no' => $data['student_no'],
                'year_level' => $data['year_level'],
                'college' => $data['college'],
                'program' => $data['program'],
                'section' => $data['section'],
                'address' => $data['address'],
                'phone' => $data['phone'],
                'facebook_link' => $data['facebook_link'],
                'role' => $data['role'] ?? 'member',
                'is_active' => true
            ]);
            
            $userId = $this->db->lastInsertId();
            
            // Generate MSC ID
            $mscId = $this->generateMscId($data['role'] ?? 'member');
            
            // Update student with MSC ID
            $updateStmt = $this->db->prepare("UPDATE students SET msc_id = :msc_id WHERE id = :id");
            $updateStmt->execute(['msc_id' => $mscId, 'id' => $userId]);
            
            return $this->findById($userId);
            
        } catch (Exception $e) {
            throw new Exception("Failed to create student: " . $e->getMessage());
        }
    }
    
    /**
     * Find student by ID
     */
    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM students WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    /**
     * Find student by username
     */
    public function findByUsername($username)
    {
        $stmt = $this->db->prepare("SELECT * FROM students WHERE username = :username");
        $stmt->execute(['username' => $username]);
        return $stmt->fetch();
    }
    
    /**
     * Find student by email
     */
    public function findByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM students WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }
    
    /**
     * Update student profile
     */
    public function updateProfile($id, $data)
    {
        try {
            $sql = "UPDATE students SET 
                first_name = :first_name,
                middle_name = :middle_name,
                last_name = :last_name,
                name_suffix = :name_suffix,
                birthdate = :birthdate,
                gender = :gender,
                student_no = :student_no,
                year_level = :year_level,
                college = :college,
                program = :program,
                section = :section,
                address = :address,
                phone = :phone,
                facebook_link = :facebook_link,
                profile_image_path = :profile_image_path
                WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'id' => $id,
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'],
                'last_name' => $data['last_name'],
                'name_suffix' => $data['name_suffix'],
                'birthdate' => $data['birthdate'],
                'gender' => $data['gender'],
                'student_no' => $data['student_no'],
                'year_level' => $data['year_level'],
                'college' => $data['college'],
                'program' => $data['program'],
                'section' => $data['section'],
                'address' => $data['address'],
                'phone' => $data['phone'],
                'facebook_link' => $data['facebook_link'],
                'profile_image_path' => $data['profile_image_path'] ?? null
            ]);
        } catch (Exception $e) {
            throw new Exception("Failed to update profile: " . $e->getMessage());
        }
    }
    
    /**
     * Change password
     */
    public function changePassword($id, $newPassword)
    {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE students SET password = :password WHERE id = :id");
            return $stmt->execute(['password' => $hashedPassword, 'id' => $id]);
        } catch (Exception $e) {
            throw new Exception("Failed to change password: " . $e->getMessage());
        }
    }
    
    /**
     * Verify password
     */
    public function verifyPassword($hashedPassword, $password)
    {
        return password_verify($password, $hashedPassword);
    }
    
    /**
     * Get all students with pagination
     */
    public function getAll($page = 1, $limit = 20, $role = null)
    {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT * FROM students";
        $params = [];
        
        if ($role) {
            $sql .= " WHERE role = :role";
            $params['role'] = $role;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Generate MSC ID
     */
    private function generateMscId($role)
    {
        // Get school year code
        $syStmt = $this->db->prepare("SELECT value FROM settings WHERE key_name = 'school_year_code'");
        $syStmt->execute();
        $schoolYearCode = $syStmt->fetchColumn() ?: '2526';
        
        if ($role === 'officer') {
            // Count officers for this school year
            $countStmt = $this->db->prepare("SELECT COUNT(*) FROM students WHERE role = 'officer' AND msc_id LIKE CONCAT('MSC', :sy, 'EB-%')");
            $countStmt->execute(['sy' => $schoolYearCode]);
            $officerNumber = $countStmt->fetchColumn() + 1;
            return sprintf("MSC%sEB-%03d", $schoolYearCode, $officerNumber);
        } else {
            // Count members
            $countStmt = $this->db->prepare("SELECT COUNT(*) FROM students WHERE role = 'member'");
            $countStmt->execute();
            $memberNumber = $countStmt->fetchColumn() + 1;
            return sprintf("MSC-%04d", $memberNumber);
        }
    }
    
    /**
     * Toggle student active status
     */
    public function toggleActive($id)
    {
        try {
            $stmt = $this->db->prepare("UPDATE students SET is_active = NOT is_active WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (Exception $e) {
            throw new Exception("Failed to toggle active status: " . $e->getMessage());
        }
    }
}
