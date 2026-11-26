<?php
session_start();
date_default_timezone_set('America/Guayaquil'); // ajusta tu zona horaria si es otra

require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/RegistroController.php';
require_once __DIR__ . '/../app/controllers/MascotaController.php';
require_once __DIR__ . '/../app/controllers/CitaController.php';

$database = new Database();
$db = $database->getConnection();

$action = $_GET['action'] ?? 'dashboard';

$authController = new AuthController($db);
$registroController = new RegistroController($db);
$mascotaController = new MascotaController($db);
$citaController = new CitaController($db);

switch ($action) {
    case 'login':
        $authController->login();
        break;
    case 'logout':
        $authController->logout();
        break;
    case 'registro':            // [RF01] formulario de registro de dueños
        $registroController->index();
        break;
    case 'registro_guardar':    // [RF01] procesa el registro
        $registroController->guardar();
        break;
    case 'mascota_guardar':     // [RF02] alta de mascota
        $mascotaController->guardar();
        break;
    case 'cita_guardar':        // [RF03] solicitud de cita
        $citaController->agendarCita();
        break;
    case 'horas_disponibles':   // [RF03] horas libres (JSON para el formulario)
        $citaController->horasDisponibles();
        break;
    case 'dashboard':
    default:
        $citaController->dashboard();
        break;
}
