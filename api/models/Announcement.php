<?php
/**
 * Announcement Model
 * Handle all announcement-related database operations
 */

require_once __DIR__ . '/../config/database.php';

class Announcement
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Create a new announcement
     */
    public function create($data)
    {
        try {
            $sql = "INSERT INTO announcements (title, content, posted_by) 
                    VALUES (:title, :content, :posted_by)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'title' => $data['title'],
                'content' => $data['content'],
                'posted_by' => $data['posted_by'] ?? 'Admin'
            ]);
            
            return $this->findById($this->db->lastInsertId());
        } catch (Exception $e) {
            throw new Exception("Failed to create announcement: " . $e->getMessage());
        }
    }
    
    /**
     * Find announcement by ID
     */
    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM announcements WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    /**
     * Get all announcements with pagination
     */
    public function getAll($page = 1, $limit = 20, $includeArchived = false)
    {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT * FROM announcements";
        $params = [];
        
        if (!$includeArchived) {
            $sql .= " WHERE is_archived = 0";
        }
        
        $sql .= " ORDER BY date_posted DESC LIMIT :limit OFFSET :offset";
        
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
     * Update announcement
     */
    public function update($id, $data)
    {
        try {
            $sql = "UPDATE announcements SET 
                    title = :title,
                    content = :content,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'id' => $id,
                'title' => $data['title'],
                'content' => $data['content']
            ]);
        } catch (Exception $e) {
            throw new Exception("Failed to update announcement: " . $e->getMessage());
        }
    }
    
    /**
     * Archive announcement
     */
    public function archive($id)
    {
        try {
            $stmt = $this->db->prepare("UPDATE announcements SET is_archived = 1, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (Exception $e) {
            throw new Exception("Failed to archive announcement: " . $e->getMessage());
        }
    }
    
    /**
     * Unarchive announcement
     */
    public function unarchive($id)
    {
        try {
            $stmt = $this->db->prepare("UPDATE announcements SET is_archived = 0, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (Exception $e) {
            throw new Exception("Failed to unarchive announcement: " . $e->getMessage());
        }
    }
    
    /**
     * Delete announcement
     */
    public function delete($id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM announcements WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (Exception $e) {
            throw new Exception("Failed to delete announcement: " . $e->getMessage());
        }
    }
    
    /**
     * Get recent announcements
     */
    public function getRecent($limit = 5)
    {
        $sql = "SELECT * FROM announcements 
                WHERE is_archived = 0 
                ORDER BY date_posted DESC 
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Search announcements
     */
    public function search($query, $page = 1, $limit = 20)
    {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT * FROM announcements 
                WHERE (title LIKE :query OR content LIKE :query) 
                AND is_archived = 0
                ORDER BY date_posted DESC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':query', '%' . $query . '%');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get total count of announcements
     */
    public function getTotalCount($includeArchived = false)
    {
        $sql = "SELECT COUNT(*) FROM announcements";
        
        if (!$includeArchived) {
            $sql .= " WHERE is_archived = 0";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchColumn();
    }
}
