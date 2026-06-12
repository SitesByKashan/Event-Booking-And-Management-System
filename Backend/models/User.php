<?php

class User {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
        $this->ensureVerificationColumns();
    }

    private function ensureVerificationColumns() {
        $columns = [
            "email_verified" => "TINYINT(1) NOT NULL DEFAULT 0",
            "verification_otp" => "VARCHAR(10) NULL",
            "otp_expires_at" => "DATETIME NULL"
        ];

        foreach ($columns as $column => $definition) {
            try {
                $stmt = $this->conn->prepare("SHOW COLUMNS FROM users LIKE :column_name");
                $stmt->execute([":column_name" => $column]);

                if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
                    $this->conn->exec("ALTER TABLE users ADD COLUMN {$column} {$definition}");
                }
            } catch (Exception $e) {
                continue;
            }
        }
    }

    public function create($name, $email, $password, $otp = null) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (name, email, password, email_verified, verification_otp, otp_expires_at)
                VALUES (:name, :email, :password, 0, :verification_otp, DATE_ADD(NOW(), INTERVAL 10 MINUTE))";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ":name" => $name,
            ":email" => $email,
            ":password" => $hashedPassword,
            ":verification_otp" => $otp
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
                status,
                email_verified
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

public function updateRole($id, $role) {
    try {
        $this->conn->exec("ALTER TABLE users MODIFY role VARCHAR(30) NOT NULL DEFAULT 'user'");
    } catch (Exception $e) {
        // Existing schema may already support vendor roles.
    }

    $sql = "UPDATE users SET role = :role WHERE id = :id";
    $stmt = $this->conn->prepare($sql);

    return $stmt->execute([
        ":id" => $id,
        ":role" => $role
    ]);
}

public function verifyEmail($email, $otp) {
    $sql = "SELECT id FROM users
            WHERE email = :email
            AND verification_otp = :otp
            AND otp_expires_at >= NOW()
            AND email_verified = 0";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute([
        ":email" => $email,
        ":otp" => $otp
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        return false;
    }

    $update = $this->conn->prepare("UPDATE users
        SET email_verified = 1,
            verification_otp = NULL,
            otp_expires_at = NULL
        WHERE id = :id");

    return $update->execute([":id" => $user["id"]]);
}

public function resendOtp($email, $otp) {
    $sql = "UPDATE users
            SET verification_otp = :otp,
                otp_expires_at = DATE_ADD(NOW(), INTERVAL 10 MINUTE)
            WHERE email = :email
            AND email_verified = 0";

    $stmt = $this->conn->prepare($sql);

    return $stmt->execute([
        ":email" => $email,
        ":otp" => $otp
    ]);
}
}
