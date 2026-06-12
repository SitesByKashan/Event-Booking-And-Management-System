<?php

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../models/User.php";
require_once __DIR__ . "/../models/Notification.php";

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
        $otp = (string) random_int(100000, 999999);
        $userModel->create($name, $email, $password, $otp);

        $user = $userModel->findByEmail($email);
        $notificationModel = new Notification($conn);
        $notificationModel->email(
            $user["id"] ?? null,
            $email,
            "Your EventHub verification OTP",
            "Your EventHub verification OTP is {$otp}. It will expire in 10 minutes."
        );

        echo json_encode([
            "status" => true,
            "message" => "Account created. Please verify your email with the OTP sent to your email.",
            "data" => [
                "email" => $email
            ]
        ]);

    } catch(Exception $e) {
        echo json_encode([
            "status" => false,
            "message" => "Email already exists"
        ]);
    }
}

function verifyEmailOtp() {
    global $conn;

    $userModel = new User($conn);
    $data = json_decode(file_get_contents("php://input"), true);

    $email = trim($data["email"] ?? "");
    $otp = trim($data["otp"] ?? "");

    if ($email === "" || $otp === "") {
        echo json_encode([
            "status" => false,
            "message" => "Email and OTP are required"
        ]);
        return;
    }

    $verified = $userModel->verifyEmail($email, $otp);

    if (!$verified) {
        echo json_encode([
            "status" => false,
            "message" => "Invalid or expired OTP"
        ]);
        return;
    }

    $user = $userModel->findByEmail($email);
    $notificationModel = new Notification($conn);
    $notificationModel->createAndEmail(
        $user,
        "Account created successfully",
        "Your EventHub account has been created and your email is verified. You can now login and start booking events.",
        "account"
    );

    echo json_encode([
        "status" => true,
        "message" => "Email verified successfully. You can now login."
    ]);
}

function resendEmailOtp() {
    global $conn;

    $userModel = new User($conn);
    $data = json_decode(file_get_contents("php://input"), true);
    $email = trim($data["email"] ?? "");

    if ($email === "") {
        echo json_encode([
            "status" => false,
            "message" => "Email is required"
        ]);
        return;
    }

    $user = $userModel->findByEmail($email);

    if (!$user) {
        echo json_encode([
            "status" => false,
            "message" => "User not found"
        ]);
        return;
    }

    if ((int) ($user["email_verified"] ?? 0) === 1) {
        echo json_encode([
            "status" => false,
            "message" => "Email is already verified"
        ]);
        return;
    }

    $otp = (string) random_int(100000, 999999);
    $userModel->resendOtp($email, $otp);

    (new Notification($conn))->email(
        $user["id"],
        $email,
        "Your new EventHub verification OTP",
        "Your new EventHub verification OTP is {$otp}. It will expire in 10 minutes."
    );

    echo json_encode([
        "status" => true,
        "message" => "A new OTP has been sent to your email."
    ]);
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

    if ((int) ($user["email_verified"] ?? 0) !== 1) {
        echo json_encode([
            "status" => false,
            "message" => "Please verify your email before login.",
            "requires_verification" => true,
            "email" => $email
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
            "phone" => $user['phone'],
            "role" => $user['role'],
            "status" => $user['status']
        ]
    ]);
}
