<?php

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../models/Payment.php";
require_once __DIR__ . "/../models/Booking.php";
require_once __DIR__ . "/../models/Event.php";

function createPayment() {
    global $conn;

    $paymentModel = new Payment($conn);
    $bookingModel = new Booking($conn);
    $eventModel = new Event($conn);

    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        echo json_encode([
            "status" => false,
            "message" => "Invalid payment data"
        ]);
        return;
    }

    $required = [
        "booking_id",
        "event_id",
        "tickets",
        "card_holder",
        "card_number",
        "payment_method",
        "amount"
    ];

    foreach ($required as $field) {
        if (!isset($data[$field]) || $data[$field] === "") {
            echo json_encode([
                "status" => false,
                "message" => "$field is required"
            ]);
            return;
        }
    }

    $booking_id = $data["booking_id"];
    $event_id = $data["event_id"];
    $tickets = $data["tickets"];

    $transaction_id = "TRX-" . time();
    $card_last4 = substr($data["card_number"], -4);

    try {
        $conn->beginTransaction();

        $paymentModel->create(
            $booking_id,
            $transaction_id,
            $data["card_holder"],
            $card_last4,
            $data["payment_method"],
            $data["amount"]
        );

        $bookingModel->confirm($booking_id);

        $eventModel->reduceTickets($event_id, $tickets);

        $conn->commit();

        echo json_encode([
            "status" => true,
            "message" => "Payment successful. Booking confirmed.",
            "transaction_id" => $transaction_id
        ]);

    } catch (Exception $e) {
        $conn->rollBack();

        echo json_encode([
            "status" => false,
            "message" => "Payment failed",
            "error" => $e->getMessage()
        ]);
    }
}