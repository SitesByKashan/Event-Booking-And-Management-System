// ===============================
// Component Loader
// ===============================
async function load(id, file) {
  const element = document.getElementById(id);

  if (!element) return;

  try {
    const response = await fetch(file);

    if (!response.ok) {
      throw new Error(`${file} not found`);
    }

    const data = await response.text();
    element.innerHTML = data;

    if (id === "navbar") {
      updateUserNavbar();
      setActiveNavbarLink();
    }

    if (id === "hero-section") {
  setupHeroSearch();
}

  } catch (error) {
    console.error("Component Load Error:", error);
  }
}

// ===============================
// Navbar User Dropdown
// ===============================
function updateUserNavbar() {
  const authSection = document.getElementById("auth-section");
  const user = JSON.parse(localStorage.getItem("user"));

  if (!authSection) return;

  const vendorDeskLink = document.getElementById("vendorDeskLink");

  if (vendorDeskLink && user && (user.role === "vendor" || user.role === "admin")) {
    vendorDeskLink.classList.remove("hidden");
    vendorDeskLink.classList.add("flex");
  }

  if (!user) {
    authSection.innerHTML = `
      <a href="/user/login.html"
        class="bg-[#5C7CFA] hover:bg-[#4C6EF5] text-white font-semibold text-xs px-5 py-2 rounded-lg inline-block">
        Login
      </a>
    `;
    return;
  }

  updateNotificationBadge();

  const initials = (user.name || "User")
    .split(" ")
    .map(word => word[0])
    .join("")
    .substring(0, 2)
    .toUpperCase();

  authSection.innerHTML = `
    <div class="relative">
      <button id="userDropdownBtn" class="flex items-center gap-3 cursor-pointer">
        <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-sm">
          ${initials}
        </div>

        <span class="font-semibold text-sm text-slate-700">
          ${user.name || "User"}
        </span>

        <i class="fa-solid fa-chevron-down text-xs text-slate-500"></i>
      </button>

      <div id="userDropdown"
        class="hidden absolute right-0 mt-3 w-48 bg-white rounded-xl shadow-lg border border-slate-200 z-50 overflow-hidden">

        <div class="px-4 py-3 border-b border-slate-100">
          <p class="text-[11px] text-slate-400">Signed in as</p>
          <p class="text-sm font-semibold text-slate-700 truncate">
            ${user.email || ""}
          </p>
        </div>

        <a href="/user/profile-settings.html"
          class="block px-4 py-3 text-sm text-slate-600 hover:bg-slate-50">
          Profile
        </a>

        <button id="logoutBtn"
          class="w-full text-left px-4 py-3 text-sm text-red-500 hover:bg-red-50">
          Logout
        </button>
      </div>
    </div>
  `;

  document.getElementById("userDropdownBtn").addEventListener("click", function (e) {
    e.stopPropagation();
    document.getElementById("userDropdown").classList.toggle("hidden");
  });

  document.getElementById("logoutBtn").addEventListener("click", logoutUser);
}

// ===============================
// Active Navbar Link
// ===============================
function setActiveNavbarLink() {
  const currentPage = window.location.pathname
    .split("/")
    .pop()
    .toLowerCase();

  const allowedPages = [
    "index.html",
    "events.html",
    "custom-event.html",
    "vendor-requests.html",
    "mybookings.html"
  ];

  document.querySelectorAll(".nav-link").forEach(link => {
    link.classList.remove(
      "text-[#000000]",
      "font-bold",
      "border-[#4B4EFC]"
    );

    link.classList.add(
      "text-[#64748B]",
      "border-transparent"
    );

    const href = link.getAttribute("href");

    if (!href) return;

    const hrefPage = href
      .split("/")
      .pop()
      .toLowerCase();

    if (
      allowedPages.includes(currentPage) &&
      hrefPage === currentPage
    ) {
      link.classList.remove(
        "text-[#64748B]",
        "border-transparent"
      );

      link.classList.add(
        "text-[#000000]",
        "font-bold",
        "border-[#4B4EFC]"
      );
    }
  });
}

