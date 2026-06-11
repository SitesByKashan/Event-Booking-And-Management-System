<?php

class Booking
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create($user_id, $event_id, $tickets, $total_amount)
    {
        $sql = "INSERT INTO bookings 
        (user_id, event_id, tickets, total_amount, status)
        VALUES 
        (:user_id, :event_id, :tickets, :total_amount, 'pending')";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute([
            ":user_id" => $user_id,
            ":event_id" => $event_id,
            ":tickets" => $tickets,
            ":total_amount" => $total_amount
        ]);

        return $this->conn->lastInsertId();
    }

    public function cancel($id)
    {
        $sql = "UPDATE bookings SET status = 'cancelled' WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([":id" => $id]);
    }

    public function getAll()
    {
        $sql = "SELECT 
                    bookings.*,
                    users.name AS user_name,
                    users.email AS user_email,
                    events.title AS event_title
                FROM bookings
                INNER JOIN users ON bookings.user_id = users.id
                INNER JOIN events ON bookings.event_id = events.id
                ORDER BY bookings.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByUser($user_id)
    {
        $sql = "SELECT
                    bookings.*,
                    events.title AS event_title,
                    events.event_date,
                    events.event_time,
                    events.location
                FROM bookings
                INNER JOIN events ON bookings.event_id = events.id
                WHERE bookings.user_id = :user_id
                ORDER BY bookings.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([":user_id" => $user_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function confirm($id) {
    $sql = "UPDATE bookings SET status = 'confirmed' WHERE id = :id";
    $stmt = $this->conn->prepare($sql);
    return $stmt->execute([":id" => $id]);
}
}