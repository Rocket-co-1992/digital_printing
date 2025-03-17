<?php
class ClientController {
    private $twig;
    private $clientModel;

    public function __construct($twig) {
        $this->twig = $twig;
        $this->clientModel = new Client();
    }

    public function index() {
        $clients = $this->clientModel->getClients();
        echo $this->twig->render('clients/index.twig', ['clients' => $clients]);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'],
                'email' => $_POST['email'],
                'phone' => $_POST['phone'],
                'company_id' => $_POST['company_id'],
                'tax_number' => $_POST['tax_number']
            ];
            
            if ($this->clientModel->createClient($data)) {
                header('Location: /clients');
                exit;
            }
        }
        
        echo $this->twig->render('clients/create.twig');
    }

    public function view($id) {
        $client = $this->clientModel->getClientWithDetails($id);
        echo $this->twig->render('clients/view.twig', ['client' => $client]);
    }

    public function edit($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'],
                'email' => $_POST['email'],
                'phone' => $_POST['phone'],
                'company_id' => $_POST['company_id'],
                'tax_number' => $_POST['tax_number']
            ];
            
            if ($this->clientModel->updateClient($id, $data)) {
                header('Location: /clients');
                exit;
            }
        }
        
        $client = $this->clientModel->getClientById($id);
        echo $this->twig->render('clients/edit.twig', ['client' => $client]);
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->clientModel->deleteClient($id)) {
                header('Location: /clients');
                exit;
            }
        }
    }
}
