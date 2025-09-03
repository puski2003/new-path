<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = 'Apply as a Counselor — New Path';
$authCss   = 'counselor-application.css';
require_once __DIR__ . '/../../auth/common/auth.head.php';

$specialties = [
    'Addiction Counseling',
    'Mental Health Counseling',
    'Family Therapy',
    'Cognitive Behavioral Therapy',
    'Trauma Counseling',
    'Youth Counseling',
    'Group Therapy',
    'Other',
];
?>
<body class="theme-counselor" style="height:auto;min-height:100vh;background:var(--color-bg-light-green);">

<!-- ============================================================
     TOP BAR
     ============================================================ -->
<header class="ca-topbar">
    <a href="/" class="ca-topbar__logo">
        <img src="/assets/img/logo.svg" alt="New Path">
        <span class="ca-topbar__logo-text">New<br>Path</span>
    </a>
    <a href="/" class="ca-topbar__back">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="15 18 9 12 15 6"/>
        </svg>
        Back to Home
    </a>
</header>

<!-- ============================================================
     HERO
     ============================================================ -->
<div class="ca-hero">
    <div class="ca-hero__badge">
        <span class="ca-hero__badge-dot"></span>
        Counselor Application
    </div>
    <h1>Apply as a Counselor</h1>
    <p>Join our team of certified professionals and help people find their path to recovery.</p>
</div>

<!-- ============================================================
     STEP INDICATORS
     ============================================================ -->
<div class="ca-steps-wrap">
    <div class="ca-steps">
        <?php
        $steps = ['Personal', 'Professional', 'Education', 'Availability'];
        foreach ($steps as $i => $label):
        ?>
            <div class="ca-step">
                <div class="ca-step__dot"><?= $i + 1 ?></div>
                <span class="ca-step__label"><?= $label ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ============================================================
     FORM
     ============================================================ -->
