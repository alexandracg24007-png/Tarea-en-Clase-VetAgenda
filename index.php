<?php
// Mostrar errores mientras verificamos que todo cargue bien en ProFreeHost
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Rutas hacia app/ desde la carpeta principal (htdocs/)
require_once __DIR__ . '/app/config/Database.php';
require_once __DIR__ . '/app/controllers/AuthController.php';
require_once __DIR__ . '/app/controllers/CitaController.php';

// Conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

// Instanciar controladores
$authController = new AuthController($db);
$citaController = new CitaController($db);

// Enrutador simple (?action=...)
$action = $_GET['action'] ?? (isset($_SESSION['usuario']) ? 'dashboard' : 'login');

switch ($action) {
    case 'login':
        $authController->login();
        break;
    case 'logout':
        $authController->logout();
        break;
    case 'dashboard':
        $citaController->dashboard();
        break;
    case 'horasDisponibles':
        $citaController->horasDisponibles();
        break;
    case 'agendarCita':
        $citaController->agendarCita();
        break;
    default:
        $citaController->dashboard();
        break;
}