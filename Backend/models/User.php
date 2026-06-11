<?php

class User {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($name, $email, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (name, email, password)
                VALUES (:name, :email, :password)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ":name" => $name,
            ":email" => $email,
            ":password" => $hashedPassword
        ]);
    }

    public function findByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = :email";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ":email" => $email
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

   public function findById($id) {

    $sql = "SELECT
                id,
                name,
                email,
                phone,
                role,
                status
            FROM users
            WHERE id = :id";

    $stmt = $this->conn->prepare($sql);

    $stmt->execute([
        ":id" => $id
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

    public function getAllUsers() {
        $sql = "SELECT 
                    users.id,
                    users.name,
                    users.email,
                    users.phone,
                    users.role,
                    users.status,
                    COUNT(bookings.id) AS total_bookings
                FROM users
                LEFT JOIN bookings 
                ON users.id = bookings.user_id
                GROUP BY 
                    users.id,
                    users.name,
                    users.email,
                    users.phone,
                    users.role,
                    users.status
                ORDER BY users.id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateProfile($id, $data) {
    $sql = "UPDATE users
            SET name = :name,
                email = :email,
                phone = :phone
            WHERE id = :id";

    $stmt = $this->conn->prepare($sql);

    return $stmt->execute([
        ":id" => $id,
        ":name" => $data['name'],
        ":email" => $data['email'],
        ":phone" => $data['phone'] ?? ''
    ]);
}

public function updatePassword($id, $password) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $sql = "UPDATE users
            SET password = :password
            WHERE id = :id";

    $stmt = $this->conn->prepare($sql);

    return $stmt->execute([
        ":id" => $id,
        ":password" => $hashedPassword
    ]);
}
}