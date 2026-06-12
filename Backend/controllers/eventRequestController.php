<?php

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../middleware/authMiddleware.php";
require_once __DIR__ . "/../models/EventRequest.php";
require_once __DIR__ . "/../models/User.php";
require_once __DIR__ . "/../models/Notification.php";

function createEventRequest()
{
    global $conn;

    startSessionSafe();
    $user_id = $_SESSION["user_id"] ?? null;
    $role = $_SESSION["role"] ?? "user";
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$user_id || !$data) {
        echo json_encode(["status" => false, "message" => "Invalid request data"]);
        return;
    }

    if ($role === "vendor") {
        echo json_encode([
            "status" => false,
            "message" => "Vendor accounts cannot create customer event requests. Please use a customer account."
        ]);
        return;
    }

    $required = ["event_type", "city", "guests", "budget", "requirements"];

    foreach ($required as $field) {
        if (!isset($data[$field]) || trim((string) $data[$field]) === "") {
            echo json_encode(["status" => false, "message" => "$field is required"]);
            return;
        }
    }

    $model = new EventRequest($conn);
    $request_id = $model->create($user_id, [
        "event_type" => trim($data["event_type"]),
        "event_date" => $data["event_date"] ?? null,
        "city" => trim($data["city"]),
        "guests" => (int) $data["guests"],
        "budget" => (float) $data["budget"],
        "requirements" => trim($data["requirements"])
    ]);

    echo json_encode([
        "status" => true,
        "message" => "Custom event request posted successfully",
        "data" => ["request_id" => $request_id]
    ]);
}

function getUserEventRequests()
{
    global $conn;

    startSessionSafe();
    $user_id = $_SESSION["user_id"] ?? null;

    if (!$user_id) {
        echo json_encode(["status" => false, "message" => "Unauthorized access"]);
        return;
    }

    $model = new EventRequest($conn);

    echo json_encode([
        "status" => true,
        "message" => "Event requests fetched successfully",
        "data" => $model->getByUser($user_id)
    ]);
}

function getEventRequests()
{
    global $conn;

    startSessionSafe();
    $user_id = $_SESSION["user_id"] ?? null;
    $role = $_SESSION["role"] ?? "user";
    $model = new EventRequest($conn);
    $excludeUser = $role === "admin" ? null : $user_id;

    echo json_encode([
        "status" => true,
        "message" => "Event requests fetched successfully",
        "data" => $model->getAll($excludeUser),
        "quotes" => $model->getAllQuotes()
    ]);
}

function createEventQuote()
{
    global $conn;

    startSessionSafe();

    $data = json_decode(file_get_contents("php://input"), true);
    $vendor_user_id = $_SESSION["user_id"] ?? null;

    if (!$data || !$vendor_user_id) {
        echo json_encode(["status" => false, "message" => "Invalid quote data"]);
        return;
    }

    if (($_SESSION["role"] ?? "user") !== "vendor" && ($_SESSION["role"] ?? "user") !== "admin") {
        echo json_encode(["status" => false, "message" => "Please activate vendor mode before sending quotes"]);
        return;
    }

    $required = ["request_id", "vendor_name", "quote_amount", "message", "contact_info"];

    foreach ($required as $field) {
        if (!isset($data[$field]) || trim((string) $data[$field]) === "") {
            echo json_encode(["status" => false, "message" => "$field is required"]);
            return;
        }
    }

    $model = new EventRequest($conn);
    $created = $model->createQuote([
        "request_id" => (int) $data["request_id"],
        "vendor_user_id" => $vendor_user_id,
        "vendor_name" => trim($data["vendor_name"]),
        "quote_amount" => (float) $data["quote_amount"],
        "message" => trim($data["message"]),
        "contact_info" => trim($data["contact_info"])
    ]);

    if (!$created) {
        echo json_encode([
            "status" => false,
            "message" => "You cannot send a quote on your own event request"
        ]);
        return;
    }

    if ($created) {
        $requestOwner = getEventRequestOwner((int) $data["request_id"]);
        $vendor = (new User($conn))->findById($vendor_user_id);

        if ($requestOwner) {
            (new Notification($conn))->createAndEmail(
                $requestOwner,
                "New vendor quote received",
                ($vendor["name"] ?? "A vendor") . " sent a quote of Rs. " . number_format((float) $data["quote_amount"]) . " for your custom event request.",
                "quote"
            );
        }
    }

    echo json_encode([
        "status" => $created,
        "message" => $created ? "Quote sent successfully" : "Quote send failed"
    ]);
}

function getCustomerQuotes()
{
    global $conn;

    startSessionSafe();
    $user_id = $_SESSION["user_id"] ?? null;

    if (!$user_id) {
        echo json_encode(["status" => false, "message" => "Unauthorized access"]);
        return;
    }

    $model = new EventRequest($conn);

    echo json_encode([
        "status" => true,
        "message" => "Quotes fetched successfully",
        "data" => $model->getQuotesByUserRequests($user_id)
    ]);
}

