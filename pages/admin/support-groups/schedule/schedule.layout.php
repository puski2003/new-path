<?php
$pageTitle = 'Schedule Group Session';
require_once __DIR__ . '/../../common/admin.html.head.php';
?>
<main class="admin-main-container">
    <?php require_once __DIR__ . '/../../common/admin.sidebar.php'; ?>
    <section class="admin-main-content">
        <div class="admin-sub-container-1" style="justify-content: space-between; align-items: center;">
            <h1>Schedule Group Session</h1>
            <a href="/admin/support-groups" class="admin-button admin-button--secondary"><span class="button-text">Back to Groups</span></a>
        </div>
        <div class="admin-sub-container-2">
            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($errorMessage) ?></div>
            <?php endif; ?>
            <form method="POST" class="admin-form" style="max-width: 800px;">
                <div class="form-group">
                    <label class="form-label" for="group_id">Support Group *</label>
                    <select class="form-select" id="group_id" name="group_id" required>
                        <option value="">Select a group</option>
                        <?php foreach ($groups as $group): ?>
                            <option value="<?= $group['groupId'] ?>" <?= ($_POST['group_id'] ?? '') == $group['groupId'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($group['name']) ?> (<?= htmlspecialchars($group['category']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="title">Session Title *</label>
                    <input class="form-input" type="text" id="title" name="title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" placeholder="e.g., Weekly AA Meeting" required />
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Description</label>
                    <textarea class="form-textarea" id="description" name="description" rows="3"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="session_datetime">Date & Time *</label>
                        <input class="form-input" type="datetime-local" id="session_datetime" name="session_datetime" value="<?= htmlspecialchars($_POST['session_datetime'] ?? '') ?>" required />
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="duration_minutes">Duration (minutes)</label>
                        <input class="form-input" type="number" id="duration_minutes" name="duration_minutes" value="<?= htmlspecialchars($_POST['duration_minutes'] ?? '60') ?>" min="15" max="480" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="session_type">Session Type *</label>
                    <select class="form-select" id="session_type" name="session_type" required onchange="toggleMeetingFields()">
                        <option value="video" <?= ($_POST['session_type'] ?? 'video') === 'video' ? 'selected' : '' ?>>Video</option>
                        <option value="in_person" <?= ($_POST['session_type'] ?? '') === 'in_person' ? 'selected' : '' ?>>In Person</option>
                    </select>
                </div>

                <div id="video_fields">
                    <div class="form-group">
                        <label class="form-label" for="meeting_link">Meeting Link *</label>
                        <input class="form-input" type="url" id="meeting_link" name="meeting_link" value="<?= htmlspecialchars($_POST['meeting_link'] ?? '') ?>" placeholder="https://meet.google.com/..." />
                    </div>
                </div>

                <div id="in_person_fields" style="display: none;">
                    <div class="form-group">
                        <label class="form-label" for="meeting_location">Meeting Location *</label>
                        <input class="form-input" type="text" id="meeting_location" name="meeting_location" value="<?= htmlspecialchars($_POST['meeting_location'] ?? '') ?>" placeholder="e.g., Community Center, Room 101" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="max_participants">Max Participants</label>
                    <input class="form-input" type="number" id="max_participants" name="max_participants" value="<?= htmlspecialchars($_POST['max_participants'] ?? '') ?>" min="1" placeholder="Leave empty for unlimited" />
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <input type="checkbox" name="is_recurring" id="is_recurring" value="1" <?= !empty($_POST['is_recurring']) ? 'checked' : '' ?> onchange="toggleRecurrenceFields()" />
                        This is a recurring session
                    </label>
                </div>

                <div id="recurrence_fields" style="display: none;">
                    <div class="form-group">
                        <label class="form-label" for="recurrence_pattern">Recurrence Pattern</label>
                        <select class="form-select" id="recurrence_pattern" name="recurrence_pattern">
                            <option value="weekly" <?= ($_POST['recurrence_pattern'] ?? '') === 'weekly' ? 'selected' : '' ?>>Weekly</option>
                            <option value="biweekly" <?= ($_POST['recurrence_pattern'] ?? '') === 'biweekly' ? 'selected' : '' ?>>Biweekly</option>
                            <option value="monthly" <?= ($_POST['recurrence_pattern'] ?? '') === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="/admin/support-groups" class="admin-button admin-button--secondary">Cancel</a>
                    <button type="submit" class="admin-button admin-button--primary">Schedule Session</button>
                </div>
            </form>
        </div>
    </section>
</main>

<script>
function toggleMeetingFields() {
    var sessionType = document.getElementById('session_type').value;
    document.getElementById('video_fields').style.display = sessionType === 'video' ? 'block' : 'none';
    document.getElementById('in_person_fields').style.display = sessionType === 'in_person' ? 'block' : 'none';
}

function toggleRecurrenceFields() {
    var isRecurring = document.getElementById('is_recurring').checked;
    document.getElementById('recurrence_fields').style.display = isRecurring ? 'block' : 'none';
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleMeetingFields();
    toggleRecurrenceFields();
});
</script>

<?php require_once __DIR__ . '/../../common/admin.footer.php'; ?>
</body>
</html>
