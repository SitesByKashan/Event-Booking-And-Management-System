<?php

function startSessionSafe() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function authMiddleware() {
    startSessionSafe();

    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            "status" => false,
            "message" => "Unauthorized access. Please login first."
        ]);
        exit;
    }

    return true;
}