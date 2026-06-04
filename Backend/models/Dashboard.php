<?php

class Dashboard {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function stats() {
        $users = $this->conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $events = $this->conn->query("SELECT COUNT(*) FROM events WHERE status='active'")->fetchColumn();
        $bookings = $this->conn->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
        $revenue = $this->conn->query("SELECT COALESCE(SUM(total_amount), 0) FROM bookings WHERE status='confirmed'")->fetchColumn();

        return [
            "total_users" => $users,
            "total_events" => $events,
            "total_bookings" => $bookings,
            "total_revenue" => $revenue
        ];
    }
}