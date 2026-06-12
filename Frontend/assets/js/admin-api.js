const API_BASE_URL = "http://localhost:8000/index.php?action=";

async function getDashboardStats() {
  const response = await fetch(API_BASE_URL + "dashboard_stats", {
    credentials: "include"
  });
  return await response.json();
}

async function getUsers() {
  const response = await fetch(API_BASE_URL + "get_users", {
    credentials: "include"
  });
  return await response.json();
}

async function getBookings() {
  const response = await fetch(API_BASE_URL + "get_bookings", {
    credentials: "include"
  });
  return await response.json();
}

async function getChartStats() {
  const response = await fetch(API_BASE_URL + "chart_stats", {
    credentials: "include"
  });

  return await response.json();
}

async function getAiAdminSummary() {
  const endpoints = [
    API_BASE_URL + "ai_admin_summary",
    "http://127.0.0.1:8000/index.php?action=ai_admin_summary"
  ];

  let lastError = null;

  for (const endpoint of endpoints) {
    try {
      const response = await fetch(endpoint, {
        credentials: "include"
      });

      return await response.json();
    } catch (error) {
      lastError = error;
    }
  }

  throw lastError;
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

async function getAdminNotifications() {
  const response = await fetch(API_BASE_URL + "get_admin_notifications", {
    credentials: "include"
  });

  return await response.json();
}

async function getRefundRequests() {
  const response = await fetch(API_BASE_URL + "get_refund_requests", {
    credentials: "include"
  });

  return await response.json();
}

async function approveRefundRequest(refundId) {
  const response = await fetch(API_BASE_URL + "approve_refund_request", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    credentials: "include",
    body: JSON.stringify({ refund_id: refundId })
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

async function uploadEventImage(file) {
  const formData = new FormData();
  formData.append("image", file);

  const res = await fetch(API_BASE_URL + "upload_event_image", {
    method: "POST",
    credentials: "include",
    body: formData
  });

  return await res.json();
}

async function getEvents() {
  const res = await fetch(API_BASE_URL + "get_events", {
    credentials: "include"
  });

  return await res.json();
}

async function addEvent(data) {
  const res = await fetch(API_BASE_URL + "add_event", {
    method: "POST",
    headers: {"Content-Type": "application/json"},
    body: JSON.stringify(data),
    credentials: "include"
  });
  return await res.json();
}

async function updateEvent(data) {
  const res = await fetch(API_BASE_URL + "update_event", {
    method: "POST",
    headers: {"Content-Type": "application/json"},
    body: JSON.stringify(data),
    credentials: "include"
  });
  return await res.json();
}

async function deleteEvent(id) {
  const res = await fetch(API_BASE_URL + "delete_event&id=" + id, {
    credentials: "include"
  });
  return await res.json();
}

async function getBookings() {
  const res = await fetch(API_BASE_URL + "get_bookings", {
    credentials: "include"
  });
  return await res.json();
}

async function addCategory(data) {
  const res = await fetch(API_BASE_URL + "add_category", {
    method: "POST",
    headers: {"Content-Type": "application/json"},
    body: JSON.stringify(data),
    credentials: "include"
  });
  return await res.json();
}

async function getCategories() {
  const res = await fetch(API_BASE_URL + "get_categories");
  return await res.json();
}

async function getUsers() {
  const response = await fetch(API_BASE_URL + "get_users", {
    credentials: "include"
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

async function updatePaymentStatus(id, payment_status) {

  const response = await fetch(
    API_URL + "?action=update_payment_status",
    {
      method: "POST",
      headers: getHeaders(),
      body: JSON.stringify({
        id,
        payment_status
      })
    }
  );

  return await response.json();
}
