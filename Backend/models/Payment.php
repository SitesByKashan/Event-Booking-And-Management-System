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
}