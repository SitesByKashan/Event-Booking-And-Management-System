<?php

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../models/Event.php";
require_once __DIR__ . "/../models/Dashboard.php";

function aiAssistant()
{
    global $conn;

    $data = json_decode(file_get_contents("php://input"), true);
    $message = trim($data['message'] ?? '');

    if ($message === '') {
        echo json_encode([
            "status" => false,
            "message" => "Message is required"
        ]);
        return;
    }

    $eventModel = new Event($conn);
    $events = $eventModel->getAll();
    $platformStats = getAiPlatformStats();
    $reply = buildAssistantReply($message, $events, $platformStats);

    echo json_encode([
        "status" => true,
        "message" => "AI assistant replied successfully",
        "data" => [
            "reply" => $reply,
            "stats" => $platformStats,
            "suggestions" => buildAssistantSuggestions($events)
        ]
    ]);
}

function getAiPlatformStats()
{
    global $conn;

    $stats = [
        "events" => 0,
        "custom_requests" => 0,
        "quotes" => 0,
        "refunds_pending" => 0,
        "notifications" => 0
    ];

    $queries = [
        "events" => "SELECT COUNT(*) FROM events WHERE status='active'",
        "custom_requests" => "SELECT COUNT(*) FROM event_requests",
        "quotes" => "SELECT COUNT(*) FROM event_quotes",
        "refunds_pending" => "SELECT COUNT(*) FROM refund_requests WHERE status='pending'",
        "notifications" => "SELECT COUNT(*) FROM notifications"
    ];

    foreach ($queries as $key => $sql) {
        try {
            $stats[$key] = (int) $conn->query($sql)->fetchColumn();
        } catch (Exception $e) {
            $stats[$key] = 0;
        }
    }

    return $stats;
}

function aiAdminSummary()
{
    global $conn;

    $dashboardModel = new Dashboard($conn);
    $stats = $dashboardModel->stats();

    $pending = (int) $conn->query("SELECT COUNT(*) FROM bookings WHERE status='pending'")->fetchColumn();
    $confirmed = (int) $conn->query("SELECT COUNT(*) FROM bookings WHERE status='confirmed'")->fetchColumn();
    $cancelled = (int) $conn->query("SELECT COUNT(*) FROM bookings WHERE status='cancelled'")->fetchColumn();

    $popularSql = "SELECT categories.name, COUNT(bookings.id) AS total
                   FROM bookings
                   INNER JOIN events ON bookings.event_id = events.id
                   LEFT JOIN categories ON events.category_id = categories.id
                   GROUP BY categories.id, categories.name
                   ORDER BY total DESC
                   LIMIT 1";
    $popularStmt = $conn->prepare($popularSql);
    $popularStmt->execute();
    $popular = $popularStmt->fetch(PDO::FETCH_ASSOC);

    $summary = "AI Admin Summary: You have {$stats['total_bookings']} total bookings, {$pending} pending approvals, {$confirmed} confirmed bookings and Rs. " .
        number_format((float) $stats['total_revenue']) . " confirmed revenue.";

    if ($popular && !empty($popular['name'])) {
        $summary .= " Your most active category is {$popular['name']}, so promote it on the home page for better conversions.";
    } else {
        $summary .= " Add more categorized bookings to unlock stronger trend insights.";
    }

    if ($pending > 0) {
        $summary .= " Priority action: review pending bookings before the exhibition demo.";
    }

    echo json_encode([
        "status" => true,
        "message" => "AI admin summary generated successfully",
        "data" => [
            "summary" => $summary,
            "pending_bookings" => $pending,
            "confirmed_bookings" => $confirmed,
            "cancelled_bookings" => $cancelled
        ]
    ]);
}

