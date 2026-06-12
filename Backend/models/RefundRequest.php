<?php

class RefundRequest
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->ensureTable();
    }

    private function ensureTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS refund_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            booking_id INT NOT NULL,
            user_id INT NOT NULL,
            reason TEXT NOT NULL,
            status VARCHAR(30) NOT NULL DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        $this->conn->exec($sql);
    }

    public function create($booking_id, $user_id, $reason)
    {
        $existing = $this->conn->prepare(
            "SELECT id FROM refund_requests WHERE booking_id = :booking_id AND user_id = :user_id AND status = 'pending'"
        );
        $existing->execute([
            ":booking_id" => $booking_id,
            ":user_id" => $user_id
        ]);

        if ($existing->fetch(PDO::FETCH_ASSOC)) {
            return false;
        }

        $stmt = $this->conn->prepare(
            "INSERT INTO refund_requests (booking_id, user_id, reason) VALUES (:booking_id, :user_id, :reason)"
        );

        return $stmt->execute([
            ":booking_id" => $booking_id,
            ":user_id" => $user_id,
            ":reason" => $reason
        ]);
    }

    public function getAll()
    {
        $stmt = $this->conn->prepare("SELECT
                refund_requests.*,
                users.name AS user_name,
                users.email AS user_email,
                bookings.total_amount,
                bookings.status AS booking_status,
                events.title AS event_title
            FROM refund_requests
            INNER JOIN users ON refund_requests.user_id = users.id
            INNER JOIN bookings ON refund_requests.booking_id = bookings.id
            INNER JOIN events ON bookings.event_id = events.id
            ORDER BY refund_requests.created_at DESC");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function approve($id)
    {
        $stmt = $this->conn->prepare("UPDATE refund_requests SET status = 'approved' WHERE id = :id");
        return $stmt->execute([":id" => $id]);
    }

    public function findById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM refund_requests WHERE id = :id");
        $stmt->execute([":id" => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
