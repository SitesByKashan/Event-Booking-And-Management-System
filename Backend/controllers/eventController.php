<?php

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../models/Event.php";

function addEvent()
{
    global $conn;

    $eventModel = new Event($conn);
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        echo json_encode(["status" => false, "message" => "Invalid JSON data"]);
        return;
    }

    $required = ['category_id', 'title', 'event_date', 'event_time', 'price', 'total_tickets'];

    foreach ($required as $field) {
        if (!isset($data[$field]) || $data[$field] === '') {
            echo json_encode(["status" => false, "message" => "$field is required"]);
            return;
        }
    }

    $data['description'] = $data['description'] ?? '';
    $data['location'] = $data['location'] ?? '';
    $data['image'] = $data['image'] ?? null;
    $data['available_tickets'] = $data['available_tickets'] ?? $data['total_tickets'];


    try {
        $eventModel->create($data);

        echo json_encode([
            "status" => true,
            "message" => "Event added successfully"
        ]);

    } catch (Exception $e) {
        echo json_encode([
            "status" => false,
            "message" => "Event add failed",
            "error" => $e->getMessage()
        ]);
    }
}

function getEvents()
{
    global $conn;

    $eventModel = new Event($conn);
    $events = $eventModel->getAll();

    echo json_encode([
        "status" => true,
        "message" => "Events fetched successfully",
        "data" => $events
    ]);
}

function updateEvent()
{
    global $conn;

    $eventModel = new Event($conn);
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || empty($data['id'])) {
        echo json_encode(["status" => false, "message" => "Event id is required"]);
        return;
    }

    $event = $eventModel->findById($data['id']);

    if (!$event) {
        echo json_encode(["status" => false, "message" => "Event not found"]);
        return;
    }

    $data['category_id'] = $data['category_id'] ?? $event['category_id'];
    $data['title'] = $data['title'] ?? $event['title'];
    $data['description'] = $data['description'] ?? $event['description'];
    $data['location'] = $data['location'] ?? $event['location'];
    $data['event_date'] = $data['event_date'] ?? $event['event_date'];
    $data['event_time'] = $data['event_time'] ?? $event['event_time'];
    $data['price'] = $data['price'] ?? $event['price'];
    $data['total_tickets'] = $data['total_tickets'] ?? $event['total_tickets'];
    $data['available_tickets'] = $data['available_tickets'] ?? $event['available_tickets'];
    $data['image'] = $data['image'] ?? $event['image'];

    $eventModel->update($data['id'], $data);

    echo json_encode([
        "status" => true,
        "message" => "Event updated successfully"
    ]);
}

function uploadEventImage()
{

    if (!isset($_FILES['image'])) {
        echo json_encode([
            "status" => false,
            "message" => "Image is required"
        ]);
        return;
    }

    $uploadDir = __DIR__ . "/../uploads/events/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = time() . "_" . preg_replace("/[^a-zA-Z0-9._-]/", "_", basename($_FILES["image"]["name"]));
    $targetPath = $uploadDir . $fileName;

    $dbPath = "uploads/events/" . $fileName;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetPath)) {
        echo json_encode([
            "status" => true,
            "message" => "Image uploaded successfully",
            "image" => $dbPath
        ]);
    } else {
        echo json_encode([
            "status" => false,
            "message" => "Image upload failed"
        ]);
    }
}

function deleteEvent()
{
    global $conn;

    $eventModel = new Event($conn);
    $id = $_GET['id'] ?? '';

    if ($id == '') {
        echo json_encode(["status" => false, "message" => "Event id is required"]);
        return;
    }

    $eventModel->delete($id);

    echo json_encode([
        "status" => true,
        "message" => "Event deleted successfully"
    ]);
}