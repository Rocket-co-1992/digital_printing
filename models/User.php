<?php
class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function authenticate($email, $password) {
        $sql = "SELECT * FROM users WHERE email = :email AND active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']);
            return $user;
        }
        return false;
    }

    public function getUserPermissions($userId) {
        $sql = "SELECT p.name FROM permissions p 
                JOIN role_permissions rp ON p.id = rp.permission_id 
                JOIN user_roles ur ON rp.role_id = ur.role_id 
                WHERE ur.user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function createSession($user) {
        $token = bin2hex(random_bytes(32));
        $sql = "INSERT INTO user_sessions (user_id, token, expires_at) 
                VALUES (:user_id, :token, DATE_ADD(NOW(), INTERVAL 24 HOUR))";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $user['id'],
            'token' => $token
        ]);
        return $token;
    }

    public function validateSession($token) {
        $sql = "SELECT u.* FROM users u 
                JOIN user_sessions s ON u.id = s.user_id 
                WHERE s.token = :token AND s.expires_at > NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['token' => $token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function destroySession($token) {
        $sql = "DELETE FROM user_sessions WHERE token = :token";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['token' => $token]);
    }

    public function hasRole($userId, $roleName) {
        $sql = "SELECT COUNT(*) FROM user_roles ur 
                JOIN roles r ON ur.role_id = r.id 
                WHERE ur.user_id = :user_id AND r.name = :role_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId, 'role_name' => $roleName]);
        return $stmt->fetchColumn() > 0;
    }

    public function hasPermission($userId, $permissionName) {
        $sql = "SELECT COUNT(*) FROM user_roles ur 
                JOIN role_permissions rp ON ur.role_id = rp.role_id 
                JOIN permissions p ON rp.permission_id = p.id 
                WHERE ur.user_id = :user_id AND p.name = :permission_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId, 'permission_name' => $permissionName]);
        return $stmt->fetchColumn() > 0;
    }
}
