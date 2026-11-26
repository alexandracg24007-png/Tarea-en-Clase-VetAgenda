<?php
class Cita {
    private $conn;
    private $table = 'citas';

    // Configuración de la agenda de la clínica
    public const HORA_INICIO = 8;      // 08:00
    public const HORA_FIN = 17;        // última cita termina 17:00
    public const INTERVALO_MIN = 30;   // duración de cada cita en minutos
    public const ALMUERZO_INICIO = 12; // sin citas de 12:00 a 13:00
    public const ALMUERZO_FIN = 13;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function listarTodas() {
        $query = "SELECT c.*, m.nombre AS mascota, m.especie, u.nombre AS dueno, v.nombre AS veterinario
                  FROM " . $this->table . " c
                  INNER JOIN mascotas m ON c.id_mascota = m.id
                  INNER JOIN usuarios u ON m.id_dueno = u.id
                  INNER JOIN usuarios v ON c.id_veterinario = v.id
                  ORDER BY c.fecha_hora DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function listarPorDueno($id_dueno) {
        $query = "SELECT c.*, m.nombre AS mascota, m.especie, v.nombre AS veterinario
                  FROM " . $this->table . " c
                  INNER JOIN mascotas m ON c.id_mascota = m.id
                  INNER JOIN usuarios v ON c.id_veterinario = v.id
                  WHERE m.id_dueno = :id_dueno
                  ORDER BY c.fecha_hora DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_dueno', $id_dueno);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // [RF03] Todos los horarios posibles de un día (formato HH:MM)
    public static function generarHorarios() {
        $horarios = [];
        for ($min = self::HORA_INICIO * 60; $min < self::HORA_FIN * 60; $min += self::INTERVALO_MIN) {
            $h = intdiv($min, 60);
            if ($h >= self::ALMUERZO_INICIO && $h < self::ALMUERZO_FIN) {
                continue;
            }
            $horarios[] = sprintf('%02d:%02d', $h, $min % 60);
        }
        return $horarios;
    }

    // [RF03] La clínica atiende de lunes a viernes
    public static function esDiaLaborable($fechaYmd) {
        $dia = (int)date('N', strtotime($fechaYmd)); // 1 = lunes ... 7 = domingo
        return $dia >= 1 && $dia <= 5;
    }

    // [RF03/RF04] Horarios libres de un veterinario en una fecha (descarta ocupados y pasados)
    public function horasDisponibles($id_veterinario, $fechaYmd) {
        if (!self::esDiaLaborable($fechaYmd)) {
            return [];
        }

        $query = "SELECT fecha_hora FROM " . $this->table . "
                  WHERE id_veterinario = :vet AND DATE(fecha_hora) = :fecha AND estado <> 'cancelada'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':vet', $id_veterinario, PDO::PARAM_INT);
        $stmt->bindParam(':fecha', $fechaYmd);
        $stmt->execute();

        // Cada cita ocupa el bloque de 30 min al que pertenece su hora
        $ocupadas = [];
        foreach ($stmt->fetchAll() as $fila) {
            $ts = strtotime($fila['fecha_hora']);
            $h = (int)date('G', $ts);
            $m = (int)date('i', $ts);
            $m -= $m % self::INTERVALO_MIN;
            $ocupadas[sprintf('%02d:%02d', $h, $m)] = true;
        }

        $ahora = time();
        $libres = [];
        foreach (self::generarHorarios() as $hora) {
            if (isset($ocupadas[$hora])) {
                continue;
            }
            if (strtotime($fechaYmd . ' ' . $hora) <= $ahora) {
                continue; // no se agenda en el pasado
            }
            $libres[] = $hora;
        }
        return $libres;
    }

    // [RF04] Indica si el veterinario está libre en esa fecha y hora exactas
    public function verificarDisponibilidad($id_veterinario, $fecha_hora) {
        $fecha = date('Y-m-d', strtotime($fecha_hora));
        $hora = date('H:i', strtotime($fecha_hora));
        return in_array($hora, $this->horasDisponibles($id_veterinario, $fecha), true);
    }

    // [RF03] Crea la cita en estado 'pendiente'. Devuelve true si se creó, false si el horario se ocupó.
    public function crear($id_mascota, $id_veterinario, $fecha_hora, $motivo) {
        try {
            $this->conn->beginTransaction();

            // Bloquea al veterinario mientras se valida e inserta: dos clientes
            // no pueden reservar el mismo horario al mismo tiempo.
            $lock = $this->conn->prepare("SELECT id FROM usuarios WHERE id = :id FOR UPDATE");
            $lock->bindParam(':id', $id_veterinario, PDO::PARAM_INT);
            $lock->execute();

            if (!$this->verificarDisponibilidad($id_veterinario, $fecha_hora)) {
                $this->conn->rollBack();
                return false;
            }

            $query = "INSERT INTO " . $this->table . " (id_mascota, id_veterinario, fecha_hora, motivo, estado)
                      VALUES (:mascota, :vet, :fecha_hora, :motivo, 'pendiente')";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':mascota', $id_mascota, PDO::PARAM_INT);
            $stmt->bindParam(':vet', $id_veterinario, PDO::PARAM_INT);
            $stmt->bindParam(':fecha_hora', $fecha_hora);
            $stmt->bindParam(':motivo', $motivo);
            $stmt->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        }
    }

    // TODO [Sprint 3 - RF05/RF06]: Implementar actualizarEstado($id_cita, $estado, $nota_clinica = null)
}
