<?php

/**
 * User Profile Modal Component
 * Expects $user (from JWT) and $userProfile (full DB record) to be available.
 * If $userProfile is not set, we load it here.
 */
if (!isset($userProfile)) {
    $uid = (int) $user['id'];
    $rs = Database::search(
        "SELECT u.user_id, u.email, u.first_name, u.last_name, u.display_name,
                u.profile_picture, u.bio, u.phone_number, u.age, u.gender,
                up.emergency_contact_name, up.emergency_contact_phone,
                up.sobriety_start_date, up.recovery_type,
                COALESCE(up.is_anonymous, 1) as is_anonymous
         FROM users u
         LEFT JOIN user_profiles up ON up.user_id = u.user_id
         WHERE u.user_id = $uid"
    );
    $userProfile = $rs->fetch_assoc() ?? [];
}
?>

<div class="profile-modal-overlay" id="profileModalOverlay" style="display: none;">
    <div class="profile-modal">
        <div class="profile-modal-header">
            <h3 id="profileModalTitle">Edit Profile</h3>
            <button type="button" class="profile-modal-close" id="profileModalClose">&times;</button>
        </div>
        <div class="profile-modal-body">
            <p class="profile-modal-subtitle">Update your profile information.</p>

            <form id="profileForm" action="/auth/profile/user/update" method="post" enctype="multipart/form-data">

                <div class="form-group">
                    <label for="profilePicture">Profile Picture</label>
                    <div class="profile-picture-upload">
                        <div class="current-profile-picture">
                            <img id="currentProfilePic"
                                src="<?= !empty($userProfile['profile_picture']) ? htmlspecialchars($userProfile['profile_picture']) : '/assets/img/avatar.png' ?>"
                                alt="Current Profile Picture">
                        </div>
                        <input type="file"
                            id="profilePicture"
                            name="profilePicture"
                            accept="image/*"
                            class="form-input"
                            style="display: none;" />
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('profilePicture').click()">
                            <i data-lucide="camera" stroke-width="1"></i>
                            Change Picture
                        </button>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="firstName">First Name</label>
                        <input type="text"
                            class="form-input"
                            id="firstName"
                            name="firstName"
                            value="<?= htmlspecialchars($userProfile['first_name'] ?? '') ?>"
                            required />
                    </div>
                    <div class="form-group">
                        <label for="lastName">Last Name</label>
                        <input type="text"
                            class="form-input"
                            id="lastName"
                            name="lastName"
                            value="<?= htmlspecialchars($userProfile['last_name'] ?? '') ?>"
                            required />
                    </div>
                </div>

                <div class="form-group">
                    <label for="displayName">Display Name</label>
                    <input type="text"
                        class="form-input"
                        id="displayName"
                        name="displayName"
                        value="<?= htmlspecialchars($userProfile['display_name'] ?? '') ?>"
                        required />
                </div>

                <div class="form-group">
                    <label for="bio">Bio</label>
                    <textarea class="form-input"
                        id="bio"
                        name="bio"
                        rows="3"
                        placeholder="Tell others about yourself..."><?= htmlspecialchars($userProfile['bio'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email"
                        class="form-input"
                        id="email"
                        name="email"
                        value="<?= htmlspecialchars($userProfile['email'] ?? '') ?>"
                        required />
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="age">Age</label>
                        <input type="number"
                            class="form-input"
                            id="age"
                            name="age"
                            value="<?= htmlspecialchars($userProfile['age'] ?? '') ?>"
                            min="13"
                            max="120" />
                    </div>
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select class="form-input" id="gender" name="gender">
                            <option value="">Select Gender</option>
                            <option value="male" <?= ($userProfile['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= ($userProfile['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                            <option value="other" <?= ($userProfile['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                            <option value="prefer_not_to_say" <?= ($userProfile['gender'] ?? '') === 'prefer_not_to_say' ? 'selected' : '' ?>>Prefer not to say</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="phoneNumber">Phone Number</label>
                    <input type="tel"
                        class="form-input"
                        id="phoneNumber"
                        name="phoneNumber"
                        value="<?= htmlspecialchars($userProfile['phone_number'] ?? '') ?>" />
                </div>

                <div class="form-group">
                    <label for="emergencyContactName">Emergency Contact Name</label>
                    <input type="text"
                        class="form-input"
                        id="emergencyContactName"
                        name="emergencyContactName"
                        value="<?= htmlspecialchars($userProfile['emergency_contact_name'] ?? '') ?>" />
                </div>

                <div class="form-group">
                    <label for="emergencyContactPhone">Emergency Contact Phone</label>
                    <input type="tel"
                        class="form-input"
                        id="emergencyContactPhone"
                        name="emergencyContactPhone"
                        value="<?= htmlspecialchars($userProfile['emergency_contact_phone'] ?? '') ?>" />
                </div>

                <!-- Privacy Section -->
                <div class="form-divider"></div>
                <h4 class="form-section-title">Privacy Settings</h4>

                <div class="form-group">
                    <label class="checkbox-container">
                        <input type="checkbox" 
                               id="isAnonymous" 
                               name="isAnonymous" 
                               value="1"
                               <?= !empty($userProfile['is_anonymous']) ? 'checked' : '' ?> />
                        <span>Anonymous Mode</span>
                    </label>
                    <p class="form-help">When enabled, other users will see "Anonymous User" instead of your name and profile picture in community posts.</p>
                </div>

                <!-- Recovery Tracking Section -->
                <div class="form-divider"></div>
                <h4 class="form-section-title">Recovery Tracking</h4>

                <div class="form-row">
                    <div class="form-group">
                        <label for="sobrietyStartDate">Sobriety Start Date</label>
                        <input type="date"
                            class="form-input"
                            id="sobrietyStartDate"
                            name="sobrietyStartDate"
                            value="<?= htmlspecialchars($userProfile['sobriety_start_date'] ?? '') ?>" />
                        <small class="form-help">When did your recovery journey begin?</small>
                    </div>
                    <div class="form-group">
                        <label for="recoveryType">Recovery Type</label>
                        <select class="form-input" id="recoveryType" name="recoveryType">
                            <option value="">Select Type</option>
                            <option value="substance" <?= ($userProfile['recovery_type'] ?? '') === 'substance' ? 'selected' : '' ?>>Substance Addiction</option>
                            <option value="alcohol" <?= ($userProfile['recovery_type'] ?? '') === 'alcohol' ? 'selected' : '' ?>>Alcohol</option>
                            <option value="gambling" <?= ($userProfile['recovery_type'] ?? '') === 'gambling' ? 'selected' : '' ?>>Gambling</option>
                            <option value="behavioral" <?= ($userProfile['recovery_type'] ?? '') === 'behavioral' ? 'selected' : '' ?>>Behavioral</option>
                            <option value="other" <?= ($userProfile['recovery_type'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                </div>

                <div class="profile-modal-actions">
                    <button type="button" class="btn btn-secondary" id="cancelProfile">Cancel</button>
                    <input type="submit" class="btn btn-primary" value="Update Profile" />
                </div>
            </form>
        </div>
    </div>
</div>
