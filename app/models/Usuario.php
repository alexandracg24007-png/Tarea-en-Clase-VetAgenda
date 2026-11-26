<?php
class Usuario {
    private $conn;
    private $table = 'usuarios';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function buscarPorEmail($email) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function listarVeterinarios() {
        $query = "SELECT id, nombre FROM " . $this->table . " WHERE rol = 'veterinario'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // [RF01] Verifica si el correo ya está registrado
    public function existeEmail($email) {
        $query = "SELECT COUNT(*) FROM " . $this->table . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return (int)$stmt->fetchColumn() > 0;
    }

    // [RF01] Registra un dueño de mascota. El rol SIEMPRE es 'cliente':
    // los roles recepcionista/veterinario solo los crea el administrador en la BD.
    public function crearDueno($nombre, $email, $password) {
        $query = "INSERT INTO " . $this->table . " (nombre, email, password_hash, rol)
                  VALUES (:nombre, :email, :hash, 'cliente')";
        $stmt = $this->conn->prepare($query);
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':hash', $hash);
        return $stmt->execute();
    }
}
