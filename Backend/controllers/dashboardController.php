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