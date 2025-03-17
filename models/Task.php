<?php
class Task {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function createTask($data) {
        $sql = "INSERT INTO tasks (title, description, status, assigned_to, due_date) 
                VALUES (:title, :description, :status, :assigned_to, :due_date)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function updateTaskStatus($taskId, $status) {
        $sql = "UPDATE tasks SET status = :status WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['status' => $status, 'id' => $taskId]);
    }

    public function getTasks() {
        $sql = "SELECT t.*, u.name as assigned_name 
                FROM tasks t 
                LEFT JOIN users u ON t.assigned_to = u.id 
                ORDER BY t.created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
