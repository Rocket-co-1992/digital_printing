<?php
class PrintQueueController {
    private $twig;
    private $printJobModel;
    private $machineModel;

    public function __construct($twig) {
        $this->twig = $twig;
        $this->printJobModel = new PrintJob();
        $this->machineModel = new Machine();
    }

    public function index() {
        $queuedJobs = $this->printJobModel->getQueuedJobs();
        $machines = $this->machineModel->getMachines();
        
        foreach ($machines as &$machine) {
            $machine['current_job'] = $this->machineModel->getCurrentJob($machine['id']);
        }
        
        echo $this->twig->render('print_queue/index.twig', [
            'queued_jobs' => $queuedJobs,
            'machines' => $machines
        ]);
    }
}
