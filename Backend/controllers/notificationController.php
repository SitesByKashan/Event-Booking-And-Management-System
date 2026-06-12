<?php

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../middleware/authMiddleware.php";
require_once __DIR__ . "/../models/Notification.php";

function getNotifications()
{
    global $conn;

    startSessionSafe();
    $user_id = $_SESSION["user_id"] ?? null;

    if (!$user_id) {
        echo json_encode(["status" => false, "message" => "Please login first"]);
        return;
    }

    $model = new Notification($conn);
    $notifications = $model->getByUser($user_id);
    $unread = array_filter($notifications, function ($item) {
        return (int) $item["is_read"] === 0;
    });

    echo json_encode([
        "status" => true,
        "message" => "Notifications fetched successfully",
        "data" => $notifications,
        "unread_count" => count($unread)
    ]);
}

function markNotificationsRead()
{
    global $conn;

    startSessionSafe();
    $user_id = $_SESSION["user_id"] ?? null;

    if (!$user_id) {
        echo json_encode(["status" => false, "message" => "Please login first"]);
        return;
    }

    $model = new Notification($conn);
    $model->markAllRead($user_id);

    echo json_encode([
        "status" => true,
        "message" => "Notifications marked as read"
    ]);
}

function getAdminNotifications()
{
    global $conn;

    $model = new Notification($conn);
    $notifications = $model->getAll();

    echo json_encode([
        "status" => true,
        "message" => "Admin notifications fetched successfully",
        "data" => $notifications,
        "unread_count" => count(array_filter($notifications, function ($item) {
            return (int) $item["is_read"] === 0;
        }))
    ]);
}
