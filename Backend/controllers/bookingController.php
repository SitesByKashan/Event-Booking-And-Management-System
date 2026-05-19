<?php

require_once __DIR__ . "/../config/db.php";

function createBooking() {
    global $conn;

    $data = json_decode(file_get_contents("php://input"), true);

    $user_id = $data['user_id'] ?? '';
    $event_id = $data['event_id'] ?? '';
    $tickets = $data['tickets'] ?? 0;

    if ($user_id == '' || $event_id == '' || $tickets <= 0) {
        echo json_encode(["status" => false, "message" => "Required fields are missing"]);
        return;
    }

    $eventSql = "SELECT * FROM events WHERE id = :event_id AND status = 'active'";
    $eventStmt = $conn->prepare($eventSql);
    $eventStmt->execute([":event_id" => $event_id]);

    $event = $eventStmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        echo json_encode(["status" => false, "message" => "Event not found"]);
        return;
    }

    if ($event['available_tickets'] < $tickets) {
        echo json_encode(["status" => false, "message" => "Not enough tickets available"]);
        return;
    }

    $total_amount = $tickets * $event['price'];

    try {
        $conn->beginTransaction();

        $bookingSql = "INSERT INTO bookings (user_id, event_id, tickets, total_amount, status)
                       VALUES (:user_id, :event_id, :tickets, :total_amount, 'confirmed')";

        $bookingStmt = $conn->prepare($bookingSql);
        $bookingStmt->execute([
            ":user_id" => $user_id,
            ":event_id" => $event_id,
            ":tickets" => $tickets,
            ":total_amount" => $total_amount
        ]);

        $updateSql = "UPDATE events 
                      SET available_tickets = available_tickets - :tickets 
                      WHERE id = :event_id";

        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->execute([
            ":tickets" => $tickets,
            ":event_id" => $event_id
        ]);

        $conn->commit();

        echo json_encode(["status" => true, "message" => "Booking created successfully"]);

    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(["status" => false, "message" => "Booking failed"]);
    }
}

?>