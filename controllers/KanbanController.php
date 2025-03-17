<?php
class KanbanController {
    private $twig;
    private $taskModel;

    public function __construct($twig) {
        $this->twig = $twig;
        $this->taskModel = new Task();
    }

    public function index() {
        $tasks = $this->taskModel->getTasks();
        
        $columns = [
            'pending' => [],
            'in_progress' => [],
            'completed' => []
        ];

        foreach ($tasks as $task) {
            $columns[$task['status']][] = $task;
        }

        echo $this->twig->render('kanban/index.twig', ['columns' => $columns]);
    }

    public function updateStatus() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $taskId = $_POST['taskId'];
            $newStatus = $_POST['status'];
            
            $success = $this->taskModel->updateTaskStatus($taskId, $newStatus);
            echo json_encode(['success' => $success]);
        }
    }
}
