<?php
class Cita {
    private $conn;
    private $table = 'citas';

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
        $stmt->bindParam(':id_dueno', $id_dueno, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // 1. Obtener horas ya ocupadas para un veterinario en una fecha específica
    public function obtenerHorasOcupadas($idVeterinario, $fecha) {
        $query = "SELECT TIME_FORMAT(fecha_hora, '%H:%i') AS hora 
                  FROM " . $this->table . " 
                  WHERE id_veterinario = :id_vet 
                    AND DATE(fecha_hora) = :fecha 
                    AND estado != 'cancelada'";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_vet', $idVeterinario, PDO::PARAM_INT);
        $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // 2. Verificar disponibilidad antes de guardar
    public function estaDisponible($idVeterinario, $fechaHora) {
        $query = "SELECT COUNT(*) 
                  FROM " . $this->table . " 
                  WHERE id_veterinario = :id_vet 
                    AND fecha_hora = :fecha_hora 
                    AND estado != 'cancelada'";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_vet', $idVeterinario, PDO::PARAM_INT);
        $stmt->bindParam(':fecha_hora', $fechaHora, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchColumn() == 0;
    }

    // 3. Crear nueva cita (usando id_mascota en singular)
    public function crear($idMascota, $idVeterinario, $fechaHora, $motivo) {
        $query = "INSERT INTO " . $this->table . " (id_mascota, id_veterinario, fecha_hora, motivo, estado) 
                  VALUES (:id_mascota, :id_veterinario, :fecha_hora, :motivo, 'pendiente')";
                
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_mascota', $idMascota, PDO::PARAM_INT);
        $stmt->bindParam(':id_veterinario', $idVeterinario, PDO::PARAM_INT);
        $stmt->bindParam(':fecha_hora', $fechaHora, PDO::PARAM_STR);
        $stmt->bindParam(':motivo', $motivo, PDO::PARAM_STR);
        
        return $stmt->execute();
    }
    // [Sprint 3 - RF05]: Cambiar estado de una cita (confirmada / cancelada)
    public function actualizarEstado($idCita, $nuevoEstado) {
        $query = "UPDATE " . $this->table . " 
                  SET estado = :estado 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':estado', $nuevoEstado, PDO::PARAM_STR);
        $stmt->bindParam(':id', $idCita, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // [Sprint 3 - RF05]: Reagendar fecha/hora y confirmar la cita
    public function reagendar($idCita, $nuevaFechaHora) {
        $query = "UPDATE " . $this->table . " 
                  SET fecha_hora = :fecha_hora, estado = 'confirmada' 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fecha_hora', $nuevaFechaHora, PDO::PARAM_STR);
        $stmt->bindParam(':id', $idCita, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // [Sprint 3 - RF06]: Guardar nota clínica y marcar la cita como atendida
    public function atender($idCita, $notaClinica) {
        $query = "UPDATE " . $this->table . " 
                  SET nota_clinica = :nota_clinica, estado = 'atendida' 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nota_clinica', $notaClinica, PDO::PARAM_STR);
        $stmt->bindParam(':id', $idCita, PDO::PARAM_INT);
        return $stmt->execute();
    }
}