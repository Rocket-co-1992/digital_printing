<?php
class Client {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function createClient($data) {
        $sql = "INSERT INTO clients (name, email, phone, company_id, tax_number) 
                VALUES (:name, :email, :phone, :company_id, :tax_number)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function getClients() {
        $sql = "SELECT c.*, comp.name as company_name 
                FROM clients c 
                LEFT JOIN companies comp ON c.company_id = comp.id 
                ORDER BY c.name";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getClientById($id) {
        $sql = "SELECT * FROM clients WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateClient($id, $data) {
        $sql = "UPDATE clients 
                SET name = :name, 
                    email = :email, 
                    phone = :phone, 
                    company_id = :company_id, 
                    tax_number = :tax_number 
                WHERE id = :id";
        $data['id'] = $id;
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function deleteClient($id) {
        $sql = "DELETE FROM clients WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    public function getClientWithDetails($id) {
        $sql = "SELECT c.*, comp.name as company_name 
                FROM clients c 
                LEFT JOIN companies comp ON c.company_id = comp.id 
                WHERE c.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
