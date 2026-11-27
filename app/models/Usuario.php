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

    public function crearCliente($nombre, $email, $passwordHash) {
        $query = "INSERT INTO " . $this->table . " (nombre, email, password_hash, rol) 
                  VALUES (:nombre, :email, :password_hash, 'cliente')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password_hash', $passwordHash, PDO::PARAM_STR);
        return $stmt->execute();
    }
}