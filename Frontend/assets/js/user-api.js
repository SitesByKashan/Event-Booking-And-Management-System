const API_BASE_URL = "http://localhost:8000/index.php?action=";

async function registerUser(name, email, password) {
  const response = await fetch(API_BASE_URL + "register", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    credentials: "include",
    body: JSON.stringify({
      name: name,
      email: email,
      password: password
    })
  });

  return await response.json();
}

async function loginUser(email, password) {
  const response = await fetch(API_BASE_URL + "login", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    credentials: "include",
    body: JSON.stringify({
      email: email,
      password: password
    })
  });

  return await response.json();
}

async function getEvents() {
  const response = await fetch(API_BASE_URL + "get_events", {
    credentials: "include"
  });

  return await response.json();
}

async function getCategories() {
  const response = await fetch(API_BASE_URL + "get_categories", {
    credentials: "include"
  });

  return await response.json();
}

async function createBooking(data) {
  console.log("Booking data sending:", data);

  const response = await fetch(API_BASE_URL + "create_booking", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    credentials: "include",
    body: JSON.stringify(data)
  });

  return await response.json();
}

async function getProfile() {
  const response = await fetch(API_BASE_URL + "get_profile", {
    credentials: "include"
  });

  return await response.json();
}

async function updateProfile(data) {
  const response = await fetch(API_BASE_URL + "update_profile", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    credentials: "include",
    body: JSON.stringify(data)
  });

  return await response.json();
}

async function updatePassword(data) {
  const response = await fetch(API_BASE_URL + "update_password", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    credentials: "include",
    body: JSON.stringify(data)
  });

  return await response.json();
}

async function getUserDashboardStats(userId) {

  const response = await fetch(
    API_BASE_URL + "user_dashboard_stats&user_id=" + userId,
    {
      credentials: "include"
    }
  );

  return await response.json();
}

async function getUserBookings(userId) {

  const response = await fetch(
    API_BASE_URL + "get_user_bookings&user_id=" + userId,
    {
      credentials: "include"
    }
  );

  return await response.json();
}

async function createPayment(data) {

    const response =
    await fetch(
        API_BASE_URL + "create_payment",
        {
            method: "POST",
            headers: {
                "Content-Type":
                "application/json"
            },
            credentials: "include",
            body: JSON.stringify(data)
        }
    );

    return await response.json();
}
async function becomeVendor() {
  const response = await fetch(API_BASE_URL + "become_vendor", {
    method: "POST",
    credentials: "include"
  });

  return await response.json();
}

async function verifyEmailOtp(email, otp) {
  const response = await fetch(API_BASE_URL + "verify_email_otp", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    credentials: "include",
    body: JSON.stringify({ email, otp })
  });

  return await response.json();
}

async function resendEmailOtp(email) {
  const response = await fetch(API_BASE_URL + "resend_email_otp", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    credentials: "include",
    body: JSON.stringify({ email })
  });

  return await response.json();
}

async function cancelBooking(bookingId) {
  const response = await fetch(API_BASE_URL + "cancel_booking", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    credentials: "include",
    body: JSON.stringify({ booking_id: bookingId })
  });

  return await response.json();
}

async function requestRefund(bookingId, reason) {
  const response = await fetch(API_BASE_URL + "request_refund", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    credentials: "include",
    body: JSON.stringify({
      booking_id: bookingId,
      reason
    })
  });

  return await response.json();
}

async function createEventRequest(data) {
  const response = await fetch(API_BASE_URL + "create_event_request", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    credentials: "include",
    body: JSON.stringify(data)
  });

  return await response.json();
}

async function getUserEventRequests() {
  const response = await fetch(API_BASE_URL + "get_user_event_requests", {
    credentials: "include"
  });

  return await response.json();
}

async function getCustomerQuotes() {
  const response = await fetch(API_BASE_URL + "get_customer_quotes", {
    credentials: "include"
  });

  return await response.json();
}

async function getVendorQuotes() {
  const response = await fetch(API_BASE_URL + "get_vendor_quotes", {
    credentials: "include"
  });

  return await response.json();
}

async function getEventRequests() {
  const response = await fetch(API_BASE_URL + "get_event_requests", {
    credentials: "include"
  });

  return await response.json();
}

async function createEventQuote(data) {
  const response = await fetch(API_BASE_URL + "create_event_quote", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    credentials: "include",
    body: JSON.stringify(data)
  });

  return await response.json();
}

async function acceptEventQuote(quoteId) {
  const response = await fetch(API_BASE_URL + "accept_event_quote", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    credentials: "include",
    body: JSON.stringify({ quote_id: quoteId })
  });

  return await response.json();
}

async function getQuoteMessages(quoteId) {
  const response = await fetch(API_BASE_URL + "get_quote_messages&quote_id=" + quoteId, {
    credentials: "include"
  });

  return await response.json();
}

async function sendQuoteMessage(quoteId, message) {
  const response = await fetch(API_BASE_URL + "send_quote_message", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    credentials: "include",
    body: JSON.stringify({
      quote_id: quoteId,
      message
    })
  });

  return await response.json();
}

async function getNotifications() {
  const response = await fetch(API_BASE_URL + "get_notifications", {
    credentials: "include"
  });

  return await response.json();
}

async function markNotificationsRead() {
  const response = await fetch(API_BASE_URL + "mark_notifications_read", {
    method: "POST",
    credentials: "include"
  });

  return await response.json();
}

async function askAiAssistant(message) {
  const endpoints = [
    API_BASE_URL + "ai_assistant",
    "http://127.0.0.1:8000/index.php?action=ai_assistant"
  ];

  let lastError = null;

  for (const endpoint of endpoints) {
    try {
      const response = await fetch(endpoint, {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        credentials: "include",
        body: JSON.stringify({ message })
      });

      return await response.json();
    } catch (error) {
      lastError = error;
    }
  }

  throw lastError;
}
