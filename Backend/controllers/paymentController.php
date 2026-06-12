<?php

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../models/Payment.php";
require_once __DIR__ . "/../models/Booking.php";
require_once __DIR__ . "/../models/Event.php";
require_once __DIR__ . "/../models/RefundRequest.php";
require_once __DIR__ . "/../models/User.php";
require_once __DIR__ . "/../models/Notification.php";

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
    $amount = $data["amount"];

    $cleanCardNumber = preg_replace('/\D/', '', $data["card_number"]);

    if (strlen($cleanCardNumber) < 12) {
        echo json_encode([
            "status" => false,
            "message" => "Invalid card number"
        ]);
        return;
    }

    $transaction_id = "TRX-" . time();
    $card_last4 = substr($cleanCardNumber, -4);

    try {
        $conn->beginTransaction();

        $paymentModel->create(
            $booking_id,
            $transaction_id,
            $data["card_holder"],
            $card_last4,
            $data["payment_method"],
            $amount
        );

        $bookingModel->confirm($booking_id);
        $eventModel->reduceTickets($event_id, $tickets);

        $conn->commit();

        $notifyStmt = $conn->prepare("SELECT users.id, users.email, users.name, events.title AS event_title
            FROM bookings
            INNER JOIN users ON bookings.user_id = users.id
            INNER JOIN events ON bookings.event_id = events.id
            WHERE bookings.id = :booking_id");
        $notifyStmt->execute([":booking_id" => $booking_id]);
        $bookingUser = $notifyStmt->fetch(PDO::FETCH_ASSOC);

        if ($bookingUser) {
            (new Notification($conn))->createAndEmail(
                $bookingUser,
                "Booking confirmed",
                "Your booking BK-" . $booking_id . " for " . ($bookingUser["event_title"] ?? "event") . " is confirmed. Transaction ID: " . $transaction_id . ".",
                "booking"
            );
        }

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

function getPayments() {
    global $conn;

    $paymentModel = new Payment($conn);

    echo json_encode([
        "status" => true,
        "message" => "Payments fetched successfully",
        "data" => $paymentModel->getAll()
    ]);
}

function updatePaymentStatus() {
    global $conn;

    $paymentModel = new Payment($conn);

    $data = json_decode(file_get_contents("php://input"), true);

    $id = $data["id"] ?? "";
    $status = $data["payment_status"] ?? "";

    if ($id == "" || $status == "") {
        echo json_encode([
            "status" => false,
            "message" => "Required fields are missing"
        ]);
        return;
    }

    $allowedStatuses = ["paid", "pending", "failed", "refunded"];

    if (!in_array($status, $allowedStatuses)) {
        echo json_encode([
            "status" => false,
            "message" => "Invalid payment status"
        ]);
        return;
    }

    $updated = $paymentModel->updateStatus($id, $status);

    if (!$updated) {
        echo json_encode([
            "status" => false,
            "message" => "Payment status update failed"
        ]);
        return;
    }

    echo json_encode([
        "status" => true,
        "message" => "Payment status updated successfully"
    ]);
}

function requestRefund()
{
    global $conn;

    startSessionSafe();

    $data = json_decode(file_get_contents("php://input"), true);
    $booking_id = $data["booking_id"] ?? "";
    $reason = trim($data["reason"] ?? "");
    $user_id = $_SESSION["user_id"] ?? null;

    if (!$user_id || $booking_id == "" || $reason == "") {
        echo json_encode([
            "status" => false,
            "message" => "Booking id and refund reason are required"
        ]);
        return;
    }

    $check = $conn->prepare("SELECT id FROM bookings WHERE id = :booking_id AND user_id = :user_id");
    $check->execute([
        ":booking_id" => $booking_id,
        ":user_id" => $user_id
    ]);

    if (!$check->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode([
            "status" => false,
            "message" => "Booking not found"
        ]);
        return;
    }

    $refundModel = new RefundRequest($conn);
    $created = $refundModel->create($booking_id, $user_id, $reason);

    if ($created) {
        $userModel = new User($conn);
        (new Notification($conn))->createAndEmail(
            $userModel->findById($user_id),
            "Refund request submitted",
            "Your refund request for booking BK-" . $booking_id . " has been submitted for review.",
            "refund"
        );
    }

    echo json_encode([
        "status" => (bool) $created,
        "message" => $created
            ? "Refund request submitted successfully"
            : "Refund request is already pending for this booking"
    ]);
}

function getRefundRequests()
{
    global $conn;

    $refundModel = new RefundRequest($conn);

    echo json_encode([
        "status" => true,
        "message" => "Refund requests fetched successfully",
        "data" => $refundModel->getAll()
    ]);
}

function approveRefundRequest()
{
    global $conn;

    $data = json_decode(file_get_contents("php://input"), true);
    $refund_id = $data["refund_id"] ?? "";

    if ($refund_id == "") {
        echo json_encode(["status" => false, "message" => "Refund id is required"]);
        return;
    }

    $refundModel = new RefundRequest($conn);
    $paymentModel = new Payment($conn);
    $bookingModel = new Booking($conn);
    $userModel = new User($conn);
    $refund = $refundModel->findById($refund_id);

    if (!$refund) {
        echo json_encode(["status" => false, "message" => "Refund request not found"]);
        return;
    }

    try {
        $conn->beginTransaction();
        $refundModel->approve($refund_id);
        $paymentModel->updateStatusByBooking($refund["booking_id"], "refunded");
        $bookingModel->cancel($refund["booking_id"]);
        $conn->commit();

        (new Notification($conn))->createAndEmail(
            $userModel->findById($refund["user_id"]),
            "Refund approved",
            "Your refund request for booking BK-" . $refund["booking_id"] . " has been approved. Your booking has been cancelled.",
            "refund"
        );

        echo json_encode([
            "status" => true,
            "message" => "Refund approved, payment refunded, and booking cancelled"
        ]);
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode([
            "status" => false,
            "message" => "Refund approval failed",
            "error" => $e->getMessage()
        ]);
    }
}
