<?php

class Category {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($name) {
        $sql = "INSERT INTO categories (name) VALUES (:name)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([":name" => $name]);
    }

    public function getAll() {
        $sql = "SELECT * FROM categories ORDER BY id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id, $name) {
        $sql = "UPDATE categories SET name = :name WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ":id" => $id,
            ":name" => $name
        ]);
    }

    public function delete($id) {
        $sql = "DELETE FROM categories WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([":id" => $id]);
    }
}