// ===============================
// Logout
// ===============================
function logoutUser() {
  localStorage.removeItem("user");
  window.location.href = "/user/login.html";
}

// Close dropdown outside click
document.addEventListener("click", function () {
  const dropdown = document.getElementById("userDropdown");

  if (dropdown) {
    dropdown.classList.add("hidden");
  }
});

// Make functions global
window.updateUserNavbar = updateUserNavbar;
window.setActiveNavbarLink = setActiveNavbarLink;
window.logoutUser = logoutUser;
window.load = load;

async function updateNotificationBadge() {
  const badge = document.getElementById("notificationBadge");

  if (!badge || typeof getNotifications !== "function") return;

  try {
    const response = await getNotifications();
    const count = Number(response.unread_count || 0);

    if (count > 0) {
      badge.innerText = count > 9 ? "9+" : count;
      badge.classList.remove("hidden");
      badge.classList.add("flex");
    } else {
      badge.classList.add("hidden");
      badge.classList.remove("flex");
    }
  } catch (error) {
    console.log("Notification Badge Error:", error);
  }
}

window.updateNotificationBadge = updateNotificationBadge;

// ===============================
// Old Static Events Grid Support
// ===============================
function buildEventsPageGrid(filteredDataArray) {
  const gridHookNode = document.getElementById("events-card-grid");
  const labelCounter = document.getElementById("eventsCountLabel");

  if (!gridHookNode) return;

  if (labelCounter) {
    labelCounter.innerText =
      `${filteredDataArray.length} event${filteredDataArray.length === 1 ? "" : "s"} found`;
  }

  let outputStreamHTML = "";

  filteredDataArray.forEach(item => {
    const trendingBadge = item.isTrending
      ? `<div class="absolute top-3 left-3 bg-[#EF4444] text-white text-[9px] font-extrabold px-2 py-0.5 rounded uppercase">Trending</div>`
      : "";

    const featuredBadge = item.isFeatured
      ? `<div class="absolute bottom-3 left-3 bg-[#5C7CFA] text-white text-[9px] font-bold px-2 py-0.5 rounded uppercase">Featured</div>`
      : "";

    const priceLabelString = item.price === 0 ? "Free Entry" : `$${item.price}`;

    outputStreamHTML += `
      <div class="bg-white rounded-xl overflow-hidden border border-gray-100 shadow-sm flex flex-col justify-between text-left group hover:shadow-lg transition-all duration-300 relative w-full">

        <div class="relative w-full h-44 bg-slate-50 overflow-hidden">
          <img src="${item.imageUrl}" alt="${item.title}" class="w-full h-full object-cover">
          ${trendingBadge}
          ${featuredBadge}

          <button class="absolute top-3 right-3 w-7 h-7 bg-white/90 text-gray-700 rounded-full flex items-center justify-center">
            <i class="fa-regular fa-heart text-[11px]"></i>
          </button>
        </div>

        <div class="p-4 space-y-3">
          <div>
            <div class="flex items-center gap-1.5">
              <span class="text-[10px] font-bold text-[#5C7CFA] uppercase">${item.category}</span>
              <span class="text-gray-300 text-[10px]">•</span>
              <span class="text-amber-500 text-[11px] font-bold">
                <i class="fa-solid fa-star text-[9px]"></i> ${item.rating}
              </span>
            </div>

            <h3 class="text-sm font-bold text-[#0F172A] mt-1">${item.title}</h3>

            <div class="space-y-1.5 text-[11px] font-medium text-gray-400 pt-2">
              <div><i class="fa-regular fa-calendar w-4"></i> ${item.timeDisplay}</div>
              <div><i class="fa-solid fa-location-dot w-4"></i> ${item.location}</div>
              <div><i class="fa-solid fa-user-group w-4"></i> ${item.seats} seats available</div>
            </div>
          </div>

          <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
            <div>
              <span class="text-[9px] font-bold uppercase text-gray-400">From</span>
              <p class="text-sm font-extrabold text-[#0F172A]">${priceLabelString}</p>
            </div>

            <button class="bg-[#5C7CFA] hover:bg-[#4C6EF5] text-white text-xs font-bold px-4 py-2 rounded-lg">
              Book Now
            </button>
          </div>
        </div>
      </div>
    `;
  });

  gridHookNode.innerHTML =
    outputStreamHTML ||
    `<div class="col-span-full py-20 text-center text-xs font-bold text-gray-400 uppercase">
      No events found.
    </div>`;
}

