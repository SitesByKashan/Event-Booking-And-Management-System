const API_BASE_URL = "http://localhost:8000/index.php?action=";

async function registerUser(data) {
  const res = await fetch(API_BASE_URL + "register", {
    method: "POST",
    headers: {"Content-Type": "application/json"},
    body: JSON.stringify(data),
    credentials: "include"
  });
  return await res.json();
}

async function loginUser(data) {
  const res = await fetch(API_BASE_URL + "login", {
    method: "POST",
    headers: {"Content-Type": "application/json"},
    body: JSON.stringify(data),
    credentials: "include"
  });
  return await res.json();
}

async function getEvents() {
  const res = await fetch(API_BASE_URL + "get_events");
  return await res.json();
}

async function createBooking(data) {
  const res = await fetch(API_BASE_URL + "create_booking", {
    method: "POST",
    headers: {"Content-Type": "application/json"},
    body: JSON.stringify(data),
    credentials: "include"
  });
  return await res.json();
}

async function getUserBookings(userId) {
  const res = await fetch(API_BASE_URL + "get_user_bookings&user_id=" + userId, {
    credentials: "include"
  });
  return await res.json();
}     