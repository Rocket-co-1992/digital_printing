<?php
class ReportController {
    private $twig;
    private $machineModel;
    private $clientModel;

    public function __construct($twig) {
        $this->twig = $twig;
        $this->machineModel = new Machine();
        $this->clientModel = new Client();
    }

    public function index() {
        echo $this->twig->render('reports/index.twig');
    }

    public function generateReport() {
        $type = $_GET['type'] ?? 'maintenance';
        $format = $_GET['format'] ?? 'html';
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        $data = [];
        switch ($type) {
            case 'maintenance':
                $data['report'] = $this->machineModel->getMaintenanceReport($startDate, $endDate);
                $template = 'reports/maintenance.twig';
                break;
            case 'efficiency':
                $data['report'] = $this->machineModel->getEfficiencyReport($_GET['machine_id']);
                $template = 'reports/efficiency.twig';
                break;
        }

        if ($format === 'pdf') {
            $this->exportToPdf($data, $template);
        } else {
            echo $this->twig->render($template, $data);
        }
    }
}
