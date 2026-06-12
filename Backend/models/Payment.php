<?php

class Payment {

    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create(
        $booking_id,
        $transaction_id,
        $card_holder,
        $card_last4,
        $payment_method,
        $amount
    ) {
        $sql = "INSERT INTO payments
        (
            booking_id,
            transaction_id,
            card_holder,
            card_last4,
            payment_method,
            payment_status,
            amount
        )
        VALUES
        (
            :booking_id,
            :transaction_id,
            :card_holder,
            :card_last4,
            :payment_method,
            'paid',
            :amount
        )";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ":booking_id" => $booking_id,
            ":transaction_id" => $transaction_id,
            ":card_holder" => $card_holder,
            ":card_last4" => $card_last4,
            ":payment_method" => $payment_method,
            ":amount" => $amount
        ]);
    }

    public function getAll() {
        $sql = "SELECT 
                    payments.*,
                    bookings.id AS booking_id,
                    users.name AS user_name,
                    users.email AS user_email,
                    events.title AS event_title
                FROM payments
                INNER JOIN bookings ON payments.booking_id = bookings.id
                INNER JOIN users ON bookings.user_id = users.id
                INNER JOIN events ON bookings.event_id = events.id
                ORDER BY payments.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status) {
        $sql = "UPDATE payments 
                SET payment_status = :status 
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ":id" => $id,
            ":status" => $status
        ]);
    }

    public function updateStatusByBooking($booking_id, $status) {
        $sql = "UPDATE payments
                SET payment_status = :status
                WHERE booking_id = :booking_id";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ":booking_id" => $booking_id,
            ":status" => $status
        ]);
    }
}
