// COMPLETE DATASET MAP WITH ALL FIGMA ATTRIBUTES & EXACT PARAMETERS
const eventsDataset = [
    {
        title: "Summer Music Festival 2026",
        category: "Concerts",
        date: "2026-06-15",
        timeDisplay: "15/06/2026 at 18:00",
        location: "New York, NY",
        price: 89,
        rating: 4.8,
        seats: 2500,
        imageUrl: "https://images.unsplash.com/photo-1506157786151-b8491531f063?w=600",
        isTrending: true,
        isFeatured: true
    },
    {
        title: "Champions League Final",
        category: "Sports",
        date: "2026-05-28",
        timeDisplay: "28/05/2026 at 20:00",
        location: "London, UK",
        price: 250,
        rating: 4.9,
        seats: 850,
        imageUrl: "https://images.unsplash.com/photo-1431324155629-1a6dba1dec1d?w=600",
        isTrending: true,
        isFeatured: true
    },
    {
        title: "E-Sports Championship 2026",
        category: "Gaming",
        date: "2026-07-20",
        timeDisplay: "20/07/2026 at 14:00",
        location: "Los Angeles, CA",
        price: 45,
        rating: 4.7,
        seats: 1200,
        imageUrl: "https://images.unsplash.com/photo-1542751371-adc38448a05e?w=600",
        isTrending: true,
        isFeatured: false
    },
    {
        title: "AI & Tech Summit 2026",
        category: "Tech Conferences",
        date: "2026-08-10",
        timeDisplay: "10/08/2026 at 09:00",
        location: "San Francisco, CA",
        price: 199,
        rating: 4.9,
        seats: 450,
        imageUrl: "https://images.unsplash.com/photo-1515187029135-18ee286d815b?w=600",
        isTrending: false,
        isFeatured: true
    },
    {
        title: "Fashion Week Gala",
        category: "Fashion Shows",
        date: "2026-09-05",
        timeDisplay: "05/09/2026 at 19:00",
        location: "Paris, France",
        price: 350,
        rating: 4.8,
        seats: 200,
        imageUrl: "https://images.unsplash.com/photo-1509631179647-0177331693ae?w=600",
        isTrending: false,
        isFeatured: true
    },
    {
        title: "Jazz Night Under The Stars",
        category: "Concerts",
        date: "2026-06-30",
        timeDisplay: "30/06/2026 at 20:30",
        location: "Chicago, IL",
        price: 65,
        rating: 4.6,
        seats: 320,
        imageUrl: "https://images.unsplash.com/photo-1511192336575-5a79af67a629?w=600",
        isTrending: false,
        isFeatured: false
    }
];

// 1. Dynamic Component Fetch Function
function loadNavbarComponent() {
fetch('Components/navbar.html') // Navbar component ka correct relative path
        .catch(() => fetch('navbar.html'))
        .then(response => response.text())
        .then(htmlContent => {
            // Jahan navbar inject karna hai (e.g., <div id="navbar-placeholder">)
const placeholder = document.getElementById('navbar-mount');
            if (placeholder) {
                placeholder.innerHTML = htmlContent;
                
                // CRITICAL STEP: Inject hone ke foran baad routers link active karo
                initNavbarRoutingEngine();
            }
        })
        .catch(err => console.error("Navbar mapping failed:", err));
}

// 2. SPA Active Links/Borders Controller
function initNavbarRoutingEngine() {
    const dynamicLinks = document.querySelectorAll('.nav-link');
    
    dynamicLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const targetPage = link.getAttribute('data-page');
            
            if (targetPage) {
                // Aapka dynamic view switching method call hoga
                if (typeof switchSPAPage === 'function') {
                    switchSPAPage(targetPage); 
                }
                
                // Borders aur Active states switch karne ka code
                updateActiveLinkState(targetPage);
            }
        });
    });
}

// 3. Tab State Highlighting UI Changer
function updateActiveLinkState(activePage) {
    const allLinks = document.querySelectorAll('#navbar-links .nav-link');
    
    allLinks.forEach(link => {
        const pageAttr = link.getAttribute('data-page');
        if (pageAttr === activePage) {
            link.classList.remove('text-[#64748B]', 'border-transparent');
            link.classList.add('text-[#000000]', 'font-bold', 'border-[#4B4EFC]');
        } else {
            link.classList.remove('text-[#000000]', 'font-bold', 'border-[#4B4EFC]');
            link.classList.add('text-[#64748B]', 'border-transparent');
        }
    });
}

