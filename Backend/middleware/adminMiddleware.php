<?php

require_once __DIR__ . "/authMiddleware.php";

function adminMiddleware() {
    startSessionSafe();

    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            "status" => false,
            "message" => "Unauthorized access. Please login first."
        ]);
        exit;
    }

    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        echo json_encode([
            "status" => false,
            "message" => "Access denied. Admin only."
        ]);
        exit;
    }

    return true;
}