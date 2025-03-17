<?php
class Budget {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function createBudget($data) {
        $this->db->beginTransaction();
        try {
            $sql = "INSERT INTO budgets (client_id, total_amount, urgency_level, status, valid_until) 
                    VALUES (:client_id, :total_amount, :urgency_level, 'pending', 
                            DATE_ADD(NOW(), INTERVAL 15 DAY))";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($data);
            $budgetId = $this->db->lastInsertId();
            
            foreach ($data['items'] as $item) {
                $this->addBudgetItem($budgetId, $item);
            }
            
            $this->db->commit();
            return $budgetId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function calculatePrice($type, $quantity, $size = null) {
        $basePrice = $this->getPriceFromTable($type);
        $quantityDiscount = $this->calculateQuantityDiscount($quantity);
        $sizeMultiplier = $size ? ($size / 100) : 1;
        
        return $basePrice * $quantity * $sizeMultiplier * $quantityDiscount;
    }

    private function calculateUrgencyMultiplier($urgencyLevel) {
        switch ($urgencyLevel) {
            case 'high': return 1.5;
            case 'medium': return 1.2;
            case 'low': return 1.0;
            default: return 1.0;
        }
    }

    public function calculateTotal($items, $urgencyLevel) {
        $total = 0;
        foreach ($items as $item) {
            $total += $this->calculateItemPrice($item);
        }
        return $total * $this->calculateUrgencyMultiplier($urgencyLevel);
    }
}
