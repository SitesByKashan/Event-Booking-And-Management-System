// 1. Theme Auto Loader
const savedTheme = localStorage.getItem("theme");

if (
  savedTheme === "dark" ||
  (!savedTheme && window.matchMedia("(prefers-color-scheme: dark)").matches)
) {
  document.documentElement.classList.add("dark");
}

// 2. Global Component Loader
function load(id, file, pageName = "") {
  return fetch(file)
    .then((res) => res.text())
    .then((data) => {
      const container = document.getElementById(id);

      if (!container) return;

      container.innerHTML = data;

      // CORRECTED CHECK: Agar container ki ID 'sidebar' ho YA uske andar 'sidebar-container' element majood ho
      if ((id === "sidebar" || id === "sidebar-container") && pageName) {
        setActiveSidebar(pageName);
      }

      //   if (id === "charts" && typeof initDashboardCharts === "function") {
      //     initDashboardCharts();
      //   }

      if (id === "revenue-chart" && typeof initRevenueChart === "function") {
        initRevenueChart();
      }
      if (id === "navbar" && typeof loadNavbarUser === "function") {
        loadNavbarUser();
      }

      // if (
      //   id === "bar-chart-container" &&
      //   typeof initReportsCharts === "function"
      // ) {
      //   initReportsCharts();
      // }

      if (id === "calendar-view" && typeof renderCalendar === "function") {
        renderCalendar();
      }
    })
    .catch((error) => {
      console.error("Component load error:", error);
    });
}

// 3. Sidebar Active Link (Figma-Perfect Fix)
function setActiveSidebar(pageName) {
  // Pehle sab links ko default state par reset karein
  document.querySelectorAll(".nav-item, #sidebar-container nav a").forEach((link) => {
    link.classList.remove("bg-white", "text-[#6366F1]", "font-semibold", "shadow-sm");
    link.classList.add("text-indigo-100", "hover:bg-white/10", "hover:text-white");

    link.style.color = "";
    const icon = link.querySelector("svg");
    if (icon) icon.style.color = "";
    const text = link.querySelector(".nav-text");
    if (text) text.style.color = "";
  });

  // Current active page link ko target karein
  const activeLink = document.getElementById(`nav-${pageName}`);
  if (!activeLink) return;

  // Active link style override (White background + Hex Indigo Text)
  activeLink.classList.remove("text-indigo-100", "hover:bg-white/10", "hover:text-white");
  activeLink.classList.add("bg-white", "font-semibold", "shadow-sm");
  activeLink.style.color = "#6366F1";

  // SVG Icon aur Text dono ka color overwrite karein taake white-on-white na ho
  const icon = activeLink.querySelector("svg");
  const text = activeLink.querySelector(".nav-text");

  if (icon) icon.style.color = "#6366F1";
  if (text) {
    text.classList.remove("hidden");
    text.style.color = "#6366F1";
    text.style.display = "inline";
  }
}

// 4. Sidebar Close Logic
function closeSidebar() {
  const sidebar = document.getElementById("sidebar-container");
  if (!sidebar) return;

  sidebar.classList.remove("w-64", "p-4");
  sidebar.classList.add("w-20", "p-2", "items-center");

  document.getElementById("sidebar-full-header")?.classList.add("hidden");
  document.getElementById("sidebar-collapsed-header")?.classList.remove("hidden");

  document.querySelectorAll(".nav-text").forEach((text) => {
    text.classList.add("hidden");
    text.style.display = "none";
  });

  document.getElementById("sidebar-help-box")?.classList.add("hidden");
}

// 5. Sidebar Open Logic
function openSidebar() {
  const sidebar = document.getElementById("sidebar-container");
  if (!sidebar) return;

  sidebar.classList.remove("w-20", "p-2", "items-center");
  sidebar.classList.add("w-64", "p-4");

  document.getElementById("sidebar-collapsed-header")?.classList.add("hidden");
  document.getElementById("sidebar-full-header")?.classList.remove("hidden");

  document.querySelectorAll(".nav-text").forEach((text) => {
    text.classList.remove("hidden");
    text.style.display = "inline";
  });

  document.getElementById("sidebar-help-box")?.classList.remove("hidden");
}