// Trigger load on application startup
document.addEventListener("DOMContentLoaded", () => {
    loadNavbarComponent();
    // Home page default render (prevents blank grid)
    buildEventsPageGrid(eventsDataset);
});
// FIGMA COMPLIANT VERTICAL GRID CONSOLE LOGIC (Renders exact 3-column cards grid layout)
function buildEventsPageGrid(filteredDataArray) {
    const gridHookNode = document.getElementById("events-card-grid");
    const labelCounter = document.getElementById("eventsCountLabel");
    
    if (!gridHookNode) return;
    
    if (labelCounter) {
        labelCounter.innerText = `${filteredDataArray.length} event${filteredDataArray.length === 1 ? '' : 's'} found`;
    }

    let outputStreamHTML = "";
    filteredDataArray.forEach(item => {
        // Build badges arrays dynamically matching figma rules
        const trendingBadge = item.isTrending ? `<div class="absolute top-3 left-3 bg-[#EF4444] text-white text-[9px] font-extrabold tracking-wider px-2 py-0.5 rounded uppercase shadow-xs">Trending</div>` : '';
        const featuredBadge = item.isFeatured ? `<div class="absolute bottom-3 left-3 bg-[#5C7CFA] text-white text-[9px] font-bold tracking-wider px-2 py-0.5 rounded uppercase shadow-xs">Featured</div>` : '';
        const priceLabelString = item.price === 0 ? "Free Entry" : `$${item.price}`;

        outputStreamHTML += `
        <div class="bg-white rounded-xl overflow-hidden border border-gray-100 shadow-[0_4px_15px_rgba(0,0,0,0.01)] flex flex-col justify-between text-left group hover:shadow-[0_8px_25px_rgba(0,0,0,0.04)] transition-all duration-300 relative w-full">
            
            <div class="relative w-full h-44 bg-slate-50 overflow-hidden flex-shrink-0">
                <img src="${item.imageUrl}" alt="${item.title}" class="w-full h-full object-cover group-hover:scale-103 transition-transform duration-500" onerror="this.src='https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=600'">
                ${trendingBadge}
                ${featuredBadge}
                <button class="absolute top-3 right-3 w-7 h-7 bg-white/90 hover:bg-white text-gray-700 rounded-full flex items-center justify-center shadow-xs transition-all cursor-pointer"><i class="fa-regular fa-heart text-[11px]"></i></button>
            </div>

            <div class="p-4 flex-grow flex flex-col justify-between space-y-3">
                <div class="space-y-1.5">
                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] font-bold text-[#5C7CFA] uppercase tracking-wider">${item.category}</span>
                        <span class="text-gray-300 text-[10px]">•</span>
                        <div class="flex items-center text-amber-500 gap-0.5 text-[11px] font-bold">
                            <i class="fa-solid fa-star text-[9px]"></i>
                            <span>${item.rating.toFixed(1)}</span>
                        </div>
                    </div>
                    
                    <h3 class="text-sm font-bold text-[#0F172A] leading-snug line-clamp-1 group-hover:text-[#5C7CFA] transition-colors">${item.title}</h3>
                    
                    <div class="space-y-1.5 text-[11px] font-medium text-gray-400 pt-0.5">
                        <div class="flex items-center gap-2"><i class="fa-regular fa-calendar text-gray-400 w-3.5 text-center"></i><span>${item.timeDisplay}</span></div>
                        <div class="flex items-center gap-2"><i class="fa-solid fa-location-dot text-gray-400 w-3.5 text-center"></i><span>${item.location}</span></div>
                        <div class="flex items-center gap-2"><i class="fa-solid fa-user-group text-gray-400 w-3.5 text-center"></i><span class="text-gray-400">${item.seats} seats available</span></div>
                    </div>
                </div>

                <div class="px-1 pt-2 border-t border-gray-50 flex items-center justify-between bg-white w-full">
                    <div class="flex flex-col">
                        <span class="text-[9px] font-bold uppercase text-gray-400 tracking-wider">From</span>
                        <span class="text-sm font-extrabold text-[#0F172A]">${priceLabelString}</span>
                    </div>
                    <button class="bg-[#5C7CFA] hover:bg-[#4C6EF5] text-white text-xs font-bold px-4 py-2 rounded-lg transition-all shadow-xs active:scale-95 cursor-pointer">Book Now</button>
                </div>
            </div>

        </div>`;
    });

    gridHookNode.innerHTML = outputStreamHTML || `<div class="col-span-full py-20 text-center text-xs font-bold text-gray-400 uppercase tracking-wider">No events found matching current criteria.</div>`;
}

