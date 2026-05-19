<?php

require_once __DIR__ . "/../controllers/authController.php";
require_once __DIR__ . "/../controllers/eventController.php";
require_once __DIR__ . "/../controllers/bookingController.php";

$action = $_GET['action'] ?? '';

switch ($action) {

    case 'register':
        register();
        break;

    case 'login':
        login();
        break;

    case 'add_event':
        addEvent();
        break;

    case 'get_events':
        getEvents();
        break;

    case 'create_booking':
        createBooking();
        break;

    default:
        echo json_encode([
            "status" => false,
            "message" => "Invalid API action"
        ]);
}

?>