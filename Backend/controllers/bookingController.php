<?php

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../models/User.php";
require_once __DIR__ . "/../models/Event.php";
require_once __DIR__ . "/../models/Booking.php";

function createBooking()
{
    global $conn;

    $userModel = new User($conn);
    $eventModel = new Event($conn);
    $bookingModel = new Booking($conn);

    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        echo json_encode(["status" => false, "message" => "Invalid JSON data"]);
        return;
    }

    $user_id = $data['user_id'] ?? '';
    $event_id = $data['event_id'] ?? '';
    $tickets = $data['tickets'] ?? 0;

    if ($user_id == '' || $event_id == '' || $tickets <= 0) {
        echo json_encode(["status" => false, "message" => "Required fields are missing"]);
        return;
    }

    $user = $userModel->findById($user_id);

    if (!$user) {
        echo json_encode(["status" => false, "message" => "User not found"]);
        return;
    }

    $event = $eventModel->findById($event_id);

    if (!$event) {
        echo json_encode(["status" => false, "message" => "Event not found"]);
        return;
    }

    if ($event['status'] != 'active') {
        echo json_encode(["status" => false, "message" => "Event is not active"]);
        return;
    }

    if ((int) $event['available_tickets'] < (int) $tickets) {
        echo json_encode(["status" => false, "message" => "Not enough tickets available"]);
        return;
    }

    $total_amount = (int) $tickets * (float) $event['price'];

    try {
        $conn->beginTransaction();

        $booking_id = $bookingModel->create($user_id, $event_id, $tickets, $total_amount);
        $eventModel->reduceTickets($event_id, $tickets);

        $conn->commit();

        echo json_encode([
            "status" => true,
            "message" => "Booking created successfully",
            "data" => [
                "booking_id" => $booking_id,
                "user_id" => $user_id,
                "event_id" => $event_id,
                "tickets" => $tickets,
                "total_amount" => $total_amount
            ]
        ]);

    } catch (Exception $e) {
        $conn->rollBack();

        echo json_encode([
            "status" => false,
            "message" => "Booking failed",
            "error" => $e->getMessage()
        ]);
    }
}

function getBookings()
{
    global $conn;

    $bookingModel = new Booking($conn);
    $bookings = $bookingModel->getAll();

    echo json_encode([
        "status" => true,
        "message" => "Bookings fetched successfully",
        "data" => $bookings
    ]);
}

function cancelBooking()
{
    global $conn;

    $data = json_decode(file_get_contents("php://input"), true);
    $booking_id = $data['booking_id'] ?? '';

    if ($booking_id == '') {
        echo json_encode(["status" => false, "message" => "Booking id is required"]);
        return;
    }

    $bookingModel = new Booking($conn);
    $bookingModel->cancel($booking_id);

    echo json_encode([
        "status" => true,
        "message" => "Booking cancelled successfully"
    ]);
}

function getUserBookings()
{
    global $conn;

    $bookingModel = new Booking($conn);
    $user_id = $_GET['user_id'] ?? '';

    if ($user_id == '') {
        echo json_encode(["status" => false, "message" => "User id is required"]);
        return;
    }

    $bookings = $bookingModel->getByUser($user_id);

    echo json_encode([
        "status" => true,
        "message" => "User bookings fetched successfully",
        "data" => $bookings
    ]);
}