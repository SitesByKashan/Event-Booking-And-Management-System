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