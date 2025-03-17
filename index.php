<?php
session_start();
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'vendor/autoload.php';

// Initialize Twig
$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader, [
    'cache' => 'cache',
    'debug' => true,
]);

// Basic routing
$route = $_GET['route'] ?? 'home';
$action = $_GET['action'] ?? 'index';

try {
    $controllerName = ucfirst($route) . 'Controller';
    $controllerFile = "controllers/{$controllerName}.php";
    
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        $controller = new $controllerName($twig);
        if (method_exists($controller, $action)) {
            $controller->$action();
        } else {
            throw new Exception('Action not found');
        }
    } else {
        throw new Exception('Controller not found');
    }
} catch (Exception $e) {
    // Handle errors
    echo $twig->render('error.twig', ['error' => $e->getMessage()]);
}
