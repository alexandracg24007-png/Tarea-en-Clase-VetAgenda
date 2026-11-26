<?php
class Mascota {
    private $conn;
    private $table = 'mascotas';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function listarPorDueno($id_dueno) {
        $query = "SELECT * FROM " . $this->table . " WHERE id_dueno = :id_dueno ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_dueno', $id_dueno);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // [RF02] Da de alta una mascota vinculada a la cuenta del dueño
    public function crear($nombre, $especie, $raza, $edad, $id_dueno) {
        $query = "INSERT INTO " . $this->table . " (nombre, especie, raza, edad, id_dueno)
                  VALUES (:nombre, :especie, :raza, :edad, :id_dueno)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':especie', $especie);
        $stmt->bindParam(':raza', $raza);
        $stmt->bindParam(':edad', $edad, PDO::PARAM_INT);
        $stmt->bindParam(':id_dueno', $id_dueno, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // [RF02] Evita registrar dos veces la misma mascota (mismo nombre y especie) para un dueño
    public function existeParaDueno($nombre, $especie, $id_dueno) {
        $query = "SELECT COUNT(*) FROM " . $this->table . "
                  WHERE id_dueno = :id_dueno AND LOWER(nombre) = LOWER(:nombre) AND especie = :especie";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_dueno', $id_dueno, PDO::PARAM_INT);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':especie', $especie);
        $stmt->execute();
        return (int)$stmt->fetchColumn() > 0;
    }

    // [RF03] Confirma que la mascota pertenece al dueño (evita agendar con mascotas ajenas)
    public function perteneceADueno($id_mascota, $id_dueno) {
        $query = "SELECT COUNT(*) FROM " . $this->table . " WHERE id = :id AND id_dueno = :id_dueno";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id_mascota, PDO::PARAM_INT);
        $stmt->bindParam(':id_dueno', $id_dueno, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn() > 0;
    }
}
