<?php
// Load emergency contact for this user
$ecRs = Database::search(
    "SELECT emergency_contact_name, emergency_contact_phone
     FROM user_profiles WHERE user_id = $userId LIMIT 1"
);
$ec = $ecRs ? $ecRs->fetch_assoc() : [];
$ecName  = htmlspecialchars($ec['emergency_contact_name']  ?? '');
$ecPhone = htmlspecialchars($ec['emergency_contact_phone'] ?? '');
?>

<div class="col-3-row-3 dashboard-card">
    <div class="card-header">
        <h3>Coping &amp; Support Tools</h3>
    </div>
    <div class="tools-grid">
        <div class="tool-item" data-tool="urge-surfing">
            <i data-lucide="waves" class="sidebar-icon" stroke-width="1"></i>
            <span class="tool-name">Urge Surfing</span>
        </div>
        <div class="tool-item" data-tool="grounding">
            <i data-lucide="leaf" class="sidebar-icon" stroke-width="1"></i>
            <span class="tool-name">Grounding Exercise</span>
        </div>
        <div class="tool-item" data-tool="emergency">
            <i data-lucide="phone-call" class="sidebar-icon" stroke-width="1"></i>
            <span class="tool-name">Emergency Contact</span>
        </div>
        <div class="tool-item" onclick="window.location='/user/recovery/journal'">
            <i data-lucide="book-open" class="sidebar-icon" stroke-width="1"></i>
            <span class="tool-name">Recovery Journal</span>
        </div>
    </div>
</div>

<!-- ── Urge Surfing Modal ─────────────────────────────────────── -->
<div class="coping-modal" id="modal-urge-surfing" aria-hidden="true">
    <div class="coping-modal-box">
        <button class="coping-modal-close" aria-label="Close">&times;</button>
        <h3>Urge Surfing</h3>
        <p class="coping-modal-sub">Ride out the craving — it will pass. Most urges peak and fade within 15–20 minutes.</p>

        <div class="urge-timer-display">
            <span id="urgeTimerLabel">15:00</span>
        </div>
        <p class="urge-instruction" id="urgeInstruction">Focus on the sensation. Observe it without acting on it.</p>

        <div class="coping-modal-actions">
            <button class="btn btn-primary" id="urgeStartBtn">Start Timer</button>
            <button class="btn btn-secondary" id="urgeResetBtn" style="display:none;">Reset</button>
        </div>
    </div>
</div>

<!-- ── Grounding Exercise Modal ───────────────────────────────── -->
<div class="coping-modal" id="modal-grounding" aria-hidden="true">
    <div class="coping-modal-box">
        <button class="coping-modal-close" aria-label="Close">&times;</button>
        <h3>5-4-3-2-1 Grounding</h3>
        <p class="coping-modal-sub">Bring yourself back to the present moment.</p>

        <div class="grounding-step" id="groundingStep">
            <div class="grounding-number" id="groundingNum">5</div>
            <p class="grounding-prompt" id="groundingPrompt">Name <strong>5 things</strong> you can <strong>see</strong> right now.</p>
        </div>

        <div class="grounding-progress">
            <span class="grounding-dot active" data-step="0"></span>
            <span class="grounding-dot" data-step="1"></span>
            <span class="grounding-dot" data-step="2"></span>
            <span class="grounding-dot" data-step="3"></span>
            <span class="grounding-dot" data-step="4"></span>
        </div>

        <div class="coping-modal-actions">
            <button class="btn btn-primary" id="groundingNextBtn">Next</button>
        </div>
    </div>
</div>

<!-- ── Emergency Contact Modal ────────────────────────────────── -->
<div class="coping-modal" id="modal-emergency" aria-hidden="true">
    <div class="coping-modal-box">
        <button class="coping-modal-close" aria-label="Close">&times;</button>
        <h3>Emergency Contact</h3>
        <p class="coping-modal-sub">Save someone you trust to call in a crisis.</p>

        <?php if ($ecName || $ecPhone): ?>
        <div class="ec-saved-card">
            <i data-lucide="user-check" stroke-width="1.5"></i>
            <div>
                <strong><?= $ecName ?></strong>
                <a href="tel:<?= $ecPhone ?>" class="ec-call-link"><?= $ecPhone ?></a>
            </div>
        </div>
        <?php endif; ?>

        <form class="ec-form" action="/user/recovery/emergency-contact/save" method="post">
            <label class="ec-label">Name
                <input class="ec-input" type="text" name="ecName"
                       value="<?= $ecName ?>" placeholder="Contact name" required />
            </label>
            <label class="ec-label">Phone
                <input class="ec-input" type="tel" name="ecPhone"
                       value="<?= $ecPhone ?>" placeholder="+1 555 000 0000" required />
            </label>
            <div class="coping-modal-actions">
                <button type="submit" class="btn btn-primary">Save Contact</button>
            </div>
        </form>
    </div>
</div>
