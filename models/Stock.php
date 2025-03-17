<?php
class Stock {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getStockItems() {
        $sql = "SELECT s.*, 
                    COALESCE(sm_in.total_in, 0) - COALESCE(sm_out.total_out, 0) as current_quantity 
                FROM stock_items s 
                LEFT JOIN (
                    SELECT item_id, SUM(quantity) as total_in 
                    FROM stock_movements 
                    WHERE type = 'in' 
                    GROUP BY item_id
                ) sm_in ON s.id = sm_in.item_id 
                LEFT JOIN (
                    SELECT item_id, SUM(quantity) as total_out 
                    FROM stock_movements 
                    WHERE type = 'out' 
                    GROUP BY item_id
                ) sm_out ON s.id = sm_out.item_id";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addStockItem($data) {
        $sql = "INSERT INTO stock_items (name, description, minimum_quantity, unit) 
                VALUES (:name, :description, :minimum_quantity, :unit)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function recordMovement($data) {
        $sql = "INSERT INTO stock_movements (item_id, type, quantity, notes, user_id) 
                VALUES (:item_id, :type, :quantity, :notes, :user_id)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }
}
