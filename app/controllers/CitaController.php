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

        require __DIR__ . '/../views/dashboard/index.php';
    }

    // [Sprint 2 - RF02]: Registrar nueva mascota (Cliente)
    public function registrarMascota() {
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'cliente') {
            header('Location: index.php?action=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre   = trim($_POST['nombre'] ?? '');
            $especie  = trim($_POST['especie'] ?? '');
            $raza     = trim($_POST['raza'] ?? '');
            $edad     = $_POST['edad'] ?? '';
            $id_dueno = $_SESSION['usuario']['id'];

            if (empty($nombre) || empty($especie) || empty($raza) || $edad === '' || (int)$edad < 0) {
                header('Location: index.php?action=dashboard&error=campos_vacios');
                exit;
            }

            if ($this->mascotaModel->crear($nombre, $especie, $raza, (int)$edad, $id_dueno)) {
                header('Location: index.php?action=dashboard&success=mascota_registrada');
            } else {
                header('Location: index.php?action=dashboard&error=error_guardar');
            }
            exit;
        }
    }

    // [Sprint 2 - RF03]: Devuelve las horas libres para el calendario (AJAX)
    public function horasDisponibles() {
        header('Content-Type: application/json');

        $idVeterinario = $_GET['id_veterinario'] ?? null;
        $fecha = $_GET['fecha'] ?? null;

        $horariosBase = ['08:00', '09:00', '10:00', '11:00', '14:00', '15:00', '16:00', '17:00'];

        if (!$idVeterinario || !$fecha) {
            echo json_encode([]);
            exit;
        }

        $ocupadas = $this->citaModel->obtenerHorasOcupadas($idVeterinario, $fecha);
        $disponibles = array_values(array_diff($horariosBase, $ocupadas));

        echo json_encode($disponibles);
        exit;
    }

    // [Sprint 2 - RF03]: Procesa y guarda la solicitud de cita del cliente
    public function agendarCita() {
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'cliente') {
            header('Location: index.php?action=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idMascota     = $_POST['id_mascotas'] ?? '';
            $idVeterinario = $_POST['id_veterinario'] ?? '';
            $fecha         = $_POST['fecha'] ?? '';
            $hora          = $_POST['hora'] ?? '';
            $motivo        = trim($_POST['motivo'] ?? '');

            // Unir fecha y hora para el campo DATETIME de MySQL (ej: 2026-11-27 10:00:00)
            $fechaHora = $fecha . ' ' . $hora . ':00';

            // Validar campos obligatorios
            if (empty($idMascota) || empty($idVeterinario) || empty($fecha) || empty($hora) || empty($motivo)) {
                header('Location: index.php?action=dashboard&error=campos_vacios');
                exit;
            }

            // Validar que el horario siga disponible
            if (!$this->citaModel->estaDisponible($idVeterinario, $fechaHora)) {
                header('Location: index.php?action=dashboard&error=horario_ocupado');
                exit;
            }

            // Guardar en la base de datos (4 parámetros alineados con Cita.php)
            $guardado = $this->citaModel->crear($idMascota, $idVeterinario, $fechaHora, $motivo);

            if ($guardado) {
                header('Location: index.php?action=dashboard&success=cita_agendada');
            } else {
                header('Location: index.php?action=dashboard&error=error_guardar');
            }
            exit;
        }
    }

    // [Sprint 3 - RF05]: Confirmar o cancelar cita (Recepcionista)
    public function cambiarEstadoCita() {
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'recepcionista') {
            header('Location: index.php?action=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idCita = $_POST['id_cita'] ?? '';
            $estado = $_POST['estado'] ?? '';

            if (empty($idCita) || !in_array($estado, ['confirmada', 'cancelada'])) {
                header('Location: index.php?action=dashboard&error=datos_invalidos');
                exit;
            }

            if ($this->citaModel->actualizarEstado($idCita, $estado)) {
                header('Location: index.php?action=dashboard&success=estado_actualizado');
            } else {
                header('Location: index.php?action=dashboard&error=error_guardar');
            }
            exit;
        }
    }

    // [Sprint 3 - RF05]: Reagendar cita verificando disponibilidad del veterinario (Recepcionista)
    public function reagendarCita() {
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'recepcionista') {
            header('Location: index.php?action=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idCita        = $_POST['id_cita'] ?? '';
            $idVeterinario = $_POST['id_veterinario'] ?? '';
            $fecha         = $_POST['fecha'] ?? '';
            $hora          = $_POST['hora'] ?? '';

            if (empty($idCita) || empty($idVeterinario) || empty($fecha) || empty($hora)) {
                header('Location: index.php?action=dashboard&error=campos_vacios');
                exit;
            }

            $nuevaFechaHora = $fecha . ' ' . $hora . ':00';

            if (!$this->citaModel->estaDisponible($idVeterinario, $nuevaFechaHora)) {
                header('Location: index.php?action=dashboard&error=horario_ocupado');
                exit;
            }

            if ($this->citaModel->reagendar($idCita, $nuevaFechaHora)) {
                header('Location: index.php?action=dashboard&success=cita_reagendada');
            } else {
                header('Location: index.php?action=dashboard&error=error_guardar');
            }
            exit;
        }
    }

    // [Sprint 3 - RF06]: Registrar atención clínica y marcar como atendida (Veterinario)
    public function atenderCita() {
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'veterinario') {
            header('Location: index.php?action=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idCita      = $_POST['id_cita'] ?? '';
            $notaClinica = trim($_POST['nota_clinica'] ?? '');

            if (empty($idCita) || empty($notaClinica)) {
                header('Location: index.php?action=dashboard&error=campos_vacios');
                exit;
            }

            if ($this->citaModel->atender($idCita, $notaClinica)) {
                header('Location: index.php?action=dashboard&success=cita_atendida');
            } else {
                header('Location: index.php?action=dashboard&error=error_guardar');
            }
            exit;
        }
    }
}