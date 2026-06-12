(function () {
  const launcherId = "eventhubAiLauncher";
  const panelId = "eventhubAiPanel";

  function initAiChatbot() {
    if (document.getElementById(launcherId)) return;

    const wrapper = document.createElement("div");
    wrapper.innerHTML = `
      <button id="${launcherId}" type="button" aria-label="Open EventHub AI"
        class="fixed bottom-5 right-5 z-[9998] w-16 h-16 rounded-2xl bg-[#0F172A] text-white shadow-2xl shadow-indigo-300/60 flex items-center justify-center hover:-translate-y-1 transition eventhub-ai-launcher">
        <span class="eventhub-ai-pulse"></span>
        <i class="fa-solid fa-wand-magic-sparkles text-xl relative z-10"></i>
      </button>

      <section id="${panelId}"
        class="hidden fixed bottom-24 right-5 z-[9998] w-[calc(100vw-2rem)] max-w-[430px] h-[min(640px,calc(100vh-7rem))] bg-white border border-slate-200 rounded-3xl shadow-2xl overflow-hidden eventhub-ai-panel">
        <header class="eventhub-ai-header bg-[#0F172A] text-white px-5 py-4 flex items-center justify-between relative overflow-hidden">
          <div class="eventhub-ai-header-shine"></div>
          <div class="flex items-center gap-3 relative z-10">
            <div class="w-11 h-11 rounded-2xl bg-white/10 border border-white/10 flex items-center justify-center">
              <i class="fa-solid fa-robot text-base"></i>
            </div>
            <div>
              <h3 class="text-base font-extrabold leading-tight">EventHub AI Command</h3>
              <p class="text-[11px] text-slate-300">Planner, vendor, refund and admin help</p>
            </div>
          </div>
          <button id="eventhubAiClose" type="button" aria-label="Close AI assistant" class="relative z-10 w-9 h-9 rounded-xl hover:bg-white/10">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </header>

        <div id="eventhubAiMessages" class="eventhub-ai-messages overflow-y-auto p-4 space-y-3 bg-slate-50">
          <div class="max-w-[92%] rounded-2xl rounded-tl-md bg-white border border-slate-200 px-4 py-3 text-xs text-slate-600 leading-relaxed">
            Assalam o Alaikum! Main updated EventHub AI hu. Main events recommend kar sakta hu, budget estimate bana sakta hu, vendor quote flow, OTP email verification, refunds, notifications aur admin workflow explain kar sakta hu.
          </div>
          <div id="eventhubAiStats" class="grid grid-cols-2 gap-2"></div>
        </div>

        <div class="eventhub-ai-composer px-4 py-3 border-t border-slate-100 bg-white">
          <div class="eventhub-ai-chips flex flex-wrap gap-2 mb-3">
            <button type="button" class="ai-chip">Explain vendor quote workflow</button>
            <button type="button" class="ai-chip">How does refund approval work?</button>
            <button type="button" class="ai-chip">Suggest wedding package in Karachi</button>
            <button type="button" class="ai-chip">Explain OTP email verification</button>
          </div>
          <form id="eventhubAiForm" class="flex items-center gap-2">
            <input id="eventhubAiInput" type="text" autocomplete="off"
              placeholder="Ask EventHub AI..."
              class="flex-1 min-w-0 border border-slate-200 rounded-2xl px-4 py-3 text-xs focus:outline-none focus:border-[#4B4EFC] focus:ring-4 focus:ring-indigo-50">
            <button type="submit" aria-label="Send message"
              class="w-12 h-12 rounded-2xl bg-[#4B4EFC] text-white flex items-center justify-center hover:bg-[#3B3DDF] transition">
              <i class="fa-solid fa-paper-plane text-xs"></i>
            </button>
          </form>
        </div>
      </section>
    `;

    document.body.appendChild(wrapper);
    addChatbotStyles();

    document.getElementById(launcherId).addEventListener("click", toggleAiChatbot);
    document.getElementById("eventhubAiClose").addEventListener("click", toggleAiChatbot);
    document.getElementById("eventhubAiForm").addEventListener("submit", handleAiSubmit);

    document.querySelectorAll(".ai-chip").forEach((chip) => {
      chip.addEventListener("click", () => {
        const message = chip.innerText;
        document.getElementById("eventhubAiInput").value = "";
        submitAiMessage(message);
      });
    });
  }

  function addChatbotStyles() {
    if (document.getElementById("eventhubAiStyles")) return;

    const style = document.createElement("style");
    style.id = "eventhubAiStyles";
    style.innerHTML = `
      .eventhub-ai-panel, .eventhub-ai-launcher { font-family: Poppins, Inter, Arial, sans-serif; }
      .eventhub-ai-panel {
        display: flex;
        flex-direction: column;
      }
      .eventhub-ai-panel.hidden {
        display: none;
      }
      .eventhub-ai-header,
      .eventhub-ai-composer {
        flex-shrink: 0;
      }
      .eventhub-ai-messages {
        flex: 1;
        min-height: 0;
      }
      .eventhub-ai-launcher { transform-style: preserve-3d; }
      .eventhub-ai-pulse {
        position: absolute;
        inset: -8px;
        border-radius: 24px;
        background: rgba(75, 78, 252, 0.18);
        animation: eventhubAiPulse 2.2s ease-in-out infinite;
      }
      .eventhub-ai-header-shine {
        position: absolute;
        inset: -40% auto auto -20%;
        width: 220px;
        height: 140px;
        background: radial-gradient(circle, rgba(99,102,241,0.45), transparent 68%);
        transform: rotate(18deg);
      }
      .ai-chip {
        border: 1px solid #E2E8F0;
        background: #F8FAFC;
        color: #475569;
        border-radius: 999px;
        padding: 7px 11px;
        font-size: 10px;
        font-weight: 800;
        transition: all 180ms ease;
      }
      .ai-chip:hover {
        border-color: #4B4EFC;
        color: #4B4EFC;
        background: #EEF2FF;
        transform: translateY(-1px);
      }
      .ai-stat {
        border: 1px solid #E2E8F0;
        background: white;
        border-radius: 16px;
        padding: 10px;
      }
      .ai-stat span {
        display: block;
        color: #64748B;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
      }
      .ai-stat strong {
        display: block;
        color: #0F172A;
        font-size: 18px;
        line-height: 1.2;
        margin-top: 2px;
      }
      .ai-typing {
        display: inline-flex;
        align-items: center;
        gap: 5px;
      }
      .ai-typing span {
        width: 6px;
        height: 6px;
        border-radius: 999px;
        background: #4B4EFC;
        animation: aiTypingBounce 1s ease-in-out infinite;
      }
      .ai-typing span:nth-child(2) { animation-delay: 0.15s; }
      .ai-typing span:nth-child(3) { animation-delay: 0.30s; }
      .ai-typing-label {
        color: #64748B;
        font-size: 11px;
        font-weight: 700;
        margin-left: 2px;
      }
      @keyframes aiTypingBounce {
        0%, 80%, 100% { transform: translateY(0); opacity: 0.35; }
        40% { transform: translateY(-4px); opacity: 1; }
      }
      @keyframes eventhubAiPulse {
        0%, 100% { transform: scale(0.92) translateZ(-8px); opacity: 0.55; }
        50% { transform: scale(1.08) translateZ(12px); opacity: 0.15; }
      }
      @media (max-width: 480px) {
        #eventhubAiPanel {
          left: 0.75rem;
          right: 0.75rem;
          bottom: 5.75rem;
          width: auto;
          max-width: none;
          height: min(620px, calc(100vh - 6.5rem));
        }
        .eventhub-ai-chips {
          max-height: 5.5rem;
          overflow-y: auto;
        }
        #eventhubAiLauncher {
          right: 1rem;
          bottom: 1rem;
          width: 3.75rem;
          height: 3.75rem;
        }
      }
    `;
    document.head.appendChild(style);
  }

  function toggleAiChatbot() {
    document.getElementById(panelId).classList.toggle("hidden");
  }

  function handleAiSubmit(event) {
    event.preventDefault();
    const input = document.getElementById("eventhubAiInput");
    const message = input.value.trim();

    if (!message) return;

    input.value = "";
    submitAiMessage(message);
  }

  async function askAi(message) {
    if (typeof askAiAssistant === "function") {
      return await askAiAssistant(message);
    }

    const endpoints = [
      "http://localhost:8000/index.php?action=ai_assistant",
      "http://127.0.0.1:8000/index.php?action=ai_assistant"
    ];

    let lastError = null;

    for (const endpoint of endpoints) {
      try {
        const response = await fetch(endpoint, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
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

  async function submitAiMessage(message) {
    addMessage(message, "user");
    const loadingId = addTypingMessage();
    const startedAt = Date.now();

    try {
      const response = await askAi(message);
      await wait(Math.max(0, 2200 - (Date.now() - startedAt)));
      const reply = response.status
        ? response.data.reply
        : response.message || "AI assistant is not available right now.";

      updateMessage(loadingId, reply);
      renderAiStats(response.data?.stats);
      renderAiSuggestions(response.data?.suggestions);
    } catch (error) {
      console.log("AI Assistant Error:", error);
      await wait(Math.max(0, 1200 - (Date.now() - startedAt)));
      updateMessage(loadingId, "AI assistant connect nahi ho saka. Backend server http://localhost:8000 par running hona chahiye.");
    }
  }

  function addMessage(text, type) {
    const messages = document.getElementById("eventhubAiMessages");
    const id = "ai-msg-" + Date.now() + "-" + Math.floor(Math.random() * 1000);
    const bubble = document.createElement("div");
    bubble.id = id;
    bubble.className = type === "user"
      ? "ml-auto max-w-[92%] rounded-2xl rounded-tr-md bg-[#4B4EFC] px-4 py-3 text-xs text-white leading-relaxed whitespace-pre-line break-words"
      : "max-w-[92%] rounded-2xl rounded-tl-md bg-white border border-slate-200 px-4 py-3 text-xs text-slate-600 leading-relaxed whitespace-pre-line break-words";
    bubble.innerText = text;
    messages.appendChild(bubble);
    messages.scrollTop = messages.scrollHeight;
    return id;
  }

  function addTypingMessage() {
    const messages = document.getElementById("eventhubAiMessages");
    const id = "ai-msg-" + Date.now() + "-" + Math.floor(Math.random() * 1000);
    const bubble = document.createElement("div");
    bubble.id = id;
    bubble.className = "max-w-[92%] rounded-2xl rounded-tl-md bg-white border border-slate-200 px-4 py-3 text-xs text-slate-600 leading-relaxed";
    bubble.innerHTML = `
      <div class="ai-typing">
        <span></span><span></span><span></span>
        <span class="ai-typing-label">AI typing...</span>
      </div>
    `;
    messages.appendChild(bubble);
    messages.scrollTop = messages.scrollHeight;
    return id;
  }

  function updateMessage(id, text) {
    const bubble = document.getElementById(id);
    if (!bubble) return;
    bubble.innerText = text;
    const messages = document.getElementById("eventhubAiMessages");
    messages.scrollTop = messages.scrollHeight;
  }

  function renderAiStats(stats) {
    const box = document.getElementById("eventhubAiStats");
    if (!box || !stats) return;

    box.innerHTML = `
      <div class="ai-stat"><span>Active Events</span><strong>${stats.events || 0}</strong></div>
      <div class="ai-stat"><span>Requests</span><strong>${stats.custom_requests || 0}</strong></div>
      <div class="ai-stat"><span>Quotes</span><strong>${stats.quotes || 0}</strong></div>
      <div class="ai-stat"><span>Pending Refunds</span><strong>${stats.refunds_pending || 0}</strong></div>
    `;
  }

  function renderAiSuggestions(suggestions) {
    if (!Array.isArray(suggestions) || suggestions.length === 0) return;

    document.querySelectorAll(".ai-chip").forEach((chip, index) => {
      if (suggestions[index]) chip.innerText = suggestions[index];
    });
  }

  function wait(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms));
  }

  document.addEventListener("DOMContentLoaded", initAiChatbot);
})();
