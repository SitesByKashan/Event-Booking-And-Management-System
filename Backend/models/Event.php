<?php

class Event {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $sql = "INSERT INTO events 
        (category_id, title, description, location, event_date, event_time, price, total_tickets, available_tickets, image)
        VALUES 
        (:category_id, :title, :description, :location, :event_date, :event_time, :price, :total_tickets, :available_tickets, :image)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ":category_id" => $data['category_id'],
            ":title" => $data['title'],
            ":description" => $data['description'] ?? '',
            ":location" => $data['location'] ?? '',
            ":event_date" => $data['event_date'],
            ":event_time" => $data['event_time'],
            ":price" => $data['price'],
            ":total_tickets" => $data['total_tickets'],
            ":available_tickets" => $data['available_tickets'] ?? $data['total_tickets'],
            ":image" => $data['image'] ?? null
        ]);
    }

    public function getAll() {
        $sql = "SELECT events.*, categories.name AS category_name
                FROM events
                LEFT JOIN categories ON events.category_id = categories.id
                WHERE events.status = 'active'
                ORDER BY events.event_date ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $sql = "SELECT * FROM events WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([":id" => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $data) {
        $sql = "UPDATE events
                SET category_id = :category_id,
                    title = :title,
                    description = :description,
                    location = :location,
                    event_date = :event_date,
                    event_time = :event_time,
                    price = :price,
                    total_tickets = :total_tickets,
                    available_tickets = :available_tickets,
                    image = :image
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ":id" => $id,
            ":category_id" => $data['category_id'],
            ":title" => $data['title'],
            ":description" => $data['description'] ?? '',
            ":location" => $data['location'] ?? '',
            ":event_date" => $data['event_date'],
            ":event_time" => $data['event_time'],
            ":price" => $data['price'],
            ":total_tickets" => $data['total_tickets'],
            ":available_tickets" => $data['available_tickets'],
            ":image" => $data['image'] ?? null
        ]);
    }

    public function reduceTickets($event_id, $tickets) {
        $sql = "UPDATE events 
                SET available_tickets = available_tickets - :tickets
                WHERE id = :event_id";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ":tickets" => $tickets,
            ":event_id" => $event_id
        ]);
    }

    public function delete($id) {
        $sql = "UPDATE events SET status = 'inactive' WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([":id" => $id]);
    }
}