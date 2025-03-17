<?php
class StockController {
    private $twig;
    private $stockModel;

    public function __construct($twig) {
        $this->twig = $twig;
        $this->stockModel = new Stock();
    }

    public function index() {
        $items = $this->stockModel->getStockItems();
        echo $this->twig->render('stock/index.twig', ['items' => $items]);
    }

    public function addMovement() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'item_id' => $_POST['item_id'],
                'type' => $_POST['type'],
                'quantity' => $_POST['quantity'],
                'notes' => $_POST['notes'],
                'user_id' => $_SESSION['user_id']
            ];
            
            if ($this->stockModel->recordMovement($data)) {
                header('Location: /stock');
                exit;
            }
        }
        
        echo $this->twig->render('stock/movement.twig');
    }
}
