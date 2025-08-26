<?php
$profilePicture = $currentCounselor['profilePictureUrl'] ?? '/assets/img/avatar.png';
$savedAvailability = json_decode($currentCounselor['availabilitySchedule'] ?? '{}', true);
if (!is_array($savedAvailability)) {
    $savedAvailability = [];
}
?>
<div class="profile-modal-overlay" id="counselorProfileModalOverlay" style="display: none;">
    <div class="profile-modal">
        <div class="profile-modal-header">
            <h3 id="counselorProfileModalTitle">Edit Profile</h3>
            <button type="button" class="profile-modal-close" id="counselorProfileModalClose">&times;</button>
        </div>
        <div class="profile-modal-body">
            <p class="profile-modal-subtitle">Update your professional profile.</p>

            <form id="counselorProfileForm" action="/counselor/profile/update" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="counselorProfilePicture">Profile Picture</label>
                    <div class="profile-picture-upload">
                        <div class="current-profile-picture">
                            <img id="counselorCurrentProfilePic" src="<?= htmlspecialchars($profilePicture) ?>" alt="Current Profile Picture">
                        </div>
                        <input type="file" id="counselorProfilePicture" name="profilePicture" accept="image/*" class="form-input" style="display: none;">
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('counselorProfilePicture').click()">
                            <i data-lucide="camera" stroke-width="1"></i>
                            Change Picture
                        </button>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="counselorDisplayName">Display Name</label>
                        <input type="text" class="form-input" id="counselorDisplayName" name="displayName" value="<?= htmlspecialchars($currentCounselor['displayName'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="counselorTitle">Title</label>
                        <input type="text" class="form-input" id="counselorTitle" name="title" value="<?= htmlspecialchars($currentCounselor['title'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="counselorSpecialty">Specialization</label>
                    <select class="form-input" id="counselorSpecialty" name="specialty">
                        <?php
                        $specialties = [
                            '',
                            'Addiction Recovery',
                            'Substance Abuse',
                            'Alcohol Addiction',
                            'Behavioral Therapy',
                            'Mental Health',
                            'Trauma & PTSD',
                            'Family Therapy',
                            'Other',
                        ];
                        $selectedSpecialty = $currentCounselor['specialty'] ?? '';
                        foreach ($specialties as $specialty):
                        ?>
                            <option value="<?= htmlspecialchars($specialty) ?>" <?= $selectedSpecialty === $specialty ? 'selected' : '' ?>>
                                <?= $specialty === '' ? 'Select Specialization' : htmlspecialchars($specialty) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="counselorBio">Bio</label>
                    <textarea class="form-input" id="counselorBio" name="bio" rows="4" placeholder="Tell clients about your background and approach..."><?= htmlspecialchars($currentCounselor['bio'] ?? '') ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="counselorEmail">Email</label>
                        <input type="email" class="form-input" id="counselorEmail" name="email" value="<?= htmlspecialchars($currentCounselor['email'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="counselorPhone">Phone Number</label>
                        <input type="tel" class="form-input" id="counselorPhone" name="phoneNumber" value="<?= htmlspecialchars($currentCounselor['phoneNumber'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="counselorFee">Consultation Fee ($)</label>
                    <input type="number" class="form-input" id="counselorFee" name="consultationFee" min="0" step="0.01" value="<?= htmlspecialchars((string) ($currentCounselor['consultationFee'] ?? '')) ?>">
                </div>

                <div class="form-divider"></div>
                <h4 class="form-section-title">Availability Schedule</h4>
                <p class="form-help">Toggle days on/off. Add multiple time slots per day (e.g. 9–12 and 14–17).</p>

                <?php
                // Helper: render <option> tags for hours 06:00–22:00
                function renderTimeOptions(string $selected): string {
                    $out = '';
                    for ($h = 6; $h <= 22; $h++) {
                        $val   = sprintf('%02d:00', $h);
                        $label = date('g:i A', mktime($h, 0, 0));
                        $sel   = $selected === $val ? ' selected' : '';
                        $out  .= "<option value=\"{$val}\"{$sel}>{$label}</option>";
                    }
                    return $out;
                }

                // Normalize a saved day value to an array of [{start,end}]
                function normalizeSlots(mixed $raw): array {
                    if (empty($raw)) return [];
                    // Old format: {start:'09:00', end:'17:00'}
                    if (isset($raw['start'])) return [$raw];
                    // New format: [{start,end}, ...]
                    return array_values((array)$raw);
                }

                $allDays = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
                ?>
                <div class="availability-list">
                    <?php foreach ($allDays as $day):
                        $slots   = normalizeSlots($savedAvailability[$day] ?? null);
                        $enabled = !empty($slots);
                        // Always show at least one default row when editing
                        if (empty($slots)) $slots = [['start' => '09:00', 'end' => '17:00']];
                    ?>
                    <div class="avail-day-row" id="day_<?= $day ?>">
                        <div class="avail-day-header">
                            <label class="avail-day-toggle">
                                <input type="checkbox"
                                       name="<?= $day ?>_enabled"
                                       id="<?= $day ?>_enabled"
                                       <?= $enabled ? 'checked' : '' ?>
                                       onchange="toggleAvailDay('<?= $day ?>')">
                                <span class="avail-day-name"><?= ucfirst($day) ?></span>
                            </label>
                            <button type="button" class="avail-add-slot"
                                    onclick="addAvailSlot('<?= $day ?>')"
                                    <?= !$enabled ? 'style="display:none"' : '' ?>>
                                + Add Slot
                            </button>
                        </div>
                        <div class="avail-slots-container"
                             id="<?= $day ?>_slots"
                             <?= !$enabled ? 'style="display:none"' : '' ?>>
                            <?php foreach ($slots as $i => $slot): ?>
                            <div class="avail-slot-row">
                                <select name="<?= $day ?>_slots[<?= $i ?>][start]" class="avail-time-select">
                                    <?= renderTimeOptions($slot['start'] ?? '09:00') ?>
                                </select>
                                <span class="avail-to">to</span>
                                <select name="<?= $day ?>_slots[<?= $i ?>][end]" class="avail-time-select">
                                    <?= renderTimeOptions($slot['end'] ?? '17:00') ?>
                                </select>
                                <?php if ($i > 0): ?>
                                <button type="button" class="avail-remove-slot" onclick="removeAvailSlot(this)">×</button>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <script>
                function toggleAvailDay(day) {
                    var cb      = document.getElementById(day + '_enabled');
                    var slots   = document.getElementById(day + '_slots');
                    var addBtn  = document.querySelector('#day_' + day + ' .avail-add-slot');
                    if (cb.checked) {
                        slots.style.display  = '';
                        addBtn.style.display = '';
                    } else {
                        slots.style.display  = 'none';
                        addBtn.style.display = 'none';
                    }
                }

                function makeTimeSelect(name, selected) {
                    var sel = document.createElement('select');
                    sel.name      = name;
                    sel.className = 'avail-time-select';
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

                function addAvailSlot(day) {
                    var container = document.getElementById(day + '_slots');
                    var index     = container.querySelectorAll('.avail-slot-row').length;

                    var row = document.createElement('div');
                    row.className = 'avail-slot-row';

                    row.appendChild(makeTimeSelect(day + '_slots[' + index + '][start]', '09:00'));

                    var toSpan = document.createElement('span');
                    toSpan.className   = 'avail-to';
                    toSpan.textContent = 'to';
                    row.appendChild(toSpan);

                    row.appendChild(makeTimeSelect(day + '_slots[' + index + '][end]', '17:00'));

                    var removeBtn = document.createElement('button');
                    removeBtn.type      = 'button';
                    removeBtn.className = 'avail-remove-slot';
                    removeBtn.textContent = '×';
                    removeBtn.addEventListener('click', function() { removeAvailSlot(this); });
                    row.appendChild(removeBtn);

                    container.appendChild(row);
                }

                function removeAvailSlot(btn) {
                    var row       = btn.closest('.avail-slot-row');
                    var container = row.closest('.avail-slots-container');
                    row.remove();
                    // Re-index remaining rows so PHP parses them as a proper array
                    container.querySelectorAll('.avail-slot-row').forEach(function(r, i) {
                        r.querySelectorAll('select').forEach(function(sel) {
                            sel.name = sel.name.replace(/\[\d+\]/, '[' + i + ']');
                        });
                    });
                }
                </script>

                <div class="profile-modal-actions">
                    <button type="button" class="btn btn-secondary" id="cancelCounselorProfile">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </div>
            </form>
        </div>
    </div>
</div>
