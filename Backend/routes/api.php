<?php

require_once __DIR__ . "/../controllers/authController.php";
require_once __DIR__ . "/../controllers/eventController.php";
require_once __DIR__ . "/../controllers/bookingController.php";
require_once __DIR__ . "/../controllers/categoryController.php";
require_once __DIR__ . "/../controllers/dashboardController.php";
require_once __DIR__ . "/../controllers/userController.php";
require_once __DIR__ . "/../controllers/paymentController.php";
require_once __DIR__ . "/../controllers/aiController.php";
require_once __DIR__ . "/../controllers/eventRequestController.php";
require_once __DIR__ . "/../controllers/notificationController.php";

require_once __DIR__ . "/../middleware/authMiddleware.php";
require_once __DIR__ . "/../middleware/adminMiddleware.php";

$action = $_GET['action'] ?? '';

switch ($action) {

    case 'register':
        register();
        break;

    case 'login':
        login();
        break;

    case 'verify_email_otp':
        verifyEmailOtp();
        break;

    case 'resend_email_otp':
        resendEmailOtp();
        break;

    case 'add_event':
        adminMiddleware();
        addEvent();
        break;

    case 'user_dashboard_stats':
        authMiddleware();
        userDashboardStats();
        break;

    case 'get_events':
        getEvents();
        break;

    case 'update_event':
        adminMiddleware();
        updateEvent();
        break;

    case 'delete_event':
        adminMiddleware();
        deleteEvent();
        break;

    case 'upload_event_image':
        adminMiddleware();
        uploadEventImage();
        break;

    case 'create_booking':
        authMiddleware();
        createBooking();
        break;

    case 'get_bookings':
        adminMiddleware();
        getBookings();
        break;

    case 'get_user_bookings':
        authMiddleware();
        getUserBookings();
        break;

    case 'cancel_booking':
        authMiddleware();
        cancelBooking();
        break;

    case 'request_refund':
        authMiddleware();
        requestRefund();
        break;

    case 'get_users':
        adminMiddleware();
        getUsers();
        break;

    case 'add_category':
        adminMiddleware();
        addCategory();
        break;

    case 'get_categories':
        getCategories();
        break;

    case 'update_category':
        adminMiddleware();
        updateCategory();
        break;

    case 'delete_category':
        adminMiddleware();
        deleteCategory();
        break;

    case 'dashboard_stats':
        adminMiddleware();
        dashboardStats();
        break;

    case 'chart_stats':
        adminMiddleware();
        chartStats();
        break;

    case 'get_profile':
        getProfile();
        break;

    case 'update_profile':
        updateProfile();
        break;

    case 'update_password':
        updatePassword();
        break;

    case 'become_vendor':
        authMiddleware();
        becomeVendor();
        break;

    case 'get_notifications':
        authMiddleware();
        getNotifications();
        break;

    case 'mark_notifications_read':
        authMiddleware();
        markNotificationsRead();
        break;

    case 'get_admin_notifications':
        adminMiddleware();
        getAdminNotifications();
        break;

    case 'create_payment':
        authMiddleware();
        createPayment();
        break;

    case 'get_payments':
        adminMiddleware();
        getPayments();
        break;

    case 'update_payment_status':
        adminMiddleware();
        updatePaymentStatus();
        break;

    case 'get_refund_requests':
        adminMiddleware();
        getRefundRequests();
        break;

    case 'approve_refund_request':
        adminMiddleware();
        approveRefundRequest();
        break;

    case 'ai_assistant':
        aiAssistant();
        break;

    case 'ai_admin_summary':
        adminMiddleware();
        aiAdminSummary();
        break;

    case 'create_event_request':
        authMiddleware();
        createEventRequest();
        break;

    case 'get_user_event_requests':
        authMiddleware();
        getUserEventRequests();
        break;

    case 'get_event_requests':
        authMiddleware();
        getEventRequests();
        break;

    case 'create_event_quote':
        authMiddleware();
        createEventQuote();
        break;

    case 'get_customer_quotes':
        authMiddleware();
        getCustomerQuotes();
        break;

    case 'get_vendor_quotes':
        authMiddleware();
        getVendorQuotes();
        break;

    case 'accept_event_quote':
        authMiddleware();
        acceptEventQuote();
        break;

    case 'send_quote_message':
        authMiddleware();
        sendQuoteMessage();
        break;

    case 'get_quote_messages':
        authMiddleware();
        getQuoteMessages();
        break;

    default:
        echo json_encode([
            "status" => false,
            "message" => "Invalid API action"
        ]);
}