function buildAssistantReply($message, $events, $platformStats = [])
{
    $text = strtolower($message);
    $budget = extractBudget($text);
    $guestCount = extractGuestCount($text);
    $eventType = extractEventType($text);
    $city = extractCity($text);
    $matchedEvents = matchEvents($text, $events, $budget, $guestCount);

    if (containsAny($text, ["otp", "verify", "verification", "email"])) {
        return buildOtpEmailReply();
    }

    if (containsAny($text, ["vendor", "quote", "quotation", "custom request", "custom event", "marketplace"])) {
        return buildMarketplaceReply($platformStats);
    }

    if (containsAny($text, ["notification", "notify", "bell", "email alert"])) {
        return buildNotificationReply($platformStats);
    }

    if (containsAny($text, ["admin refund", "approve refund", "refund queue", "payment refund"])) {
        return buildAdminRefundReply($platformStats);
    }

    if (containsAny($text, ["chat", "message", "messenger"])) {
        return "Quote Messenger workflow:\n1. Customer custom event request post karta hai.\n2. Vendor quote send karta hai.\n3. Customer quote accept karta hai.\n4. Accepted quote ke baad customer aur vendor dono website ke andar messenger se chat kar sakte hain.\n5. Har chat message receiver ko website notification aur email alert bhejta hai.";
    }

    if ($eventType || containsAny($text, ["recommend", "suggest", "best", "package", "plan"])) {
        return buildPlannerReply($eventType, $city, $budget, $guestCount, $matchedEvents);
    }

    if (containsWord($text, ["hello", "hi", "salam", "assalam", "hey"])) {
        return "Assalam o Alaikum! Main EventHub AI Assistant hu. Aap mujhe event type, budget, guests aur city batayen, main best event/package recommend kar dunga.";
    }

    if (containsAny($text, ["budget", "price", "cost", "estimate", "kitna", "under"])) {
        return buildBudgetReply($budget, $guestCount, $matchedEvents);
    }

    if (containsAny($text, ["book", "booking", "reserve", "ticket"])) {
        return "Booking ke liye Events page par apna event select karein, Book Now press karein, tickets enter karein aur dummy payment complete karein. Admin panel mein booking pending/confirmed status ke sath show hogi.";
    }

    if (containsAny($text, ["cancel", "refund"])) {
        return "Refund/cancellation flow:\n- User My Bookings se custom refund modal submit karta hai.\n- Request admin Payments page ke Refund Requests section mein jati hai.\n- Admin Approve Refund click karta hai.\n- System payment ko refunded, booking ko cancelled, aur user ko notification/email send karta hai.\nPending refund requests: " . ($platformStats["refunds_pending"] ?? 0) . ".";
    }

    if (containsAny($text, ["admin", "dashboard", "analytics", "report"])) {
        return "Admin AI overview:\n- Dashboard: revenue, bookings, events, users and AI summary.\n- Payments: transactions plus refund approval queue.\n- Requests: customer event requests and vendor quote activity.\n- Notifications: real database activity across bookings, refunds, quotes, vendors and messages.\nCurrent platform snapshot: {$platformStats['events']} active events, {$platformStats['custom_requests']} custom requests, {$platformStats['quotes']} vendor quotes.";
    }

    return buildRecommendationReply($matchedEvents, $budget, $guestCount);
}

function buildOtpEmailReply()
{
    return "Email verification flow:\n1. User signup karta hai.\n2. EventHub Gmail SMTP se 6-digit OTP send hota hai.\n3. Signup ke baad OTP screen open hoti hai.\n4. OTP verify hone par account email_verified ho jata hai.\n5. Welcome email send hoti hai.\nLogin unverified account ko block karta hai aur OTP screen par redirect karta hai.";
}

function buildMarketplaceReply($stats)
{
    return "Vendor marketplace flow:\n- Customer custom event request create karta hai with type, city, date, guests, budget and requirements.\n- Vendor account apni request create nahi kar sakta; woh Vendor Desk se dusre customers ko quotes bhejta hai.\n- Vendor apni hi request par quote nahi bhej sakta.\n- Customer quote accept karta hai, phir messenger unlock hota hai.\n- Admin Requests page par all requests and vendor quote activity monitor hoti hai.\n\nCurrent marketplace: " . ($stats["custom_requests"] ?? 0) . " requests, " . ($stats["quotes"] ?? 0) . " quotes.";
}

function buildNotificationReply($stats)
{
    return "Notification system:\n- Website bell badge unread notifications show karta hai.\n- Email alerts Gmail SMTP se send hoti hain.\n- Triggers: signup OTP, account verified, booking confirmed, vendor quote, quote accepted, chat message, refund request, refund approved, booking cancelled.\n- Admin notifications page real database activity show karta hai.\nTotal notification records: " . ($stats["notifications"] ?? 0) . ".";
}

