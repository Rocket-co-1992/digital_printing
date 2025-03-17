<?php
class NotificationService {
    private $db;
    private $websocket;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->initializeWebSocket();
    }

    public function sendNotification($userId, $type, $message, $data = []) {
        $sql = "INSERT INTO notifications (user_id, type, message, data, created_at) 
                VALUES (:user_id, :type, :message, :data, NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'type' => $type,
            'message' => $message,
            'data' => json_encode($data)
        ]);

        $this->pushToWebSocket($userId, [
            'type' => $type,
            'message' => $message,
            'data' => $data
        ]);
    }
}