<main class="ca-body">

    <?php if (!empty($errorMessage)): ?>
        <div class="ca-alert">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <?= htmlspecialchars($errorMessage) ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" novalidate>

        <!-- ------------------------------------------------
             SECTION 1 — Personal Information
             ------------------------------------------------ -->
        <div class="ca-section">
            <div class="ca-section__header">
                <div class="ca-section__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                <div>
                    <h2 class="ca-section__title">Personal Information</h2>
                    <p class="ca-section__sub">Your basic contact details</p>
                </div>
            </div>

            <div class="ca-row">
                <div class="ca-group">
                    <label class="ca-label" for="fullName">Full Name <span class="ca-required">*</span></label>
                    <input class="ca-input" type="text" id="fullName" name="fullName" required
                           placeholder="Dr. Jane Smith"
                           value="<?= htmlspecialchars(Request::post('fullName') ?? '') ?>">
                </div>
                <div class="ca-group">
                    <label class="ca-label" for="email">Email Address <span class="ca-required">*</span></label>
                    <input class="ca-input" type="email" id="email" name="email" required
                           placeholder="jane@example.com"
                           value="<?= htmlspecialchars(Request::post('email') ?? '') ?>">
                </div>
            </div>

            <div class="ca-row">
                <div class="ca-group">
                    <label class="ca-label" for="phoneNumber">Phone Number <span class="ca-required">*</span></label>
                    <input class="ca-input" type="tel" id="phoneNumber" name="phoneNumber" required
                           placeholder="+94 77 123 4567"
                           value="<?= htmlspecialchars(Request::post('phoneNumber') ?? '') ?>">
                </div>
                <div class="ca-group">
                    <label class="ca-label" for="title">Professional Title</label>
                    <input class="ca-input" type="text" id="title" name="title"
                           placeholder="e.g. Licensed Clinical Social Worker"
                           value="<?= htmlspecialchars(Request::post('title') ?? '') ?>">
                </div>
            </div>
        </div>

        <!-- ------------------------------------------------
             SECTION 2 — Professional Information
             ------------------------------------------------ -->
        <div class="ca-section">
            <div class="ca-section__header">
                <div class="ca-section__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                    </svg>
                </div>
                <div>
                    <h2 class="ca-section__title">Professional Information</h2>
                    <p class="ca-section__sub">Your specialty, experience and practice details</p>
                </div>
            </div>

            <div class="ca-row">
                <div class="ca-group">
                    <label class="ca-label" for="specialty">Specialty <span class="ca-required">*</span></label>
                    <select class="ca-select" id="specialty" name="specialty" required>
                        <option value="">Select your specialty</option>
                        <?php foreach ($specialties as $s): ?>
                            <option value="<?= htmlspecialchars($s) ?>"
                                <?= Request::post('specialty') === $s ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="ca-group">
                    <label class="ca-label" for="languagesSpoken">Languages Spoken</label>
                    <input class="ca-input" type="text" id="languagesSpoken" name="languagesSpoken"
                           placeholder="e.g. English, Sinhala, Tamil"
                           value="<?= htmlspecialchars(Request::post('languagesSpoken') ?? '') ?>">
                </div>
            </div>

            <div class="ca-row">
                <div class="ca-group">
                    <label class="ca-label" for="experienceYears">Years of Experience</label>
                    <div class="ca-input-wrap">
                        <input class="ca-input ca-input--suffix" type="number" id="experienceYears"
                               name="experienceYears" min="0" max="50"
                               placeholder="0"
                               value="<?= htmlspecialchars(Request::post('experienceYears') ?? '') ?>">
                        <span class="ca-input-suffix">yrs</span>
                    </div>
                </div>
                <div class="ca-group">
                    <label class="ca-label" for="consultationFee">Consultation Fee (per session)</label>
                    <div class="ca-input-wrap">
                        <span class="ca-input-prefix">Rs.</span>
                        <input class="ca-input" type="number" id="consultationFee"
                               name="consultationFee" min="0" step="0.01"
                               placeholder="0.00"
                               value="<?= htmlspecialchars(Request::post('consultationFee') ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="ca-row ca-row--full">
                <div class="ca-group">
                    <label class="ca-label" for="bio">Professional Bio <span class="ca-required">*</span></label>
                    <textarea class="ca-textarea ca-textarea--tall" id="bio" name="bio" required
                              placeholder="Describe your approach to counseling, your philosophy, and what clients can expect when working with you..."><?= htmlspecialchars(Request::post('bio') ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- ------------------------------------------------
             SECTION 3 — Education & Credentials
             ------------------------------------------------ -->
        <div class="ca-section">
            <div class="ca-section__header">
                <div class="ca-section__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                        <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                    </svg>
                </div>
                <div>
                    <h2 class="ca-section__title">Education &amp; Credentials</h2>
                    <p class="ca-section__sub">Your academic background and licenses</p>
                </div>
            </div>

            <div class="ca-row ca-row--full" style="margin-bottom:16px;">
                <div class="ca-group">
                    <label class="ca-label" for="education">Education <span class="ca-required">*</span></label>
                    <textarea class="ca-textarea" id="education" name="education" required
                              placeholder="e.g. MSc in Clinical Psychology — University of Colombo, 2016&#10;BSc in Psychology — University of Kelaniya, 2014"><?= htmlspecialchars(Request::post('education') ?? '') ?></textarea>
                </div>
            </div>

            <div class="ca-row ca-row--full" style="margin-bottom:16px;">
                <div class="ca-group">
                    <label class="ca-label" for="certifications">Certifications &amp; Licenses</label>
                    <textarea class="ca-textarea" id="certifications" name="certifications"
                              placeholder="e.g. Licensed Professional Counselor (LPC) — 2018&#10;Certified Addiction Counselor (CAC) — 2019"><?= htmlspecialchars(Request::post('certifications') ?? '') ?></textarea>
                </div>
            </div>

            <div class="ca-row ca-row--full">
                <div class="ca-group">
                    <label class="ca-label">Supporting Documents</label>
                    <div class="ca-file-zone">
                        <input type="file" id="documentsFile" name="documentsFile"
                               accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                               onchange="showFileName(this)">
                        <div class="ca-file-zone__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="17 8 12 3 7 8"/>
                                <line x1="12" y1="3" x2="12" y2="15"/>
                            </svg>
                        </div>
                        <div class="ca-file-zone__title">Click to upload or drag &amp; drop</div>
                        <div class="ca-file-zone__sub">PDF, DOC, DOCX, JPG or PNG &mdash; max 10 MB</div>
                        <div id="fileNameDisplay"></div>
                    </div>
                    <p class="ca-hint">Upload a copy of your degree, license, or certification. Accepted by admin during review.</p>
                </div>
            </div>
        </div>

        <!-- ------------------------------------------------
             SECTION 4 — Availability
             ------------------------------------------------ -->
        <div class="ca-section">
            <div class="ca-section__header">
                <div class="ca-section__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                </div>
                <div>
                    <h2 class="ca-section__title">Availability</h2>
                    <p class="ca-section__sub">Toggle days on/off and add time slots for each day you're available</p>
                </div>
            </div>

            <?php
            function caRenderTimeOptions(string $selected): string {
                $out = '';
                for ($h = 6; $h <= 22; $h++) {
                    $val   = sprintf('%02d:00', $h);
                    $label = date('g:i A', mktime($h, 0, 0));
                    $sel   = $selected === $val ? ' selected' : '';
                    $out  .= "<option value=\"{$val}\"{$sel}>{$label}</option>";
                }
                return $out;
            }
            $caAllDays = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
            ?>
            <div class="ca-avail-list">
                <?php foreach ($caAllDays as $day): ?>
                <div class="ca-avail-day-row" id="ca_day_<?= $day ?>">
                    <div class="ca-avail-day-header">
                        <label class="ca-avail-day-toggle">
                            <input type="checkbox"
                                   name="<?= $day ?>_enabled"
                                   id="ca_<?= $day ?>_enabled"
                                   onchange="caToggleAvailDay('<?= $day ?>')">
                            <span class="ca-avail-day-name"><?= ucfirst($day) ?></span>
                        </label>
                        <button type="button" class="ca-avail-add-slot"
                                onclick="caAddAvailSlot('<?= $day ?>')"
                                style="display:none">
                            + Add Slot
                        </button>
                    </div>
                    <div class="ca-avail-slots-container"
                         id="ca_<?= $day ?>_slots"
                         style="display:none">
                        <div class="ca-avail-slot-row">
                            <select name="<?= $day ?>_slots[0][start]" class="ca-avail-time-select">
                                <?= caRenderTimeOptions('09:00') ?>
                            </select>
                            <span class="ca-avail-to">to</span>
                            <select name="<?= $day ?>_slots[0][end]" class="ca-avail-time-select">
                                <?= caRenderTimeOptions('17:00') ?>
                            </select>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <script>
            function caToggleAvailDay(day) {
                var cb     = document.getElementById('ca_' + day + '_enabled');
                var slots  = document.getElementById('ca_' + day + '_slots');
                var addBtn = document.querySelector('#ca_day_' + day + ' .ca-avail-add-slot');
                if (cb.checked) {
                    slots.style.display  = '';
                    addBtn.style.display = '';
                } else {
                    slots.style.display  = 'none';
                    addBtn.style.display = 'none';
                }
            }

            function caMakeTimeSelect(name, selected) {
                var sel = document.createElement('select');
                sel.name      = name;
                sel.className = 'ca-avail-time-select';
                for (var h = 6; h <= 22; h++) {
                    var val  = (h < 10 ? '0' : '') + h + ':00';
                    var ampm = h >= 12 ? 'PM' : 'AM';
                    var dh   = h > 12 ? h - 12 : (h === 0 ? 12 : h);
                    var opt  = document.createElement('option');
                    opt.value       = val;
                    opt.textContent = dh + ':00 ' + ampm;
                    if (val === selected) opt.selected = true;
                    sel.appendChild(opt);
                }
                return sel;
            }

            function caAddAvailSlot(day) {
                var container = document.getElementById('ca_' + day + '_slots');
                var index     = container.querySelectorAll('.ca-avail-slot-row').length;

                var row = document.createElement('div');
                row.className = 'ca-avail-slot-row';

                row.appendChild(caMakeTimeSelect(day + '_slots[' + index + '][start]', '09:00'));

                var toSpan = document.createElement('span');
                toSpan.className   = 'ca-avail-to';
                toSpan.textContent = 'to';
                row.appendChild(toSpan);

                row.appendChild(caMakeTimeSelect(day + '_slots[' + index + '][end]', '17:00'));

                var removeBtn = document.createElement('button');
                removeBtn.type        = 'button';
                removeBtn.className   = 'ca-avail-remove-slot';
                removeBtn.textContent = '×';
                removeBtn.addEventListener('click', function() { caRemoveAvailSlot(this); });
                row.appendChild(removeBtn);

                container.appendChild(row);
            }

            function caRemoveAvailSlot(btn) {
                var row       = btn.closest('.ca-avail-slot-row');
                var container = row.closest('.ca-avail-slots-container');
                row.remove();
                container.querySelectorAll('.ca-avail-slot-row').forEach(function(r, i) {
                    r.querySelectorAll('select').forEach(function(sel) {
                        sel.name = sel.name.replace(/\[\d+\]/, '[' + i + ']');
                    });
                });
            }
            </script>
        </div>

        <!-- ------------------------------------------------
             SUBMIT
             ------------------------------------------------ -->
        <div class="ca-submit-area">
            <p class="ca-submit-note">
                <strong>Ready to submit?</strong><br>
                Your application will be reviewed within 3–5 business days. We'll reach out via email.
            </p>
            <button type="submit" class="ca-submit-btn">
                Submit Application
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"/>
                    <polyline points="12 5 19 12 12 19"/>
                </svg>
            </button>
        </div>

    </form>
</main>

<script>
function showFileName(input) {
    var display = document.getElementById('fileNameDisplay');
    display.textContent = input.files.length > 0 ? '✓ ' + input.files[0].name : '';
}
</script>

</body>
</html>
