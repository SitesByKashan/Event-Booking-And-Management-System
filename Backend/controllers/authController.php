<?php

require_once __DIR__ . "/../config/db.php";

function register() {

    global $conn;

    $data = json_decode(file_get_contents("php://input"), true);

    $name = $data['name'] ?? '';
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    if ($name == '' || $email == '' || $password == '') {

        echo json_encode([
            "status" => false,
            "message" => "All fields are required"
        ]);

        return;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (name, email, password)
            VALUES (:name, :email, :password)";

    $stmt = $conn->prepare($sql);

    try {

        $stmt->execute([
            ":name" => $name,
            ":email" => $email,
            ":password" => $hashedPassword
        ]);

        echo json_encode([
            "status" => true,
            "message" => "User registered successfully"
        ]);

    } catch(PDOException $e) {

        echo json_encode([
            "status" => false,
            "message" => "Email already exists"
        ]);
    }
}

function login() {

    global $conn;

    session_start();

    $data = json_decode(file_get_contents("php://input"), true);

    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    if ($email == '' || $password == '') {

        echo json_encode([
            "status" => false,
            "message" => "Email and password are required"
        ]);

        return;
    }

    $sql = "SELECT * FROM users WHERE email = :email";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        ":email" => $email
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {

        echo json_encode([
            "status" => false,
            "message" => "Invalid email or password"
        ]);

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
            "role" => $user['role']
        ]
    ]);
}

?>