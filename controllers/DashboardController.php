<?php
class DashboardController {
    private $twig;
    private $machineModel;
    private $budgetModel;
    private $stockModel;

    public function __construct($twig) {
        $this->twig = $twig;
        $this->machineModel = new Machine();
        $this->budgetModel = new Budget();
        $this->stockModel = new Stock();
    }

    public function index() {
        $data = [
            'upcoming_maintenance' => $this->machineModel->getMaintenanceSchedule(),
            'low_stock_items' => $this->stockModel->getLowStockItems(),
            'pending_budgets' => $this->budgetModel->getPendingBudgets(),
            'machine_statistics' => $this->getMachineStatistics()
        ];
        
        echo $this->twig->render('dashboard/index.twig', $data);
    }

    private function getMachineStatistics() {
        $machines = $this->machineModel->getMachines();
        $stats = [];
        
        foreach ($machines as $machine) {
            $stats[$machine['id']] = $this->machineModel->getUsageStatistics($machine['id']);
        }
        
        return $stats;
    }
}