// 6. Click Listener
document.addEventListener("click", (e) => {
  if (e.target.closest("#sidebar-close-btn")) {
    closeSidebar();
  }

  if (e.target.closest("#sidebar-open-btn")) {
    openSidebar();
  }

  if (e.target.closest("#theme-toggle-btn")) {
    toggleTheme();
  }

  if (e.target.closest("#user-menu-btn")) {
    e.stopPropagation();
    document.getElementById("user-dropdown")?.classList.toggle("hidden");
    document.getElementById("notif-dropdown")?.classList.add("hidden");
  }

  if (e.target.closest("#notif-btn")) {
    e.stopPropagation();
    document.getElementById("notif-dropdown")?.classList.toggle("hidden");
    document.getElementById("user-dropdown")?.classList.add("hidden");
  }

  if (!e.target.closest("#user-menu-btn") && !e.target.closest("#notif-btn")) {
    document.getElementById("user-dropdown")?.classList.add("hidden");
    document.getElementById("notif-dropdown")?.classList.add("hidden");
  }
});

// 7. Input Search Listener
document.addEventListener("input", (e) => {
  const target = e.target;
  if (!target) return;

  if (target.placeholder?.includes("Search events")) {
    const value = target.value.toLowerCase();
    document.querySelectorAll(".event-card").forEach((card) => {
      card.classList.toggle("hidden", !card.innerText.toLowerCase().includes(value));
    });
  }

  if (target.placeholder?.includes("Search by booking ID")) {
    const value = target.value.toLowerCase();
    document.querySelectorAll("tbody tr").forEach((row) => {
      row.classList.toggle("hidden", !row.innerText.toLowerCase().includes(value));
    });
  }

  if (target.placeholder === "Search users...") {
    const query = target.value.toLowerCase();
    const rows = document.querySelectorAll("#users-table tbody tr");
    rows.forEach((row) => {
      row.style.display = row.innerText.toLowerCase().includes(query) ? "" : "none";
    });
  }
});

