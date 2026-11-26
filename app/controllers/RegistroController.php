<?php
require_once __DIR__ . '/../models/Usuario.php';

class RegistroController {
    private $usuarioModel;

    public function __construct($db) {
        $this->usuarioModel = new Usuario($db);
    }

    // Muestra el formulario de registro
    public function index($errores = [], $old = []) {
        if (isset($_SESSION['usuario'])) {
            header('Location: index.php?action=dashboard');
            exit;
        }
        require __DIR__ . '/../views/auth/register.php';
    }

    // [RF01] Procesa el registro de un nuevo dueño de mascota
    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=registro');
            exit;
        }

        $nombre    = trim($_POST['nombre'] ?? '');
        $email     = strtolower(trim($_POST['correo'] ?? ''));
        $password  = $_POST['password'] ?? '';
        $password2 = $_POST['password_confirm'] ?? '';

        $errores = [];

        if ($nombre === '' || mb_strlen($nombre) < 3) {
            $errores[] = 'El nombre debe tener al menos 3 caracteres.';
        } elseif (mb_strlen($nombre) > 100) {
            $errores[] = 'El nombre no puede superar los 100 caracteres.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'Ingresa un correo electrónico válido.';
        } elseif (mb_strlen($email) > 100) {
            $errores[] = 'El correo no puede superar los 100 caracteres.';
        } elseif ($this->usuarioModel->existeEmail($email)) {
            $errores[] = 'Ese correo ya está registrado. Inicia sesión.';
        }

        if (strlen($password) < 6) {
            $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
        }
        if ($password !== $password2) {
            $errores[] = 'Las contraseñas no coinciden.';
        }

        if (!empty($errores)) {
            // Se devuelve el formulario conservando lo escrito (sin contraseñas)
            $this->index($errores, ['nombre' => $nombre, 'correo' => $email]);
            return;
        }

        $this->usuarioModel->crearDueno($nombre, $email, $password);

        $_SESSION['flash'] = [
            'tipo' => 'success',
            'mensaje' => '¡Cuenta creada con éxito! Ya puedes iniciar sesión.'
        ];
        header('Location: index.php?action=login');
        exit;
    }
}
