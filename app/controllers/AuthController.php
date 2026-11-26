<?php
require_once __DIR__ . '/../models/Usuario.php';

class AuthController {
    private $usuarioModel;

    public function __construct($db) {
        $this->usuarioModel = new Usuario($db);
    }

    public function mostrarLogin($error = null) {
        require __DIR__ . '/../views/auth/login.php';
    }

    public function login() {
        // Si ya hay sesión activa, no tiene sentido mostrar el login
        if (isset($_SESSION['usuario'])) {
            header('Location: index.php?action=dashboard');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = strtolower(trim($_POST['email'] ?? ''));
            $password = $_POST['password'] ?? '';

            $user = $this->usuarioModel->buscarPorEmail($email);

            if ($user && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true); // evita fijación de sesión
                $_SESSION['usuario'] = [
                    'id' => $user['id'],
                    'nombre' => $user['nombre'],
                    'email' => $user['email'],
                    'rol' => $user['rol']
                ];

                // [RF01] Mensaje de bienvenida diferenciado por rol
                $bienvenida = [
                    'cliente'       => 'Bienvenido/a. Aquí puedes gestionar tus mascotas y tus citas.',
                    'recepcionista' => 'Bienvenido/a al panel de recepción: gestiona las citas de la clínica.',
                    'veterinario'   => 'Bienvenido/a, doctor/a. Aquí verás las citas que debes atender.'
                ];
                $_SESSION['flash'] = [
                    'tipo' => 'success',
                    'mensaje' => $bienvenida[$user['rol']] ?? 'Sesión iniciada.'
                ];

                header('Location: index.php?action=dashboard');
                exit;
            } else {
                $this->mostrarLogin("Credenciales incorrectas. Intenta nuevamente.");
            }
        } else {
            $this->mostrarLogin();
        }
    }

    public function logout() {
        $_SESSION = [];
        session_destroy();
        session_start();
        $_SESSION['flash'] = ['tipo' => 'info', 'mensaje' => 'Sesión cerrada correctamente.'];
        header('Location: index.php?action=login');
        exit;
    }
}
