<?php
require_once __DIR__ . '/../models/Mascota.php';

class MascotaController {
    private $mascotaModel;

    public const ESPECIES = ['Perro', 'Gato', 'Ave', 'Conejo', 'Hámster', 'Reptil', 'Otro'];

    public function __construct($db) {
        $this->mascotaModel = new Mascota($db);
    }

    // [RF02] Registra una mascota para el dueño que tiene la sesión iniciada
    public function guardar() {
        if (!isset($_SESSION['usuario'])) {
            header('Location: index.php?action=login');
            exit;
        }
        // Solo los dueños (rol cliente) registran mascotas
        if ($_SESSION['usuario']['rol'] !== 'cliente' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=dashboard');
            exit;
        }

        $nombre  = trim($_POST['nombre'] ?? '');
        $especie = trim($_POST['especie'] ?? '');
        $raza    = trim($_POST['raza'] ?? '');
        $edadRaw = trim($_POST['edad'] ?? '0');
        $idDueno = (int)$_SESSION['usuario']['id']; // siempre desde la sesión, nunca desde el formulario

        $errores = [];

        if ($nombre === '' || mb_strlen($nombre) > 80) {
            $errores[] = 'El nombre de la mascota es obligatorio (máximo 80 caracteres).';
        }
        if (!in_array($especie, self::ESPECIES, true)) {
            $errores[] = 'Selecciona una especie válida.';
        }
        if (mb_strlen($raza) > 80) {
            $errores[] = 'La raza no puede superar los 80 caracteres.';
        }
        if ($raza === '') {
            $raza = 'Mestizo';
        }
        if (!ctype_digit($edadRaw) || (int)$edadRaw > 40) {
            $errores[] = 'La edad debe ser un número entero entre 0 y 40.';
        }
        if (empty($errores) && $this->mascotaModel->existeParaDueno($nombre, $especie, $idDueno)) {
            $errores[] = "Ya tienes registrada una mascota llamada «{$nombre}» de la especie {$especie}.";
        }

        if (!empty($errores)) {
            $_SESSION['flash'] = ['tipo' => 'danger', 'mensaje' => implode(' ', $errores)];
            $_SESSION['old'] = ['modal' => 'modalMascota', 'datos' => $_POST];
            header('Location: index.php?action=dashboard');
            exit;
        }

        $this->mascotaModel->crear($nombre, $especie, $raza, (int)$edadRaw, $idDueno);

        $_SESSION['flash'] = ['tipo' => 'success', 'mensaje' => "Mascota «{$nombre}» registrada correctamente."];
        header('Location: index.php?action=dashboard');
        exit;
    }
}
