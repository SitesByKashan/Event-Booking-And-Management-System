<?php

require_once __DIR__ . "/../config/db.php";

function addEvent() {
    global $conn;

    $data = json_decode(file_get_contents("php://input"), true);

    $category_id = $data['category_id'] ?? null;
    $title = $data['title'] ?? '';
    $description = $data['description'] ?? '';
    $location = $data['location'] ?? '';
    $event_date = $data['event_date'] ?? '';
    $event_time = $data['event_time'] ?? '';
    $price = $data['price'] ?? 0;
    $total_tickets = $data['total_tickets'] ?? 0;

    if ($title == '' || $event_date == '' || $event_time == '' || $total_tickets == 0) {
        echo json_encode(["status" => false, "message" => "Required fields are missing"]);
        return;
    }

    $sql = "INSERT INTO events 
    (category_id, title, description, location, event_date, event_time, price, total_tickets, available_tickets)
    VALUES 
    (:category_id, :title, :description, :location, :event_date, :event_time, :price, :total_tickets, :available_tickets)";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        ":category_id" => $category_id,
        ":title" => $title,
        ":description" => $description,
        ":location" => $location,
        ":event_date" => $event_date,
        ":event_time" => $event_time,
        ":price" => $price,
        ":total_tickets" => $total_tickets,
        ":available_tickets" => $total_tickets
    ]);

    echo json_encode(["status" => true, "message" => "Event added successfully"]);
}

function getEvents() {
    global $conn;

    $sql = "SELECT events.*, categories.name AS category_name
            FROM events
            LEFT JOIN categories ON events.category_id = categories.id
            WHERE events.status = 'active'
            ORDER BY events.event_date ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => true,
        "message" => "Events fetched successfully",
        "data" => $events
    ]);
}

?>