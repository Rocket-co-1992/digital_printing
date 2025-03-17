<?php
class MachineController {
    private $twig;
    private $machineModel;
    private $emailService;

    public function __construct($twig) {
        $this->twig = $twig;
        $this->machineModel = new Machine();
        $this->emailService = new EmailService($twig);
    }

    public function index() {
        $machines = $this->machineModel->getMachines();
        echo $this->twig->render('machines/index.twig', ['machines' => $machines]);
    }

    public function checkMaintenanceStatus() {
        $machines = $this->machineModel->getMachinesNeedingMaintenance();
        
        foreach ($machines as $machine) {
            $responsibleUsers = $this->machineModel->getResponsibleUsers($machine['id']);
            $this->emailService->sendMaintenanceAlert($machine, $responsibleUsers);
        }
    }

    public function maintenance($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'machine_id' => $id,
                'type' => $_POST['type'],
                'description' => $_POST['description'],
                'date_performed' => date('Y-m-d'),
                'performed_by' => $_SESSION['user_id'],
                'status' => 'completed'
            ];
            
            if ($this->machineModel->recordMaintenance($data)) {
                $machine = $this->machineModel->getMachineById($id);
                $users = $this->machineModel->getResponsibleUsers($id);
                $this->emailService->sendMaintenanceComplete($machine, $users);
                header('Location: /machines');
                exit;
            }
        }
        
        $machine = $this->machineModel->getMachineById($id);
        echo $this->twig->render('machines/maintenance.twig', ['machine' => $machine]);
    }
}
