// 1. Theme Auto-Loader 
const savedTheme = localStorage.getItem('theme');
if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
}

// 2. Global Loader
function load(id, file, pageName) {
    fetch(file)
        .then(res => res.text())
        .then(data => {
            const container = document.getElementById(id);
            if (container) {
                container.innerHTML = data;
                if (id === "revenue-chart" && typeof initRevenueChart === 'function') {
                    initRevenueChart();
                }
        async function initPage() {
    //  Calendar Load
    const calResponse = await fetch("Components/calendar-component.html");
    const calData = await calResponse.text();
    document.getElementById("calendar-view").innerHTML = calData;
    renderCalendar(); 

    // Stats Load 
    const statsResponse = await fetch("Components/calendar-stats.html");
    const statsData = await statsResponse.text();
    document.getElementById("stats-view").innerHTML = statsData;
}
initPage();
if (container) {
    container.innerHTML = data;

    // Dashboard chart
    if (id === "charts" && typeof initDashboardCharts === 'function') {
        initDashboardCharts();
    } 
    // Payments / Revenue chart
    else if (id === "revenue-chart" && typeof initRevenueChart === 'function') {
        initRevenueChart();
    }
    // Load function ke andar ye else-if check karo
else if (id === "bar-chart-container") {
    if (typeof initReportsCharts === 'function') {
        initReportsCharts();
    }
}
}        
// Sidebar active state highlight logic
if (id === "sidebar" && pageName) {
    const activeLink = document.getElementById(`nav-${pageName}`);
    if (activeLink) {
        
        activeLink.classList.remove("text-indigo-100", "hover:bg-white/10", "hover:text-white");
        
        activeLink.classList.add("bg-white", "text-[#6366F1]", "font-semibold", "shadow-sm");
        
        
        const icon = activeLink.querySelector('svg');
        if(icon) icon.classList.add("text-[#6366F1]");
    }
} 
}
});
}

// 3. SINGLE MASTER LISTENER (Sidebar, Navbar, Theme, Search)
document.addEventListener('click', (e) => {
    // --- SIDEBAR ---
if (e.target.closest("#sidebar-close-btn")) {
    document.getElementById("sidebar-container")?.classList.remove("w-64", "p-4");
    document.getElementById("sidebar-container")?.classList.add("w-20", "p-2", "items-center");
    document.getElementById("sidebar-full-header")?.classList.add("hidden");
    document.getElementById("sidebar-collapsed-header")?.classList.remove("hidden");
    document.querySelectorAll(".nav-text").forEach(t => t.classList.add("hidden"));
    
    
    document.getElementById("sidebar-help-box")?.classList.add("hidden"); 
}

if (e.target.closest("#sidebar-open-btn")) {
    document.getElementById("sidebar-container")?.classList.remove("w-20", "p-2", "items-center");
    document.getElementById("sidebar-container")?.classList.add("w-64", "p-4");
    document.getElementById("sidebar-collapsed-header")?.classList.add("hidden");
    document.getElementById("sidebar-full-header")?.classList.remove("hidden");
    document.querySelectorAll(".nav-text").forEach(t => t.classList.remove("hidden"));
    
    
    document.getElementById("sidebar-help-box")?.classList.remove("hidden");
}

    // --- NAVBAR & THEME ---
    if (e.target.closest("#theme-toggle-btn")) {
        const isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
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
    
    // Close dropdowns on outside click
    if (!e.target.closest("#user-menu-btn") && !e.target.closest("#notif-btn")) {
        document.getElementById("user-dropdown")?.classList.add("hidden");
        document.getElementById("notif-dropdown")?.classList.add("hidden");
    }
});

// 4. SEARCH LOGIC (Stable version)
document.addEventListener('keyup', (e) => {
    const target = e.target;
    // Search Events
    if (target.placeholder?.includes("Search events")) {
        const val = target.value.toLowerCase();
        document.querySelectorAll('.grid > div').forEach(c => c.classList.toggle('hidden', !c.innerText.toLowerCase().includes(val)));
    }
    // Search Bookings
    if (target.placeholder?.includes("Search by booking ID")) {
        const val = target.value.toLowerCase();
        document.querySelectorAll('tbody tr').forEach(r => r.classList.toggle('hidden', !r.innerText.toLowerCase().includes(val)));
    }
    // Search function
    document.addEventListener('input', (e) => {
        if (e.target && e.target.placeholder === "Search users...") {
            const query = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#users-table tbody tr');
            rows.forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(query) ? '' : 'none';
            });
        }
    })
    const ctx = document.getElementById('revenueChart').getContext('2d');

// Gradient Creation
const gradient = ctx.createLinearGradient(0, 0, 0, 300);
gradient.addColorStop(0, '#6366f1'); // Indigo
gradient.addColorStop(1, '#3b82f6'); // Blue
});