function buildAdminRefundReply($stats)
{
    return "Admin refund workflow:\n1. User My Bookings se refund reason submit karta hai.\n2. Admin Payments page mein Refund Requests section open karta hai.\n3. Approve Refund se refund approved, payment refunded, booking cancelled.\n4. User ko website notification aur email milti hai.\nPending refunds right now: " . ($stats["refunds_pending"] ?? 0) . ".";
}

function buildPlannerReply($eventType, $city, $budget, $guestCount, $events)
{
    $type = $eventType ?: "custom event";
    $cityText = $city ? " in " . ucfirst($city) : "";
    $guests = $guestCount > 0 ? $guestCount : 100;
    $baseBudget = $budget > 0 ? $budget : 150000;
    $venue = round($baseBudget * 0.30);
    $food = round($baseBudget * 0.38);
    $decor = round($baseBudget * 0.20);
    $media = $baseBudget - $venue - $food - $decor;

    $reply = "AI Event Plan for {$type}{$cityText}\n";
    $reply .= "Recommended setup: {$guests} guests, budget around Rs. " . number_format($baseBudget) . ".\n\n";
    $reply .= "Package idea:\n";
    $reply .= "- Venue and seating: Rs. " . number_format($venue) . "\n";
    $reply .= "- Food/catering: Rs. " . number_format($food) . "\n";
    $reply .= "- Decor/theme: Rs. " . number_format($decor) . "\n";
    $reply .= "- Photography, sound and extras: Rs. " . number_format($media) . "\n\n";
    $reply .= "Theme suggestion: " . themeForEvent($type) . "\n";
    $reply .= "Vendor checklist: venue, catering, decor, photography, sound system, host/staff, backup power.\n\n";

    if (!empty($events) && ($events[0]['_ai_score'] ?? 0) > 0) {
        $reply .= "Closest available listed event: " . ($events[0]['title'] ?? 'Event') .
            " at Rs. " . number_format((float) ($events[0]['price'] ?? 0)) .
            " with " . ($events[0]['available_tickets'] ?? 0) . " seats.\n";
    }

    $reply .= "Advanced option: Custom Event Request page par apni requirements post karein, phir vendors/admin aapko quote bhej sakte hain.";

    return $reply;
}

function themeForEvent($type)
{
    if (strpos($type, "wedding") !== false || strpos($type, "shadi") !== false) {
        return "Royal floral stage, warm lighting, premium seating, traditional entrance and family photo zone.";
    }

    if (strpos($type, "birthday") !== false) {
        return "Personalized backdrop, cake table, balloon decor, music corner and photo booth.";
    }

    if (strpos($type, "corporate") !== false || strpos($type, "seminar") !== false) {
        return "Clean stage branding, projector setup, registration desk, speaker area and formal refreshments.";
    }

    return "Modern elegant theme with branded entrance, lighting, seating plan and media coverage.";
}

function matchEvents($text, $events, $budget, $guestCount)
{
    $keywords = array_filter(preg_split('/\s+/', preg_replace('/[^a-z0-9\s]/', ' ', $text)));

    $scored = [];

    foreach ($events as $event) {
        $score = 0;
        $haystack = strtolower(
            ($event['title'] ?? '') . ' ' .
            ($event['category_name'] ?? '') . ' ' .
            ($event['location'] ?? '') . ' ' .
            ($event['description'] ?? '')
        );

        foreach ($keywords as $keyword) {
            if (strlen($keyword) > 2 && strpos($haystack, $keyword) !== false) {
                $score += 3;
            }
        }

        $price = (float) ($event['price'] ?? 0);
        $available = (int) ($event['available_tickets'] ?? 0);

        if ($budget > 0 && $price <= $budget) {
            $score += 4;
        }

        if ($guestCount > 0 && $available >= $guestCount) {
            $score += 4;
        }

        if ($available > 0) {
            $score += 1;
        }

        $event['_ai_score'] = $score;
        $scored[] = $event;
    }

    usort($scored, function ($a, $b) {
        return $b['_ai_score'] <=> $a['_ai_score'];
    });

    return array_slice($scored, 0, 3);
}

