<?php

class EventRequest
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->ensureTables();
    }

    private function ensureTables()
    {
        $this->conn->exec("CREATE TABLE IF NOT EXISTS event_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            event_type VARCHAR(80) NOT NULL,
            event_date DATE NULL,
            city VARCHAR(120) NOT NULL,
            guests INT NOT NULL DEFAULT 0,
            budget DECIMAL(12,2) NOT NULL DEFAULT 0,
            requirements TEXT NOT NULL,
            status VARCHAR(30) NOT NULL DEFAULT 'open',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $this->conn->exec("CREATE TABLE IF NOT EXISTS event_quotes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            request_id INT NOT NULL,
            vendor_user_id INT NULL,
            vendor_name VARCHAR(120) NOT NULL,
            quote_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
            message TEXT NOT NULL,
            contact_info VARCHAR(180) NOT NULL,
            status VARCHAR(30) NOT NULL DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $this->ensureColumn("event_quotes", "vendor_user_id", "INT NULL");
        $this->ensureColumn("event_quotes", "status", "VARCHAR(30) NOT NULL DEFAULT 'pending'");

        $this->conn->exec("CREATE TABLE IF NOT EXISTS quote_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            quote_id INT NOT NULL,
            sender_id INT NOT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    }

    private function ensureColumn($table, $column, $definition)
    {
        try {
            $stmt = $this->conn->prepare("SHOW COLUMNS FROM {$table} LIKE :column_name");
            $stmt->execute([":column_name" => $column]);

            if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->conn->exec("ALTER TABLE {$table} ADD COLUMN {$column} {$definition}");
            }
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    public function create($user_id, $data)
    {
        $stmt = $this->conn->prepare("INSERT INTO event_requests
            (user_id, event_type, event_date, city, guests, budget, requirements)
            VALUES (:user_id, :event_type, :event_date, :city, :guests, :budget, :requirements)");

        $stmt->execute([
            ":user_id" => $user_id,
            ":event_type" => $data["event_type"],
            ":event_date" => $data["event_date"] ?: null,
            ":city" => $data["city"],
            ":guests" => $data["guests"],
            ":budget" => $data["budget"],
            ":requirements" => $data["requirements"]
        ]);

        return $this->conn->lastInsertId();
    }

    public function getByUser($user_id)
    {
        $stmt = $this->conn->prepare("SELECT
                event_requests.*,
                (SELECT COUNT(*) FROM event_quotes WHERE event_quotes.request_id = event_requests.id) AS quote_count,
                (SELECT MIN(quote_amount) FROM event_quotes WHERE event_quotes.request_id = event_requests.id) AS starting_quote
            FROM event_requests
            WHERE event_requests.user_id = :user_id
            ORDER BY event_requests.created_at DESC");

        $stmt->execute([":user_id" => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll($exclude_user_id = null)
    {
        $where = "";
        $params = [];

        if ($exclude_user_id !== null) {
            $where = "WHERE event_requests.user_id != :exclude_user_id";
            $params[":exclude_user_id"] = $exclude_user_id;
        }

        $stmt = $this->conn->prepare("SELECT
                event_requests.*,
                users.name AS user_name,
                users.email AS user_email,
                (SELECT COUNT(*) FROM event_quotes WHERE event_quotes.request_id = event_requests.id) AS quote_count,
                (SELECT MIN(quote_amount) FROM event_quotes WHERE event_quotes.request_id = event_requests.id) AS starting_quote,
                (SELECT COUNT(*) FROM event_quotes WHERE event_quotes.request_id = event_requests.id AND event_quotes.status = 'accepted') AS accepted_quote_count
            FROM event_requests
            INNER JOIN users ON event_requests.user_id = users.id
            {$where}
            ORDER BY event_requests.created_at DESC");

        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllQuotes()
    {
        $stmt = $this->conn->prepare("SELECT
                event_quotes.*,
                event_requests.event_type,
                event_requests.city,
                users.name AS customer_name
            FROM event_quotes
            INNER JOIN event_requests ON event_quotes.request_id = event_requests.id
            INNER JOIN users ON event_requests.user_id = users.id
            ORDER BY event_quotes.created_at DESC");

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createQuote($data)
    {
        $owner = $this->conn->prepare("SELECT user_id FROM event_requests WHERE id = :request_id");
        $owner->execute([":request_id" => $data["request_id"]]);
        $request = $owner->fetch(PDO::FETCH_ASSOC);

        if ($request && (int) $request["user_id"] === (int) ($data["vendor_user_id"] ?? 0)) {
            return false;
        }

        $stmt = $this->conn->prepare("INSERT INTO event_quotes
            (request_id, vendor_user_id, vendor_name, quote_amount, message, contact_info)
            VALUES (:request_id, :vendor_user_id, :vendor_name, :quote_amount, :message, :contact_info)");

        return $stmt->execute([
            ":request_id" => $data["request_id"],
            ":vendor_user_id" => $data["vendor_user_id"] ?? null,
            ":vendor_name" => $data["vendor_name"],
            ":quote_amount" => $data["quote_amount"],
            ":message" => $data["message"],
            ":contact_info" => $data["contact_info"]
        ]);
    }

    public function getQuotesByUserRequests($user_id)
    {
        $stmt = $this->conn->prepare("SELECT
                event_quotes.*,
                event_requests.event_type,
                event_requests.city,
                event_requests.user_id AS customer_id
            FROM event_quotes
            INNER JOIN event_requests ON event_quotes.request_id = event_requests.id
            WHERE event_requests.user_id = :user_id
            ORDER BY event_quotes.created_at DESC");

        $stmt->execute([":user_id" => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getQuotesByVendor($vendor_user_id)
    {
        $stmt = $this->conn->prepare("SELECT
                event_quotes.*,
                event_requests.event_type,
                event_requests.city,
                event_requests.guests,
                event_requests.budget,
                users.name AS customer_name
            FROM event_quotes
            INNER JOIN event_requests ON event_quotes.request_id = event_requests.id
            INNER JOIN users ON event_requests.user_id = users.id
            WHERE event_quotes.vendor_user_id = :vendor_user_id
            ORDER BY event_quotes.created_at DESC");

        $stmt->execute([":vendor_user_id" => $vendor_user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function acceptQuote($quote_id, $user_id)
    {
        $stmt = $this->conn->prepare("UPDATE event_quotes
            INNER JOIN event_requests ON event_quotes.request_id = event_requests.id
            SET event_quotes.status = 'accepted', event_requests.status = 'quoted'
            WHERE event_quotes.id = :quote_id AND event_requests.user_id = :user_id");

        return $stmt->execute([
            ":quote_id" => $quote_id,
            ":user_id" => $user_id
        ]);
    }

    public function sendMessage($quote_id, $sender_id, $message)
    {
        $stmt = $this->conn->prepare("INSERT INTO quote_messages (quote_id, sender_id, message)
            VALUES (:quote_id, :sender_id, :message)");

        return $stmt->execute([
            ":quote_id" => $quote_id,
            ":sender_id" => $sender_id,
            ":message" => $message
        ]);
    }

    public function getMessages($quote_id, $user_id)
    {
        $auth = $this->conn->prepare("SELECT event_quotes.id
            FROM event_quotes
            INNER JOIN event_requests ON event_quotes.request_id = event_requests.id
            WHERE event_quotes.id = :quote_id
            AND (event_requests.user_id = :user_id OR event_quotes.vendor_user_id = :user_id)");
        $auth->execute([
            ":quote_id" => $quote_id,
            ":user_id" => $user_id
        ]);

        if (!$auth->fetch(PDO::FETCH_ASSOC)) {
            return null;
        }

        $stmt = $this->conn->prepare("SELECT quote_messages.*, users.name AS sender_name
            FROM quote_messages
            INNER JOIN users ON quote_messages.sender_id = users.id
            WHERE quote_messages.quote_id = :quote_id
            ORDER BY quote_messages.created_at ASC");
        $stmt->execute([":quote_id" => $quote_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