//5. Revenue Chart
function initRevenueChart() {
    const canvas = document.getElementById('revenueChart');
    if (!canvas) return; 
    
    const ctx = canvas.getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, '#6366f1');
    gradient.addColorStop(1, '#3b82f6');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Revenue',
                data: [12000, 15000, 14000, 18000, 21000, 25000],
                backgroundColor: gradient,
                borderRadius: 10,
                barThickness: 50,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [5, 5], color: '#e2e8f0' }, ticks: { stepSize: 6500 } },
                x: { grid: { display: false } }
            }
        }
    });
}
// 6. Render Calendar
function renderCalendar() {
    const container = document.getElementById('calendar-cells');
    if (!container) return;

    container.innerHTML = ''; 
    
    // empty cell for Sunday
    container.innerHTML += `<div class="h-28"></div>`; 

    for (let i = 1; i <= 30; i++) {
        let eventHtml = '';
        let borderClass = 'border-slate-200 dark:border-slate-700'; 

        if (i === 15) eventHtml = `<div class="mt-2 bg-indigo-500 text-white text-[10px] px-2 py-1 rounded-md">Summer Festival</div>`;
        if (i === 20) eventHtml = `<div class="mt-2 bg-indigo-600 text-white text-[10px] px-2 py-1 rounded-md">Tech Conference</div>`;
        if (i === 30) eventHtml = `<div class="mt-2 bg-indigo-500 text-white text-[10px] px-2 py-1 rounded-md">Workshop</div>`;
        
        if (i === 11) borderClass = 'border-indigo-400 dark:border-indigo-500 ring-1 ring-indigo-200 dark:ring-0'; 

        container.innerHTML += `
            <div class="bg-white dark:bg-[#1f2040] border ${borderClass} rounded-2xl p-3 h-28 transition-all hover:bg-slate-100 dark:hover:bg-[#2a2b55] flex flex-col justify-between">
                <span class="text-slate-900 dark:text-white font-semibold text-sm">${i}</span>
                ${eventHtml}
            </div>`;
    }
}

// 7. Revenue  Line Chart & Donut Chart
function initDashboardCharts() {
    //  Revenue Line Chart
    const revCtx = document.getElementById('revenueChart')?.getContext('2d');
    if (revCtx) {
        new Chart(revCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    data: [4500, 5200, 4800, 6100, 7300, 8500],
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.2)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3
                }]
            },
           options: { 
            responsive: true, 
            maintainAspectRatio: false, 
            plugins: { legend: { display: false } },
            scales: {
                y: { 
                    beginAtZero: true, 
                    grid: { 
                        color: () => document.documentElement.classList.contains('dark') ? '#1e293b' : '#e2e8f0',
                        lineWidth: 1 
                    },
                    ticks: { color: '#64748b' }
                }, 
                x: { 
                    grid: { display: false },
                    ticks: { color: '#64748b' }
                }
            }
        }
    });
}

    //  Event Types Donut Chart
    const eventCtx = document.getElementById('eventChart')?.getContext('2d');
    if (eventCtx) {
        new Chart(eventCtx, {
            type: 'doughnut',
            data: {
                labels: ['Concerts', 'Conferences', 'Workshops', 'Sports'],
                datasets: [{
                    data: [40, 25, 20, 15],
                    backgroundColor: ['#6366f1', '#3b82f6', '#8b5cf6', '#10b981'],
                    borderWidth: 0
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false, 
                cutout: '75%',
                plugins: { legend: { display: false } }
            }
        });
    }
}
// 8. Reports 
function initReportsCharts() {
    //  User Growth (Line Chart)
    const growthCtx = document.getElementById('userGrowthChart')?.getContext('2d');
    if (growthCtx) {
        new Chart(growthCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'User Growth',
                    data: [500, 700, 850, 1000, 1250, 1600],
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }

    //  Popular Events (Horizontal Bar Chart)
    const eventsCtx = document.getElementById('popularEventsChart')?.getContext('2d');
    if (eventsCtx) {
        new Chart(eventsCtx, {
            type: 'bar',
            data: {
                labels: ['Summer Festival', 'Tech Conference', 'Gourmet & Grains Expo', 'Workshop'],
                datasets: [{
                    label: 'Popularity',
                    data: [4200, 1800, 1200, 900],
                    backgroundColor: ['#6366f1', '#3b82f6', '#60a5fa', '#818cf8'],
                    borderRadius: 8
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }
}
// 9. Toggle Switch
function toggleSwitch(id) {
    const toggle = document.getElementById(id);
    const circle = toggle.querySelector('div');

    // Background color toggle
    toggle.classList.toggle('bg-indigo-600');
    toggle.classList.toggle('bg-slate-300');
    toggle.classList.toggle('dark:bg-slate-700');

    // Circle position toggle (left vs right)
    circle.classList.toggle('right-1');
    circle.classList.toggle('left-1');
}
function toggleTheme() {
    // Ye Tailwind ki 'dark' class ko body ya document element par toggle karega
    document.documentElement.classList.toggle('dark');
    
    if (document.documentElement.classList.contains('dark')) {
        localStorage.theme = 'dark';
    } else {
        localStorage.theme = 'light';
    }
}
    