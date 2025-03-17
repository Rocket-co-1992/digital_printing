<?php
class AuthMiddleware {
    private $userModel;
    private $twig;

    public function __construct($twig) {
        $this->userModel = new User();
        $this->twig = $twig;
    }

    public function authenticate($requiredPermission = null) {
        if (!isset($_SESSION['user_token'])) {
            header('Location: /login');
            exit;
        }

        $user = $this->userModel->validateSession($_SESSION['user_token']);
        if (!$user) {
            unset($_SESSION['user_token']);
            header('Location: /login');
            exit;
        }

        if ($requiredPermission && !$this->userModel->hasPermission($user['id'], $requiredPermission)) {
            echo $this->twig->render('errors/403.twig');
            exit;
        }

        return $user;
    }
}