// ===============================
// Old Static Filters Support
// ===============================
function filterEventsPanel() {
  const searchStringQuery =
    (document.getElementById("innerEventsSearch")?.value || "").toLowerCase();

  const selectedCategoryVal =
    document.getElementById("sidebarCategory")?.value || "All";

  const maxAllowedPriceLimit =
    parseInt(document.getElementById("sidebarPriceSlider")?.value || "500", 10);

  const selectedEventTypeRadio =
    document.querySelector('input[name="eventType"]:checked')?.value || "All";

  let result = eventsDataset.filter(event => {
    if (searchStringQuery && !event.title.toLowerCase().includes(searchStringQuery)) return false;
    if (selectedCategoryVal !== "All" && event.category !== selectedCategoryVal) return false;
    if (event.price > maxAllowedPriceLimit) return false;
    if (selectedEventTypeRadio === "Free" && event.price !== 0) return false;
    if (selectedEventTypeRadio === "Paid" && event.price === 0) return false;

    return true;
  });

  buildEventsPageGrid(result);
}

function resetAllFilters() {
  if (document.getElementById("innerEventsSearch")) {
    document.getElementById("innerEventsSearch").value = "";
  }

  if (document.getElementById("sidebarCategory")) {
    document.getElementById("sidebarCategory").value = "All";
  }

  if (document.getElementById("sidebarPriceSlider")) {
    document.getElementById("sidebarPriceSlider").value = 500;
  }

  buildEventsPageGrid(eventsDataset);
}

// ===============================
// Page Init
// ===============================
document.addEventListener("DOMContentLoaded", () => {
  if (document.getElementById("events-card-grid") && typeof eventsDataset !== "undefined") {
    buildEventsPageGrid(eventsDataset);
  }
});

function setupHeroSearch() {
  const searchInput = document.getElementById("globalSearch");
  const searchBtn = document.getElementById("heroSearchBtn");

  if (!searchInput || !searchBtn) return;

  searchBtn.addEventListener("click", function () {
    applyHomeSearch(searchInput.value);
  });

  searchInput.addEventListener("keypress", function (e) {
    if (e.key === "Enter") {
      applyHomeSearch(searchInput.value);
    }
  });
}

function searchCategory(category) {
  applyHomeSearch(category);
}

function applyHomeSearch(keyword) {
  keyword = keyword.trim().toLowerCase();

  if (!keyword) return;

  const filtered = homeEvents.filter(event => {
    return (
      (event.title || "").toLowerCase().includes(keyword) ||
      (event.category_name || "").toLowerCase().includes(keyword) ||
      (event.location || "").toLowerCase().includes(keyword)
    );
  });

  renderHomeEvents(filtered);
}

function searchCategory(category) {
  window.location.href =
    "events.html?search=" + encodeURIComponent(category);
}

window.setupHeroSearch = setupHeroSearch;
window.searchCategory = searchCategory;

function showToast(message, type = "success") {

  let bgColor = "#10B981";

  if (type === "error") {
    bgColor = "#EF4444";
  }

  if (type === "warning") {
    bgColor = "#F59E0B";
  }

  Toastify({
    text: message,
    duration: 3000,
    gravity: "top",
    position: "right",
    stopOnFocus: true,
    close: true,
    style: {
      background: bgColor,
      borderRadius: "12px",
      fontSize: "14px",
      fontWeight: "600"
    }
  }).showToast();
}

window.showToast = showToast;
