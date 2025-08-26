<?php $calendarCounselorId = (int) ($currentCounselor['counselorId'] ?? 0); ?>
<div class="availability-section counselor-dashboard-calendar" id="counselorCalendar">
    <div class="availability-content">
        <div class="availability-content-body">
            <div class="calendar-container">
                <div class="calendar-shell">
                    <div class="calendar-header">
                        <button class="calendar-nav-btn" id="calPrevMonth" type="button" aria-label="Previous month">
                            <i data-lucide="arrow-left" stroke-width="1.8" class="calendar-nav-icon"></i>
                        </button>
                        <span class="calendar-month" id="calMonthYear">January 2026</span>
                        <button class="calendar-nav-btn" id="calNextMonth" type="button" aria-label="Next month">
                            <i data-lucide="arrow-right" stroke-width="1.8" class="calendar-nav-icon"></i>
                        </button>
                    </div>
                    <div class="calendar-grid">
                        <div class="calendar-weekdays">
                            <span>S</span><span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span>
                        </div>
                        <div class="calendar-days" id="calendarDays"></div>
                    </div>
                </div>

                <div class="time-slots-container">
                    <h5 id="selectedDateTitle">Scheduled Times</h5>
                    <div class="time-slots" id="daySessionSlots">
                        <p class="no-slots-message" id="noSlotsMessage">Select a highlighted date to see scheduled sessions</p>
                    </div>
                    <div class="session-detail-card" id="sessionDetailCard" hidden>
                        <div class="session-detail-time" id="sessionDetailTime"></div>
                        <div class="session-detail-client" id="sessionDetailClient"></div>
                        <div class="session-detail-type" id="sessionDetailType"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    const counselorId = <?= json_encode($calendarCounselorId) ?>;
    let currentMonth = new Date().getMonth();
    let currentYear = new Date().getFullYear();
    let sessionsData = [];
    let selectedDate = "";

    const months = [
        "January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
    ];

    document.addEventListener("DOMContentLoaded", () => {
        renderCalendar();
        fetchSessions();
        document.getElementById("calPrevMonth")?.addEventListener("click", () => changeMonth(-1));
        document.getElementById("calNextMonth")?.addEventListener("click", () => changeMonth(1));
    });

    function changeMonth(delta) {
        currentMonth += delta;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear -= 1;
        }
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear += 1;
        }

        selectedDate = "";
        renderCalendar();
        clearSessionDetails();
        fetchSessions();
    }

    function getDateKey(date) {
        return date.getFullYear() + "-" +
            String(date.getMonth() + 1).padStart(2, "0") + "-" +
            String(date.getDate()).padStart(2, "0");
    }

    function getSessionDatesSet() {
        return new Set(sessionsData.map((session) => session.date).filter(Boolean));
    }

    function renderCalendar() {
        const monthYear = document.getElementById("calMonthYear");
        const calendarDays = document.getElementById("calendarDays");
        if (!monthYear || !calendarDays) return;

        monthYear.textContent = `${months[currentMonth]} ${currentYear}`;
        calendarDays.innerHTML = "";

        const firstDay = new Date(currentYear, currentMonth, 1).getDay();
        const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
        const today = new Date();
        const sessionDates = getSessionDatesSet();

        for (let i = 0; i < firstDay; i += 1) {
            const emptySpan = document.createElement("span");
            emptySpan.classList.add("empty");
            calendarDays.appendChild(emptySpan);
        }

        for (let day = 1; day <= daysInMonth; day += 1) {
            const daySpan = document.createElement("span");
            daySpan.textContent = String(day);

            const date = new Date(currentYear, currentMonth, day);
            const dateStr = getDateKey(date);
            const hasSessions = sessionDates.has(dateStr);

            if (
                today.getDate() === day &&
                today.getMonth() === currentMonth &&
                today.getFullYear() === currentYear
            ) {
                daySpan.classList.add("today");
            }

            if (!hasSessions) {
                daySpan.classList.add("unavailable");
            } else {
                daySpan.classList.add("available");
                daySpan.dataset.date = dateStr;

                if (selectedDate === dateStr) {
                    daySpan.classList.add("selected");
                }

                daySpan.addEventListener("click", () => selectDate(dateStr));
            }

            calendarDays.appendChild(daySpan);
        }
    }

    function fetchSessions() {
        const startDate = `${currentYear}-${String(currentMonth + 1).padStart(2, "0")}-01`;
        const endDate = `${currentYear}-${String(currentMonth + 1).padStart(2, "0")}-${String(new Date(currentYear, currentMonth + 1, 0).getDate()).padStart(2, "0")}`;

        fetch(`/counselor/sessions/calendar?counselorId=${counselorId}&startDate=${startDate}&endDate=${endDate}`)
            .then((response) => response.ok ? response.json() : Promise.reject())
            .then((data) => {
                sessionsData = Array.isArray(data.sessions) ? data.sessions : [];
                renderCalendar();
                syncSelectedDate();
            })
            .catch(() => {
                sessionsData = [];
                renderCalendar();
                syncSelectedDate();
            });
    }

    function syncSelectedDate() {
        if (!selectedDate) {
            return;
        }

        const hasSessions = sessionsData.some((session) => session.date === selectedDate);
        if (!hasSessions) {
            selectedDate = "";
            clearSessionDetails();
            return;
        }

        selectDate(selectedDate);
    }

    function selectDate(dateStr) {
        selectedDate = dateStr;

        document.querySelectorAll("#calendarDays span.selected").forEach((day) => {
            day.classList.remove("selected");
        });

        const activeDay = document.querySelector(`#calendarDays span[data-date="${dateStr}"]`);
        if (activeDay) {
            activeDay.classList.add("selected");
        }

        showDayDetails(dateStr);
    }

    function showDayDetails(dateStr) {
        const title = document.getElementById("selectedDateTitle");
        const slotsContainer = document.getElementById("daySessionSlots");
        const noSlotsMessage = document.getElementById("noSlotsMessage");
        if (!title || !slotsContainer || !noSlotsMessage) return;

        const date = new Date(`${dateStr}T00:00:00`);
        title.textContent = `Sessions on ${months[date.getMonth()]} ${date.getDate()}`;

        const daySessions = sessionsData
            .filter((session) => session.date === dateStr)
            .sort((a, b) => String(a.time || "").localeCompare(String(b.time || "")));

        slotsContainer.querySelectorAll(".time-slot").forEach((slot) => slot.remove());

        if (!daySessions.length) {
            noSlotsMessage.style.display = "block";
            noSlotsMessage.textContent = "No sessions scheduled for this day";
            clearSessionDetails();
            return;
        }

        

        daySessions.forEach((session, index) => {
            const button = document.createElement("button");
            button.type = "button";
            button.className = "time-slot";
            button.dataset.time = session.time || "";
            button.textContent = session.time || "Session";
            button.addEventListener("click", () => selectSession(button, session));
            slotsContainer.appendChild(button);

            if (index === 0) {
                selectSession(button, session);
            }
        });
    }

    function selectSession(button, session) {
        document.querySelectorAll("#daySessionSlots .time-slot").forEach((slot) => {
            slot.classList.remove("selected");
        });

        button.classList.add("selected");

        const detailCard = document.getElementById("sessionDetailCard");
        const detailTime = document.getElementById("sessionDetailTime");
        const detailClient = document.getElementById("sessionDetailClient");
        const detailType = document.getElementById("sessionDetailType");
        if (!detailCard || !detailTime || !detailClient || !detailType) return;

        detailTime.textContent = session.time || "Session time unavailable";
        detailClient.textContent = session.clientName || "Client";
        detailType.textContent = String(session.type || "video").replace("_", " ");
        detailCard.hidden = false;
    }

    function clearSessionDetails() {
        const title = document.getElementById("selectedDateTitle");
        const noSlotsMessage = document.getElementById("noSlotsMessage");
        const detailCard = document.getElementById("sessionDetailCard");
        const slotsContainer = document.getElementById("daySessionSlots");

        if (title) {
            title.textContent = "Scheduled Times";
        }

        if (noSlotsMessage) {
            noSlotsMessage.style.display = "block";
            noSlotsMessage.textContent = "Select a highlighted date to see scheduled sessions";
        }

        if (slotsContainer) {
            slotsContainer.querySelectorAll(".time-slot").forEach((slot) => slot.remove());
        }

        if (detailCard) {
            detailCard.hidden = true;
        }
    }
})();
</script>
