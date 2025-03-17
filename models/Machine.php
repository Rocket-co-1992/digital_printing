<?php
class Machine {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getMachines() {
        $sql = "SELECT m.*, 
                    (SELECT COUNT(*) FROM maintenance_records mr 
                     WHERE mr.machine_id = m.id AND mr.status = 'pending') as pending_maintenance
                FROM machines m
                ORDER BY m.name";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createMachine($data) {
        $sql = "INSERT INTO machines (name, type, model, serial_number, 
                    purchase_date, maintenance_interval) 
                VALUES (:name, :type, :model, :serial_number, 
                    :purchase_date, :maintenance_interval)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function recordMaintenance($data) {
        $sql = "INSERT INTO maintenance_records (machine_id, type, description, 
                    date_performed, performed_by, status) 
                VALUES (:machine_id, :type, :description, 
                    :date_performed, :performed_by, :status)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function getMaintenanceSchedule() {
        $sql = "SELECT m.*, 
                DATE_ADD(COALESCE(
                    (SELECT MAX(date_performed) 
                     FROM maintenance_records 
                     WHERE machine_id = m.id), 
                    m.purchase_date
                ), INTERVAL m.maintenance_interval DAY) as next_maintenance
                FROM machines m
                HAVING next_maintenance <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                ORDER BY next_maintenance";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUsageStatistics($machineId, $period = 30) {
        $sql = "SELECT 
                    COUNT(*) as total_jobs,
                    SUM(duration) as total_duration,
                    AVG(duration) as avg_duration
                FROM machine_usage 
                WHERE machine_id = :machine_id 
                AND date_used >= DATE_SUB(CURDATE(), INTERVAL :period DAY)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['machine_id' => $machineId, 'period' => $period]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getMaintenanceReport($startDate, $endDate) {
        $sql = "SELECT m.name, m.type, mr.type as maintenance_type,
                    mr.date_performed, mr.description, u.name as performed_by
                FROM maintenance_records mr
                JOIN machines m ON mr.machine_id = m.id
                JOIN users u ON mr.performed_by = u.id
                WHERE mr.date_performed BETWEEN :start_date AND :end_date
                ORDER BY mr.date_performed DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['start_date' => $startDate, 'end_date' => $endDate]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEfficiencyReport($machineId, $period = 30) {
        $sql = "SELECT 
                    DATE(date_used) as usage_date,
                    COUNT(*) as jobs_count,
                    SUM(duration) as total_duration,
                    SUM(pages_printed) as total_pages
                FROM machine_usage 
                WHERE machine_id = :machine_id 
                AND date_used >= DATE_SUB(CURDATE(), INTERVAL :period DAY)
                GROUP BY DATE(date_used)
                ORDER BY date_used";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['machine_id' => $machineId, 'period' => $period]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMachinesNeedingMaintenance() {
        $sql = "SELECT m.*, 
                DATE_ADD(COALESCE(
                    (SELECT MAX(date_performed) 
                     FROM maintenance_records 
                     WHERE machine_id = m.id), 
                    m.purchase_date
                ), INTERVAL m.maintenance_interval DAY) as maintenance_due
                FROM machines m
                HAVING maintenance_due <= CURDATE()";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getResponsibleUsers($machineId) {
        $sql = "SELECT u.* FROM users u
                JOIN machine_responsibilities mr ON u.id = mr.user_id
                WHERE mr.machine_id = :machine_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['machine_id' => $machineId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCurrentJob($machineId) {
        $sql = "SELECT pj.*, c.name as client_name 
                FROM print_jobs pj
                JOIN clients c ON pj.client_id = c.id
                WHERE pj.machine_id = :machine_id 
                AND pj.status = 'processing'
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['machine_id' => $machineId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function startNextJob($machineId) {
        $sql = "UPDATE print_jobs 
                SET status = 'processing', started_at = NOW()
                WHERE id = (
                    SELECT id FROM (
                        SELECT id FROM print_jobs 
                        WHERE machine_id = :machine_id AND status = 'queued'
                        ORDER BY priority DESC, created_at ASC
                        LIMIT 1
                    ) AS sub
                )";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['machine_id' => $machineId]);
    }

    public function getMachineAvailability($machineId, $date) {
        $sql = "SELECT TIME_FORMAT(time_slot, '%H:%i') as slot,
                    CASE WHEN pj.id IS NULL THEN true ELSE false END as available
                FROM (
                    SELECT DATE_FORMAT(:date, '%Y-%m-%d ') + INTERVAL (n*30) MINUTE as time_slot
                    FROM (
                        SELECT a.N + b.N*10 + c.N*100 as n
                        FROM (SELECT 0 as N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3) a
                        CROSS JOIN (SELECT 0 as N UNION SELECT 1 UNION SELECT 2 UNION SELECT 3) b
                        CROSS JOIN (SELECT 0 as N) c
                    ) numbers
                    WHERE DATE_FORMAT(:date, '%Y-%m-%d ') + INTERVAL (n*30) MINUTE < DATE_FORMAT(:date, '%Y-%m-%d 23:59:59')
                ) slots
                LEFT JOIN print_jobs pj ON pj.machine_id = :machine_id 
                    AND pj.scheduled_start <= slots.time_slot 
                    AND DATE_ADD(pj.scheduled_start, INTERVAL pj.estimated_duration MINUTE) > slots.time_slot
                ORDER BY time_slot";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['machine_id' => $machineId, 'date' => $date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPredictedMaintenance($machineId) {
        $sql = "SELECT 
                    AVG(DATEDIFF(mr2.date_performed, mr1.date_performed)) as avg_days_between,
                    COUNT(*) as failure_count,
                    MAX(mr1.date_performed) as last_failure
                FROM maintenance_records mr1
                LEFT JOIN maintenance_records mr2 ON mr1.machine_id = mr2.machine_id 
                    AND mr1.date_performed < mr2.date_performed
                WHERE mr1.machine_id = :machine_id 
                AND mr1.type = 'repair'
                GROUP BY mr1.machine_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['machine_id' => $machineId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function calculateWorkloadScore($machineId) {
        $sql = "SELECT 
                    (COUNT(*) / 30) * 100 as usage_percentage,
                    AVG(duration) as avg_job_duration,
                    STD(duration) as std_duration
                FROM print_jobs
                WHERE machine_id = :machine_id 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['machine_id' => $machineId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