function buildRecommendationReply($events, $budget, $guestCount)
{
    if (empty($events)) {
        return "Abhi active events available nahi hain. Admin panel se events add karein, phir main budget aur guests ke mutabiq recommendation de dunga.";
    }

    $intro = "Meri recommendation yeh hai:";

    if ($budget > 0 || $guestCount > 0) {
        $intro = "Aapke " .
            ($budget > 0 ? "Rs. " . number_format($budget) . " budget " : "") .
            ($guestCount > 0 ? "aur " . $guestCount . " guests " : "") .
            "ke hisaab se best options:";
    }

    $lines = [$intro];

    foreach ($events as $index => $event) {
        $lines[] = ($index + 1) . ". " . ($event['title'] ?? 'Event') .
            " - Rs. " . number_format((float) ($event['price'] ?? 0)) .
            ", " . ($event['location'] ?? 'location not set') .
            ", " . ($event['available_tickets'] ?? 0) . " seats available.";
    }

    $lines[] = "Best demo tip: user ko Events page par le ja kar Book Now flow complete karwao, phir admin dashboard mein booking live show karo.";

    return implode("\n", $lines);
}

function buildBudgetReply($budget, $guestCount, $events)
{
    $baseBudget = $budget > 0 ? $budget : 50000;
    $guests = $guestCount > 0 ? $guestCount : 100;
    $perGuest = max(500, round($baseBudget / max($guests, 1)));
    $decor = round($baseBudget * 0.25);
    $food = round($baseBudget * 0.45);
    $venue = round($baseBudget * 0.20);
    $media = $baseBudget - $decor - $food - $venue;

    $reply = "Smart budget estimate for {$guests} guests:\n";
    $reply .= "- Per guest estimate: Rs. " . number_format($perGuest) . "\n";
    $reply .= "- Food: Rs. " . number_format($food) . "\n";
    $reply .= "- Decoration: Rs. " . number_format($decor) . "\n";
    $reply .= "- Venue/service: Rs. " . number_format($venue) . "\n";
    $reply .= "- Media/extra: Rs. " . number_format($media) . "\n";

    if (!empty($events)) {
        $reply .= "\nClosest available event: " . ($events[0]['title'] ?? 'Event') .
            " at Rs. " . number_format((float) ($events[0]['price'] ?? 0)) . ".";
    }

    return $reply;
}

function buildAssistantSuggestions($events)
{
    $suggestions = [
        "Explain vendor quote workflow",
        "How does refund approval work?",
        "Suggest wedding package in Karachi",
        "Explain OTP email verification"
    ];

    if (!empty($events)) {
        array_unshift($suggestions, "Recommend from available events");
    }

    return array_slice($suggestions, 0, 4);
}

function extractBudget($text)
{
    if (preg_match('/(?:rs\.?|pkr|under|budget)?\s*(\d{4,7})/', $text, $matches)) {
        return (int) $matches[1];
    }

    return 0;
}

function extractGuestCount($text)
{
    if (preg_match('/(\d{1,5})\s*(guest|guests|people|persons|seats|tickets)/', $text, $matches)) {
        return (int) $matches[1];
    }

    return 0;
}

function extractEventType($text)
{
    $types = [
        "wedding", "shadi", "shaadi", "mehndi", "barat", "walima",
        "birthday", "corporate", "conference", "seminar", "concert", "festival"
    ];

    foreach ($types as $type) {
        if (strpos($text, $type) !== false) {
            return $type;
        }
    }

    return "";
}

function extractCity($text)
{
    $cities = ["karachi", "lahore", "islamabad", "rawalpindi", "hyderabad", "multan"];

    foreach ($cities as $city) {
        if (strpos($text, $city) !== false) {
            return $city;
        }
    }

    return "";
}

function containsAny($text, $words)
{
    foreach ($words as $word) {
        if (strpos($text, $word) !== false) {
            return true;
        }
    }

    return false;
}

function containsWord($text, $words)
{
    foreach ($words as $word) {
        if (preg_match('/\b' . preg_quote($word, '/') . '\b/', $text)) {
            return true;
        }
    }

    return false;
}
