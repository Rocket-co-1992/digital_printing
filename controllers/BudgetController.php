<?php
class BudgetController {
    private $twig;
    private $budgetModel;
    private $clientModel;

    public function __construct($twig) {
        $this->twig = $twig;
        $this->budgetModel = new Budget();
        $this->clientModel = new Client();
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'client_id' => $_POST['client_id'],
                'items' => $_POST['items'],
                'urgency_level' => $_POST['urgency_level'],
                'total_amount' => $this->budgetModel->calculateTotal($_POST['items'], $_POST['urgency_level'])
            ];
            
            $budgetId = $this->budgetModel->createBudget($data);
            if ($budgetId) {
                $this->sendBudgetEmail($budgetId);
                header('Location: /budgets/view/' . $budgetId);
                exit;
            }
        }
        
        $clients = $this->clientModel->getClients();
        echo $this->twig->render('budgets/create.twig', ['clients' => $clients]);
    }

    private function sendBudgetEmail($budgetId) {
        $budget = $this->budgetModel->getBudgetWithDetails($budgetId);
        $client = $this->clientModel->getClientById($budget['client_id']);
        
        $approvalLink = SITE_URL . "/budgets/approve/" . $budgetId;
        
        $message = $this->twig->render('emails/budget.twig', [
            'budget' => $budget,
            'client' => $client,
            'approvalLink' => $approvalLink
        ]);
        
        mail($client['email'], 
             "Orçamento #" . $budgetId, 
             $message, 
             "From: " . EMAIL_FROM);
    }
}
