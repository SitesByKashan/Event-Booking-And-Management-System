<?php

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../models/User.php";

function register() {
    global $conn;

    $userModel = new User($conn);
    $data = json_decode(file_get_contents("php://input"), true);

    $name = $data['name'] ?? '';
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    if ($name == '' || $email == '' || $password == '') {
        echo json_encode(["status" => false, "message" => "All fields are required"]);
        return;
    }

    try {
        $userModel->create($name, $email, $password);

        echo json_encode([
            "status" => true,
            "message" => "User registered successfully"
        ]);

    } catch(Exception $e) {
        echo json_encode([
            "status" => false,
            "message" => "Email already exists"
        ]);
    }
}

function login() {
    global $conn;

    session_start();

    $userModel = new User($conn);
    $data = json_decode(file_get_contents("php://input"), true);

    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    if ($email == '' || $password == '') {
        echo json_encode(["status" => false, "message" => "Email and password are required"]);
        return;
    }

    $user = $userModel->findByEmail($email);

    if (!$user || !password_verify($password, $user['password'])) {
        echo json_encode(["status" => false, "message" => "Invalid email or password"]);
        return;
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];

    echo json_encode([
        "status" => true,
        "message" => "Login successful",
        "data" => [
            "id" => $user['id'],
            "name" => $user['name'],
            "email" => $user['email'],
            "phone" => $user['phone'],
            "role" => $user['role'],
            "status" => $user['status']
        ]
    ]);
}