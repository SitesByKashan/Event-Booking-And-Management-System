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

async function getUserBookings(userId) {
  const res = await fetch(API_BASE_URL + "get_user_bookings&user_id=" + userId, {
    credentials: "include"
  });
  return await res.json();
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