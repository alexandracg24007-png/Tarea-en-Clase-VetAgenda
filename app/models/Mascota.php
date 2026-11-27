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

    // TODO [Sprint 2 - RF02]: Implementar método crear($nombre, $especie, $raza, $edad, $id_dueno)
    public function crear($nombre, $especie, $raza, $edad, $id_dueno) {
        $query = "INSERT INTO " . $this->table . " (nombre, especie, raza, edad, id_dueno) 
                  VALUES (:nombre, :especie, :raza, :edad, :id_dueno)";
        
        $stmt = $this->conn->prepare($query);

        // Limpiar datos (seguridad básica)
        $nombre = htmlspecialchars(strip_tags($nombre));
        $especie = htmlspecialchars(strip_tags($especie));
        $raza = htmlspecialchars(strip_tags($raza));
        $edad = htmlspecialchars(strip_tags($edad));
        $id_dueno = htmlspecialchars(strip_tags($id_dueno));

        // Vincular parámetros
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':especie', $especie);
        $stmt->bindParam(':raza', $raza);
        $stmt->bindParam(':edad', $edad);
        $stmt->bindParam(':id_dueno', $id_dueno);

        return $stmt->execute();
    }
    }
