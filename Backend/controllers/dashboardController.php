<?php

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../models/Dashboard.php";

function dashboardStats() {
    global $conn;

    $dashboardModel = new Dashboard($conn);

    echo json_encode([
        "status" => true,
        "data" => $dashboardModel->stats()
    ]);
}

function chartStats() {
    global $conn;

    $revenueSql = "SELECT 
                    DATE_FORMAT(created_at, '%b') AS month,
                    SUM(total_amount) AS total
                   FROM bookings
                   WHERE status = 'confirmed'
                   GROUP BY MONTH(created_at), DATE_FORMAT(created_at, '%b')
                   ORDER BY MONTH(created_at)";

    $stmt = $conn->prepare($revenueSql);
    $stmt->execute();
    $revenueData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $revenueLabels = [];
    $revenueValues = [];

    foreach ($revenueData as $row) {
        $revenueLabels[] = $row['month'];
        $revenueValues[] = $row['total'];
    }

    $categorySql = "SELECT 
                        categories.name,
                        COUNT(events.id) AS total
                    FROM categories
                    LEFT JOIN events ON events.category_id = categories.id
                    GROUP BY categories.id, categories.name";

    $stmt = $conn->prepare($categorySql);
    $stmt->execute();
    $categoryData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $categoryLabels = [];
    $categoryValues = [];

    foreach ($categoryData as $row) {
        $categoryLabels[] = $row['name'];
        $categoryValues[] = $row['total'];
    }

    echo json_encode([
        "status" => true,
        "data" => [
            "revenue" => [
                "labels" => $revenueLabels,
                "values" => $revenueValues
            ],
            "categories" => [
                "labels" => $categoryLabels,
                "values" => $categoryValues
            ]
        ]
    ]);
}

function userDashboardStats()
{
    global $conn;

    $user_id = $_GET['user_id'] ?? '';

    if ($user_id == '') {
        echo json_encode([
            "status" => false,
            "message" => "User id is required"
        ]);
        return;
    }

    $sql = "SELECT
                COUNT(*) AS total_bookings,
                SUM(total_amount) AS total_spent
            FROM bookings
            WHERE user_id = :user_id
            AND status = 'confirmed'";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ":user_id" => $user_id
    ]);

    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => true,
        "data" => [
            "total_bookings" => $stats['total_bookings'] ?? 0,
            "total_spent" => $stats['total_spent'] ?? 0
        ]
    ]);
}