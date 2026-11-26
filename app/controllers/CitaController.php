<?php
require_once __DIR__ . '/../models/Cita.php';
require_once __DIR__ . '/../models/Mascota.php';
require_once __DIR__ . '/../models/Usuario.php';

class CitaController {
    private $citaModel;
    private $mascotaModel;
    private $usuarioModel;

    public function __construct($db) {
        $this->citaModel = new Cita($db);
        $this->mascotaModel = new Mascota($db);
        $this->usuarioModel = new Usuario($db);
    }

    public function dashboard() {
        if (!isset($_SESSION['usuario'])) {
            header('Location: index.php?action=login');
            exit;
        }

        $usuario = $_SESSION['usuario'];
        $veterinarios = $this->usuarioModel->listarVeterinarios();

        if ($usuario['rol'] === 'cliente') {
            $citas = $this->citaModel->listarPorDueno($usuario['id']);
            $mascotas = $this->mascotaModel->listarPorDueno($usuario['id']);
        } else {
            $citas = $this->citaModel->listarTodas();
            $mascotas = [];
        }

        // Si un formulario falló, se vuelve a abrir su modal con lo que el usuario escribió
        $old = $_SESSION['old'] ?? null;
        unset($_SESSION['old']);

        require __DIR__ . '/../views/dashboard/index.php';
    }

    // [RF03] Devuelve en JSON las horas libres de un veterinario en una fecha
    public function horasDisponibles() {
        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_SESSION['usuario'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Sesión no iniciada.']);
            return;
        }

        $idVet = (int)($_GET['id_veterinario'] ?? 0);
        $fecha = $_GET['fecha'] ?? '';

        if ($idVet <= 0 || !$this->fechaValida($fecha)) {
            http_response_code(400);
            echo json_encode(['error' => 'Parámetros inválidos.']);
            return;
        }

        if (!Cita::esDiaLaborable($fecha)) {
            echo json_encode(['horas' => [], 'mensaje' => 'La clínica atiende de lunes a viernes.']);
            return;
        }

        echo json_encode(['horas' => $this->citaModel->horasDisponibles($idVet, $fecha)]);
    }

    // [RF03] El dueño solicita una cita: mascota, veterinario, fecha, hora y motivo
    public function agendarCita() {
        if (!isset($_SESSION['usuario'])) {
            header('Location: index.php?action=login');
            exit;
        }
        if ($_SESSION['usuario']['rol'] !== 'cliente' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=dashboard');
            exit;
        }

        $idDueno    = (int)$_SESSION['usuario']['id'];
        $idMascota  = (int)($_POST['id_mascota'] ?? 0);
        $idVet      = (int)($_POST['id_veterinario'] ?? 0);
        $fecha      = $_POST['fecha'] ?? '';
        $hora       = $_POST['hora'] ?? '';
        $motivo     = trim($_POST['motivo'] ?? '');

        $errores = [];

        if (!$this->mascotaModel->perteneceADueno($idMascota, $idDueno)) {
            $errores[] = 'Selecciona una de tus mascotas.';
        }

        $vetsValidos = array_column($this->usuarioModel->listarVeterinarios(), 'id');
        if (!in_array($idVet, array_map('intval', $vetsValidos), true)) {
            $errores[] = 'Selecciona un veterinario válido.';
        }

        if (!$this->fechaValida($fecha)) {
            $errores[] = 'Selecciona una fecha válida.';
        } elseif ($fecha < date('Y-m-d')) {
            $errores[] = 'No puedes solicitar una cita en una fecha pasada.';
        } elseif (!Cita::esDiaLaborable($fecha)) {
            $errores[] = 'La clínica atiende de lunes a viernes.';
        }

        if (!preg_match('/^\d{2}:\d{2}$/', $hora)) {
            $errores[] = 'Selecciona una hora disponible.';
        }

        if ($motivo === '' || mb_strlen($motivo) < 5) {
            $errores[] = 'Describe el motivo de la consulta (mínimo 5 caracteres).';
        } elseif (mb_strlen($motivo) > 255) {
            $errores[] = 'El motivo no puede superar los 255 caracteres.';
        }

        if (empty($errores)) {
            $fechaHora = $fecha . ' ' . $hora . ':00';
            // La disponibilidad se valida SIEMPRE en el servidor, aunque el JS ya filtre las horas
            if (!$this->citaModel->crear($idMascota, $idVet, $fechaHora, $motivo)) {
                $errores[] = 'Ese horario ya no está disponible. Elige otra hora.';
            }
        }

        if (!empty($errores)) {
            $_SESSION['flash'] = ['tipo' => 'danger', 'mensaje' => implode(' ', $errores)];
            $_SESSION['old'] = ['modal' => 'modalCita', 'datos' => $_POST];
            header('Location: index.php?action=dashboard');
            exit;
        }

        $_SESSION['flash'] = [
            'tipo' => 'success',
            'mensaje' => 'Cita solicitada para el ' . date('d/m/Y', strtotime($fecha)) . " a las {$hora}. Quedó en estado pendiente de confirmación."
        ];
        header('Location: index.php?action=dashboard');
        exit;
    }

    private function fechaValida($fecha) {
        $d = DateTime::createFromFormat('Y-m-d', $fecha);
        return $d && $d->format('Y-m-d') === $fecha;
    }

    // TODO [Sprint 3 - RF05/RF06]: Método cambiarEstadoCita()
}