// COMPLETE SYSTEM MULTI-FILTER PIPELINE AUTOMATION CONTROL
function filterEventsPanel() {
    const searchStringQuery = (document.getElementById("innerEventsSearch")?.value || "").toLowerCase();
    const selectedCategoryVal = document.getElementById("sidebarCategory")?.value || "All";
    const maxAllowedPriceLimit = parseInt(document.getElementById("sidebarPriceSlider")?.value || "500", 10);
    const selectedEventTypeRadio = document.querySelector('input[name="eventType"]:checked')?.value || "All";
    
    // Figma Added Fields Filters Mappings
    const targetDateValue = document.getElementById("sidebarDateField")?.value || "";
    const targetCityValue = (document.getElementById("sidebarCityField")?.value || "").toLowerCase();
    const minRatingThreshold = parseFloat(document.getElementById("sidebarRatingField")?.value || "0");
    const activeSeatsOnlyToggle = document.getElementById("sidebarSeatsToggle")?.checked || false;
    
    const activeSortRule = document.getElementById("sortBy")?.value || "relevance";

    let computeFilteredResultMatrix = eventsDataset.filter(event => {
        if (searchStringQuery && !event.title.toLowerCase().includes(searchStringQuery)) return false;
        if (selectedCategoryVal !== "All" && event.category !== selectedCategoryVal) return false;
        if (event.price > maxAllowedPriceLimit) return false;
        if (selectedEventTypeRadio === "Free" && event.price !== 0) return false;
        if (selectedEventTypeRadio === "Paid" && event.price === 0) return false;
        
        // Figma Fields Validation Checks
        if (targetDateValue && event.date !== targetDateValue) return false;
        if (targetCityValue && !event.location.toLowerCase().includes(targetCityValue)) return false;
        if (event.rating < minRatingThreshold) return false;
        if (activeSeatsOnlyToggle && event.seats <= 0) return false;
        
        return true;
    });

    // Sorting Engine Configurations
    if (activeSortRule === "price") {
        computeFilteredResultMatrix.sort((a, b) => a.price - b.price);
    } else if (activeSortRule === "date") {
        computeFilteredResultMatrix.sort((a, b) => new Date(a.date) - new Date(b.date));
    }

    buildEventsPageGrid(computeFilteredResultMatrix);
}

// FULL RECOVERY REINITIALIZATION RESET CONTROL CONSOLE
function resetAllFilters() {
    if(document.getElementById("innerEventsSearch")) document.getElementById("innerEventsSearch").value = "";
    if(document.getElementById("sidebarCategory")) document.getElementById("sidebarCategory").value = "All";
    if(document.getElementById("sidebarPriceSlider")) {
        document.getElementById("sidebarPriceSlider").value = 500;
        const displayLabel = document.getElementById("priceRangeDisplay");
        if(displayLabel) displayLabel.innerText = "$0 - $500";
    }
    const defaultRadioElement = document.querySelector('input[name="eventType"][value="All"]');
    if(defaultRadioElement) defaultRadioElement.checked = true;
    
    // Reset newly targeted figma component nodes
    if(document.getElementById("sidebarDateField")) document.getElementById("sidebarDateField").value = "";
    if(document.getElementById("sidebarCityField")) document.getElementById("sidebarCityField").value = "";
    if(document.getElementById("sidebarRatingField")) document.getElementById("sidebarRatingField").value = "0";
    if(document.getElementById("sidebarSeatsToggle")) document.getElementById("sidebarSeatsToggle").checked = false;
    if(document.getElementById("sortBy")) document.getElementById("sortBy").value = "relevance";

    buildEventsPageGrid(eventsDataset);
}

// Function to load components
async function loadComponent(id, file) {
    const element = document.getElementById(id);
    if (element) {
        try {
            const response = await fetch(file);
            if (response.ok) {
                const data = await response.text();
                element.innerHTML = data;
            }
        } catch (error) {
            console.error(`Error loading ${file}:`, error);
        }
    }
}

document.addEventListener("DOMContentLoaded", async () => {
    // Header Load
    const headerEl = document.getElementById('navbar-mount');
    if (headerEl) {
        try {
            const res = await fetch('header.html');
            if (res.ok) headerEl.innerHTML = await res.text();
        } catch (e) { console.error("Header load error:", e); }
    }

    // Footer Load
    const footerEl = document.getElementById('footer-mount');
    if (footerEl) {
        try {
            // Try multiple paths because different pages use different folder structures.
            const footerCandidates = [
                'footer.html',
                'Footer.html',
                'Dashboard-homepage-components/Footer.html',
                'Dashboard-homepage-components/footer.html',
                'Components/Footer.html',
                'Components/footer.html',
                'Dashboard-homepage-components/Footer.html',
            ];

            for (const file of footerCandidates) {
                const res = await fetch(file);
                if (res.ok) {
                    footerEl.innerHTML = await res.text();
                    break;
                }
            }
        } catch (e) {
            console.error("Footer load error:", e);
        }
    }
});
