<?php
$pageTitle = 'Edit Help Center';
require_once __DIR__ . '/../../common/admin.html.head.php';
?>
<main class="admin-main-container">
    <?php require_once __DIR__ . '/../../common/admin.sidebar.php'; ?>
    <section class="admin-main-content">
        <div class="admin-sub-container-1" style="justify-content: space-between; align-items: center;">
            <h1>Edit Help Center</h1>
            <a href="/admin/resources?tab=help-centers" class="admin-button admin-button--secondary"><span class="button-text">Back to Help Centers</span></a>
        </div>
        <div class="admin-sub-container-2">
            <?php if ($error !== ''): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <?php if ($success !== ''): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
            <?php if ($helpCenter): ?>
                <form method="POST" class="admin-form" id="editHelpCenterForm" novalidate style="max-width: 800px;">
                    <input type="hidden" name="helpCenterId" value="<?= $helpCenter['helpCenterId'] ?>">
                    <?php foreach (['name' => 'Help Center Name *', 'organization' => 'Organization', 'phoneNumber' => 'Phone Number', 'email' => 'Email', 'website' => 'Website', 'address' => 'Address', 'city' => 'City', 'state' => 'State', 'zipCode' => 'ZIP Code', 'availability' => 'Availability'] as $field => $label): ?>
                        <div class="form-group">
                            <label class="form-label" for="<?= $field ?>"><?= $label ?></label>
                            <input class="form-input" id="<?= $field ?>" name="<?= $field ?>"
                                <?= $field === 'name' ? 'required' : '' ?>
                                <?= $field === 'email' ? 'type="email"' : 'type="text"' ?>
                                value="<?= htmlspecialchars($helpCenter[$field] ?? '') ?>">
                            <?php if ($field === 'name'): ?>
                                <div class="field-error" id="nameError" style="color:#f44336;font-size:13px;margin-top:4px;display:none;"></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="type">Type *</label>
                            <select class="form-input" id="type" name="type" required>
                                <option value="">-- Select Type --</option>
                                <option value="hotline" <?= $helpCenter['type'] === 'hotline' ? 'selected' : '' ?>>Hotline</option>
                                <option value="chat" <?= $helpCenter['type'] === 'chat' ? 'selected' : '' ?>>Live Chat</option>
                                <option value="appointment" <?= $helpCenter['type'] === 'appointment' ? 'selected' : '' ?>>Appointment</option>
                                <option value="resources" <?= $helpCenter['type'] === 'resources' ? 'selected' : '' ?>>Resources</option>
                            </select>
                            <div class="field-error" id="typeError" style="color:#f44336;font-size:13px;margin-top:4px;display:none;"></div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="category">Category *</label>
                            <select class="form-input" id="category" name="category" required>
                                <option value="">-- Select Category --</option>
                                <option value="emergency" <?= $helpCenter['category'] === 'emergency' ? 'selected' : '' ?>>Emergency</option>
                                <option value="counseling" <?= $helpCenter['category'] === 'counseling' ? 'selected' : '' ?>>Counseling</option>
                                <option value="recovery" <?= $helpCenter['category'] === 'recovery' ? 'selected' : '' ?>>Recovery Plans</option>
                                <option value="community" <?= $helpCenter['category'] === 'community' ? 'selected' : '' ?>>Community</option>
                                <option value="technical" <?= $helpCenter['category'] === 'technical' ? 'selected' : '' ?>>Technical Support</option>
                            </select>
                            <div class="field-error" id="categoryError" style="color:#f44336;font-size:13px;margin-top:4px;display:none;"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="description">Description *</label>
                        <textarea class="form-textarea" id="description" name="description" rows="5" required><?= htmlspecialchars($helpCenter['description']) ?></textarea>
                        <div class="field-error" id="descriptionError" style="color:#f44336;font-size:13px;margin-top:4px;display:none;"></div>
                    </div>
                    <div class="form-group"><label class="form-label" for="specialties">Specialties</label><textarea class="form-textarea" id="specialties" name="specialties" rows="3"><?= htmlspecialchars($helpCenter['specialties']) ?></textarea></div>
                    <div class="form-group"><label><input type="checkbox" name="isActive" value="1" <?= $helpCenter['active'] ? 'checked' : '' ?>> Active (visible to users)</label></div>
                    <div class="form-actions"><a href="/admin/resources?tab=help-centers" class="admin-button admin-button--secondary">Cancel</a><button type="submit" class="admin-button admin-button--primary">Update Help Center</button></div>
                </form>

                <script>
                document.getElementById('editHelpCenterForm').addEventListener('submit', function(e) {
                    var valid = true;
                    var fields = [
                        { id: 'name', errId: 'nameError', msg: 'Help Center Name is required.' },
                        { id: 'type', errId: 'typeError', msg: 'Please select a type.' },
                        { id: 'category', errId: 'categoryError', msg: 'Please select a category.' },
                        { id: 'description', errId: 'descriptionError', msg: 'Description is required.' },
                    ];
                    fields.forEach(function(f) {
                        var el  = document.getElementById(f.id);
                        var err = document.getElementById(f.errId);
                        if (!el || !err) return;
                        if (!el.value.trim()) {
                            err.textContent = f.msg;
                            err.style.display = 'block';
                            el.style.borderColor = '#f44336';
                            valid = false;
                        } else {
                            err.style.display = 'none';
                            el.style.borderColor = '';
                        }
                    });
                    var emailEl = document.getElementById('email');
                    if (emailEl && emailEl.value.trim() && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailEl.value.trim())) {
                        emailEl.style.borderColor = '#f44336';
                        valid = false;
                    }
                    if (!valid) e.preventDefault();
                });
                </script>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../../common/admin.footer.php'; ?>
</body>
</html>
