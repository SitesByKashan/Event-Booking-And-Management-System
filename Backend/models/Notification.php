<?php

require_once __DIR__ . "/../helpers/SmtpMailer.php";

class Notification
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->ensureTables();
    }

    private function ensureTables()
    {
        $this->conn->exec("CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(160) NOT NULL,
            message TEXT NOT NULL,
            type VARCHAR(50) NOT NULL DEFAULT 'info',
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $this->conn->exec("CREATE TABLE IF NOT EXISTS email_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            email VARCHAR(180) NOT NULL,
            subject VARCHAR(180) NOT NULL,
            body TEXT NOT NULL,
            status VARCHAR(30) NOT NULL DEFAULT 'queued',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    }

    public function create($user_id, $title, $message, $type = "info")
    {
        $stmt = $this->conn->prepare("INSERT INTO notifications (user_id, title, message, type)
            VALUES (:user_id, :title, :message, :type)");

        return $stmt->execute([
            ":user_id" => $user_id,
            ":title" => $title,
            ":message" => $message,
            ":type" => $type
        ]);
    }

    public function email($user_id, $email, $subject, $body)
    {
        $config = require __DIR__ . "/../config/mail.php";
        $mailer = new SmtpMailer($config);
        $result = $mailer->send($email, $subject, $body);
        $status = $result["status"] ? "sent" : "failed";
        $loggedBody = $body . "\n\nMailer status: " . ($result["message"] ?? "");

        $stmt = $this->conn->prepare("INSERT INTO email_logs (user_id, email, subject, body, status)
            VALUES (:user_id, :email, :subject, :body, :status)");

        return $stmt->execute([
            ":user_id" => $user_id,
            ":email" => $email,
            ":subject" => $subject,
            ":body" => $loggedBody,
            ":status" => $status
        ]);
    }

    public function createAndEmail($user, $title, $message, $type = "info")
    {
        if (!$user || empty($user["id"])) {
            return false;
        }

        $this->create($user["id"], $title, $message, $type);

        if (!empty($user["email"])) {
            $this->email($user["id"], $user["email"], $title, $message);
        }

        return true;
    }

    public function getByUser($user_id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM notifications
            WHERE user_id = :user_id
            ORDER BY created_at DESC");
        $stmt->execute([":user_id" => $user_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll()
    {
        $stmt = $this->conn->prepare("SELECT notifications.*, users.name AS user_name, users.email AS user_email
            FROM notifications
            INNER JOIN users ON notifications.user_id = users.id
            ORDER BY notifications.created_at DESC");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markAllRead($user_id)
    {
        $stmt = $this->conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :user_id");
        return $stmt->execute([":user_id" => $user_id]);
    }
}
