<?php
class PrintJob {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function createJob($data) {
        $sql = "INSERT INTO print_jobs (machine_id, client_id, description, 
                    status, priority, estimated_duration, file_path) 
                VALUES (:machine_id, :client_id, :description, 
                    'queued', :priority, :estimated_duration, :file_path)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function getQueuedJobs() {
        $sql = "SELECT pj.*, m.name as machine_name, c.name as client_name 
                FROM print_jobs pj
                JOIN machines m ON pj.machine_id = m.id
                JOIN clients c ON pj.client_id = c.id
                WHERE pj.status = 'queued'
                ORDER BY pj.priority DESC, pj.created_at ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function scheduleJob($data) {
        $sql = "INSERT INTO print_jobs (
                    machine_id, client_id, title, description, 
                    scheduled_start, estimated_duration, priority, file_path, status
                ) VALUES (
                    :machine_id, :client_id, :title, :description,
                    :scheduled_start, :estimated_duration, :priority, :file_path, 'scheduled'
                )";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function getScheduledJobs($machineId = null) {
        $sql = "SELECT pj.*, c.name as client_name, m.name as machine_name 
                FROM print_jobs pj
                JOIN clients c ON pj.client_id = c.id
                JOIN machines m ON pj.machine_id = m.id
                WHERE pj.status = 'scheduled'";
        
        if ($machineId) {
            $sql .= " AND pj.machine_id = :machine_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['machine_id' => $machineId]);
        } else {
            $stmt = $this->db->query($sql);
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function calculateJobCost($data) {
        $basePrice = $this->getMaterialCost($data['material_id'], $data['quantity']);
        $urgencyMultiplier = $this->getUrgencyMultiplier($data['urgency']);
        $laborCost = $this->calculateLaborCost($data['estimated_duration'], $data['complexity']);
        
        return [
            'material_cost' => $basePrice,
            'labor_cost' => $laborCost,
            'urgency_fee' => ($basePrice + $laborCost) * ($urgencyMultiplier - 1),
            'total_cost' => ($basePrice + $laborCost) * $urgencyMultiplier
        ];
    }

    private function getUrgencyMultiplier($urgency) {
        switch ($urgency) {
            case 'high': return 1.5;
            case 'medium': return 1.2;
            default: return 1.0;
        }
    }

    private function calculateLaborCost($duration, $complexity) {
        $baseRate = 50; // hourly rate
        $complexityMultipliers = [
            'low' => 1.0,
            'medium' => 1.3,
            'high' => 1.8
        ];
        
        return ($duration / 60) * $baseRate * $complexityMultipliers[$complexity];
    }

    public function optimizeSchedule($machineId) {
        $sql = "WITH RECURSIVE schedule AS (
            SELECT id, scheduled_start, estimated_duration, priority,
                   scheduled_start + INTERVAL estimated_duration MINUTE as end_time
            FROM print_jobs
            WHERE machine_id = :machine_id 
            AND status = 'scheduled'
            ORDER BY priority DESC, scheduled_start
            LIMIT 1
        )
        SELECT * FROM schedule";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['machine_id' => $machineId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getJobFiles($jobId) {
        $sql = "SELECT * FROM job_files WHERE job_id = :job_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['job_id' => $jobId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
