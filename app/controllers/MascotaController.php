<?php
require_once __DIR__ . '/../models/Mascota.php';

class MascotaController {
    private $mascotaModel;

    public function __construct($db) {
        $this->mascotaModel = new Mascota($db);
    }

    public function registrar() {
        // Verificar que esté logueado y sea dueño 
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'cliente') {
            header('Location: index.php?action=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre  = trim($_POST['nombre'] ?? '');
            $especie = trim($_POST['especie'] ?? '');
            $raza    = trim($_POST['raza'] ?? '');
            $edad    = trim($_POST['edad'] ?? '');
            $id_dueno = $_SESSION['usuario']['id'];

            // Validación en backend
            if (empty($nombre) || empty($especie) || empty($raza) || $edad === '') {
                $_SESSION['error'] = "Todos los campos de la mascota son obligatorios.";
            } elseif (!is_numeric($edad) || $edad < 0) {
                $_SESSION['error'] = "La edad debe ser un número válido.";
            } else {
                // Guardar en base de datos
                if ($this->mascotaModel->crear($nombre, $especie, $raza, $edad, $id_dueno)) {
                    $_SESSION['exito'] = "Mascota registrada correctamente.";
                } else {
                    $_SESSION['error'] = "Error al registrar la mascota. Intenta de nuevo.";
                }
            }
        }

        // Volver al panel del dueño
        header('Location: index.php?action=dashboard');
        exit;
    }
}