function getVendorQuotes()
{
    global $conn;

    startSessionSafe();
    $vendor_user_id = $_SESSION["user_id"] ?? null;

    if (!$vendor_user_id) {
        echo json_encode(["status" => false, "message" => "Unauthorized access"]);
        return;
    }

    $model = new EventRequest($conn);

    echo json_encode([
        "status" => true,
        "message" => "Vendor quotes fetched successfully",
        "data" => $model->getQuotesByVendor($vendor_user_id)
    ]);
}

function acceptEventQuote()
{
    global $conn;

    startSessionSafe();
    $user_id = $_SESSION["user_id"] ?? null;
    $data = json_decode(file_get_contents("php://input"), true);
    $quote_id = $data["quote_id"] ?? "";

    if (!$user_id || $quote_id == "") {
        echo json_encode(["status" => false, "message" => "Quote id is required"]);
        return;
    }

    $model = new EventRequest($conn);
    $accepted = $model->acceptQuote((int) $quote_id, $user_id);

    if ($accepted) {
        $quoteInfo = getQuoteNotificationInfo((int) $quote_id);

        if ($quoteInfo && !empty($quoteInfo["vendor_user_id"])) {
            (new Notification($conn))->createAndEmail(
                [
                    "id" => $quoteInfo["vendor_user_id"],
                    "email" => $quoteInfo["vendor_email"]
                ],
                "Your quote was accepted",
                ($quoteInfo["customer_name"] ?? "A customer") . " accepted your quote for " . ($quoteInfo["event_type"] ?? "custom event") . ". Open Vendor Desk to chat.",
                "accepted"
            );
        }
    }

    echo json_encode([
        "status" => (bool) $accepted,
        "message" => $accepted ? "Quote accepted. Messenger is now available." : "Quote accept failed"
    ]);
}

function sendQuoteMessage()
{
    global $conn;

    startSessionSafe();
    $sender_id = $_SESSION["user_id"] ?? null;
    $data = json_decode(file_get_contents("php://input"), true);
    $quote_id = $data["quote_id"] ?? "";
    $message = trim($data["message"] ?? "");

    if (!$sender_id || $quote_id == "" || $message == "") {
        echo json_encode(["status" => false, "message" => "Message is required"]);
        return;
    }

    $model = new EventRequest($conn);
    $sent = $model->sendMessage((int) $quote_id, $sender_id, $message);

    if ($sent) {
        notifyQuoteMessage((int) $quote_id, $sender_id, $message);
    }

    echo json_encode([
        "status" => (bool) $sent,
        "message" => $sent ? "Message sent" : "Message send failed"
    ]);
}

function getQuoteMessages()
{
    global $conn;

    startSessionSafe();
    $user_id = $_SESSION["user_id"] ?? null;
    $quote_id = $_GET["quote_id"] ?? "";

    if (!$user_id || $quote_id == "") {
        echo json_encode(["status" => false, "message" => "Quote id is required"]);
        return;
    }

    $model = new EventRequest($conn);
    $messages = $model->getMessages((int) $quote_id, $user_id);

    if ($messages === null) {
        echo json_encode(["status" => false, "message" => "Chat not available for this quote"]);
        return;
    }

    echo json_encode([
        "status" => true,
        "message" => "Messages fetched successfully",
        "data" => $messages
    ]);
}

function getEventRequestOwner($request_id)
{
    global $conn;

    $stmt = $conn->prepare("SELECT users.id, users.email, users.name
        FROM event_requests
        INNER JOIN users ON event_requests.user_id = users.id
        WHERE event_requests.id = :request_id");
    $stmt->execute([":request_id" => $request_id]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getQuoteNotificationInfo($quote_id)
{
    global $conn;

    $stmt = $conn->prepare("SELECT
            event_quotes.vendor_user_id,
            vendor.email AS vendor_email,
            customer.name AS customer_name,
            event_requests.event_type,
            event_requests.user_id AS customer_id,
            customer.email AS customer_email
        FROM event_quotes
        INNER JOIN event_requests ON event_quotes.request_id = event_requests.id
        LEFT JOIN users vendor ON event_quotes.vendor_user_id = vendor.id
        INNER JOIN users customer ON event_requests.user_id = customer.id
        WHERE event_quotes.id = :quote_id");
    $stmt->execute([":quote_id" => $quote_id]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function notifyQuoteMessage($quote_id, $sender_id, $message)
{
    global $conn;

    $info = getQuoteNotificationInfo($quote_id);

    if (!$info) {
        return;
    }

    $receiver = null;

    if ((int) $sender_id === (int) $info["customer_id"] && !empty($info["vendor_user_id"])) {
        $receiver = [
            "id" => $info["vendor_user_id"],
            "email" => $info["vendor_email"]
        ];
    } elseif ((int) $sender_id === (int) $info["vendor_user_id"]) {
        $receiver = [
            "id" => $info["customer_id"],
            "email" => $info["customer_email"]
        ];
    }

    if (!$receiver) {
        return;
    }

    (new Notification($conn))->createAndEmail(
        $receiver,
        "New quote chat message",
        "You received a new message: " . substr($message, 0, 120),
        "message"
    );
}
