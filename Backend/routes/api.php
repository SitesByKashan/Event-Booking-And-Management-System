<?php

require_once __DIR__ . "/../controllers/authController.php";
require_once __DIR__ . "/../controllers/eventController.php";
require_once __DIR__ . "/../controllers/bookingController.php";
require_once __DIR__ . "/../controllers/categoryController.php";
require_once __DIR__ . "/../controllers/dashboardController.php";
require_once __DIR__ . "/../controllers/userController.php";

require_once __DIR__ . "/../middleware/authMiddleware.php";
require_once __DIR__ . "/../middleware/adminMiddleware.php";

$action = $_GET['action'] ?? '';

switch ($action) {

    case 'register':
        register();
        break;

    case 'login':
        login();
        break;

    case 'add_event':
        // adminMiddleware();
        addEvent();
        break;

    case 'get_events':
        getEvents();
        break;

    case 'update_event':
        // adminMiddleware();
        updateEvent();
        break;

    case 'delete_event':
        // adminMiddleware();
        deleteEvent();
        break;

    case 'upload_event_image':
        // adminMiddleware();
        uploadEventImage();
        break;

    case 'create_booking':
        // authMiddleware();
        createBooking();
        break;

    case 'get_bookings':
        // adminMiddleware();
        getBookings();
        break;

    case 'get_user_bookings':
        // authMiddleware();
        getUserBookings();
        break;

    case 'cancel_booking':
        // authMiddleware();
        cancelBooking();
        break;

    case 'get_users':
        // adminMiddleware();
        getUsers();
        break;

    case 'add_category':
        // adminMiddleware();
        addCategory();
        break;

    case 'get_categories':
        getCategories();
        break;

    case 'update_category':
        // adminMiddleware();
        updateCategory();
        break;

    case 'delete_category':
        // adminMiddleware();
        deleteCategory();
        break;

    case 'dashboard_stats':
        // adminMiddleware();
        dashboardStats();
        break;

    case 'chart_stats':
        // adminMiddleware();
        chartStats();
        break;

    case 'get_profile':
        getProfile();
        break;

    case 'update_profile':
        updateProfile();
        break;

    case 'update_password':
        updatePassword();
        break;
    default:
        echo json_encode([
            "status" => false,
            "message" => "Invalid API action"
        ]);
}