// 8. Revenue Bar Chart
function initRevenueChart() {
  const canvas = document.getElementById("revenueChart");
  if (!canvas || typeof Chart === "undefined") return;

  const ctx = canvas.getContext("2d");
  const gradient = ctx.createLinearGradient(0, 0, 0, 300);
  gradient.addColorStop(0, "#6366f1");
  gradient.addColorStop(1, "#3b82f6");

  new Chart(ctx, {
    type: "bar",
    data: {
      labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
      datasets: [
        {
          label: "Revenue",
          data: [12000, 15000, 14000, 18000, 21000, 25000],
          backgroundColor: gradient,
          borderRadius: 10,
          barThickness: 50,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: { beginAtZero: true, grid: { borderDash: [5, 5], color: "#e2e8f0" } },
        x: { grid: { display: false } },
      },
    },
  });
}

// 9. Render Calendar
function renderCalendar() {
  const container = document.getElementById("calendar-cells");
  if (!container) return;

  container.innerHTML = "";
  container.innerHTML += `<div class="h-28"></div>`;

  for (let i = 1; i <= 30; i++) {
    let eventHtml = "";
    let borderClass = "border-slate-200 dark:border-slate-700";

    if (i === 15) eventHtml = `<div class="mt-2 bg-indigo-500 text-white text-[10px] px-2 py-1 rounded-md">Summer Festival</div>`;
    if (i === 20) eventHtml = `<div class="mt-2 bg-indigo-600 text-white text-[10px] px-2 py-1 rounded-md">Tech Conference</div>`;
    if (i === 30) eventHtml = `<div class="mt-2 bg-indigo-500 text-white text-[10px] px-2 py-1 rounded-md">Workshop</div>`;
    if (i === 11) borderClass = "border-indigo-400 dark:border-indigo-500 ring-1 ring-indigo-200 dark:ring-0";

    container.innerHTML += `
      <div class="bg-white dark:bg-[#1f2040] border ${borderClass} rounded-2xl p-3 h-28 transition-all hover:bg-slate-100 dark:hover:bg-[#2a2b55] flex flex-col justify-between">
        <span class="text-slate-900 dark:text-white font-semibold text-sm">${i}</span>
        ${eventHtml}
      </div>
    `;
  }
}

// 10. Dashboard Charts
function initDashboardCharts() {
  if (typeof Chart === "undefined") return;

  const revCanvas = document.getElementById("revenueChart");
  if (revCanvas) {
    const revCtx = revCanvas.getContext("2d");
    new Chart(revCtx, {
      type: "line",
      data: {
        labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
        datasets: [
          {
            data: [4500, 5200, 4800, 6100, 7300, 8500],
            borderColor: "#6366f1",
            backgroundColor: "rgba(99, 102, 241, 0.2)",
            fill: true,
            tension: 0.4,
            borderWidth: 3,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
      },
    });
  }

  const eventCanvas = document.getElementById("eventChart");
  if (eventCanvas) {
    const eventCtx = eventCanvas.getContext("2d");
    new Chart(eventCtx, {
      type: "doughnut",
      data: {
        labels: ["Concerts", "Conferences", "Workshops", "Sports"],
        datasets: [
          {
            data: [40, 25, 20, 15],
            backgroundColor: ["#6366f1", "#3b82f6", "#8b5cf6", "#10b981"],
            borderWidth: 0,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: "75%",
        plugins: { legend: { display: false } },
      },
    });
  }
}

// 11. Reports Charts
function initReportsCharts() {
  if (typeof Chart === "undefined") return;

  const growthCanvas = document.getElementById("userGrowthChart");
  if (growthCanvas) {
    const growthCtx = growthCanvas.getContext("2d");
    new Chart(growthCtx, {
      type: "line",
      data: {
        labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
        datasets: [
          {
            label: "User Growth",
            data: [500, 700, 850, 1000, 1250, 1600],
            borderColor: "#6366f1",
            backgroundColor: "rgba(99, 102, 241, 0.1)",
            fill: true,
            tension: 0.4,
          },
        ],
      },
      options: { responsive: true, maintainAspectRatio: false },
    });
  }

  const eventsCanvas = document.getElementById("popularEventsChart");
  if (eventsCanvas) {
    const eventsCtx = eventsCanvas.getContext("2d");
    new Chart(eventsCtx, {
      type: "bar",
      data: {
        labels: ["Summer Festival", "Tech Conference", "Gourmet & Grains Expo", "Workshop"],
        datasets: [
          {
            label: "Popularity",
            data: [4200, 1800, 1200, 900],
            backgroundColor: ["#6366f1", "#3b82f6", "#60a5fa", "#818cf8"],
            borderRadius: 8,
          },
        ],
      },
      options: {
        indexAxis: "y",
        responsive: true,
        maintainAspectRatio: false,
      },
    });
  }
}

// 12. Toggle Switch
function toggleSwitch(id) {
  const toggle = document.getElementById(id);
  if (!toggle) return;

  const circle = toggle.querySelector("div");
  if (!circle) return;

  toggle.classList.toggle("bg-indigo-600");
  toggle.classList.toggle("bg-slate-300");
  toggle.classList.toggle("dark:bg-slate-700");

  circle.classList.toggle("right-1");
  circle.classList.toggle("left-1");
}

// 13. Theme Toggle
function toggleTheme() {
  const isDark = document.documentElement.classList.toggle("dark");
  localStorage.setItem("theme", isDark ? "dark" : "light");
}

async function loadNavbarUser() {
  try {
    const response = await getProfile();

    if (!response.status) return;

    const user = response.data;

    const initials = (user.name || "Admin User")
      .split(" ")
      .map(word => word[0])
      .join("")
      .substring(0, 2)
      .toUpperCase();

    const nameEl = document.getElementById("navbarUserName");
    const emailEl = document.getElementById("navbarUserEmail");
    const initialsEl = document.getElementById("navbarUserInitials");

    if (nameEl) nameEl.innerText = user.name;
    if (emailEl) emailEl.innerText = user.email;
    if (initialsEl) initialsEl.innerText = initials;

  } catch (error) {
    console.log("Navbar user error:", error);
  }
}

function logoutUser() {
  localStorage.clear();
  window.location.href = "../user/index.html";
}

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
        close: true,
        stopOnFocus: true,
        style: {
            background: bgColor,
            borderRadius: "12px"
        }
    }).showToast();
}

window.showToast = showToast;