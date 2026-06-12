<?php

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../models/User.php";
require_once __DIR__ . "/../middleware/authMiddleware.php";
require_once __DIR__ . "/../models/Notification.php";

function getUsers() {
    global $conn;

    $userModel = new User($conn);
    $users = $userModel->getAllUsers();

    echo json_encode([
        "status" => true,
        "message" => "Users fetched successfully",
        "data" => $users
    ]);
}

function getProfile() {
    global $conn;

    startSessionSafe();

    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            "status" => false,
            "message" => "Please login first"
        ]);
        return;
    }

    $userModel = new User($conn);
    $user = $userModel->findById($_SESSION['user_id']);

    echo json_encode([
        "status" => true,
        "data" => $user
    ]);
}

function updateProfile() {
    global $conn;

    startSessionSafe();

    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            "status" => false,
            "message" => "Please login first"
        ]);
        return;
    }

    $data = json_decode(file_get_contents("php://input"), true);

    $userModel = new User($conn);

    $userModel->updateProfile($_SESSION['user_id'], $data);

    echo json_encode([
        "status" => true,
        "message" => "Profile updated successfully"
    ]);
}

function updatePassword() {
    global $conn;

    startSessionSafe();

    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            "status" => false,
            "message" => "Please login first"
        ]);
        return;
    }

    $data = json_decode(file_get_contents("php://input"), true);

    $userModel = new User($conn);
    $user = $userModel->findById($_SESSION['user_id']);

    $fullUser = $userModel->findByEmail($user['email']);

    if (!password_verify($data['current_password'], $fullUser['password'])) {
        echo json_encode([
            "status" => false,
            "message" => "Current password is incorrect"
        ]);
        return;
    }

    $userModel->updatePassword($_SESSION['user_id'], $data['new_password']);

    echo json_encode([
        "status" => true,
        "message" => "Password updated successfully"
    ]);
}

function becomeVendor() {
    global $conn;

    startSessionSafe();

    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            "status" => false,
            "message" => "Please login first"
        ]);
        return;
    }

    $userModel = new User($conn);
    $updated = $userModel->updateRole($_SESSION['user_id'], "vendor");

    if ($updated) {
        $_SESSION['role'] = "vendor";
        $notificationModel = new Notification($conn);
        $notificationModel->createAndEmail(
            $userModel->findById($_SESSION['user_id']),
            "Vendor mode activated",
            "You can now receive event requests and send quotations from Vendor Desk.",
            "vendor"
        );
    }

    echo json_encode([
        "status" => (bool) $updated,
        "message" => $updated ? "Vendor mode activated successfully" : "Vendor mode activation failed",
        "data" => [
            "role" => "vendor"
        ]
    ]);
}
