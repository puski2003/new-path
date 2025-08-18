<?php
$name = $counselor['name'] ?? 'Counselor';
$bio = $counselor['bio'] ?? 'No biography available.';
?>

<div class="counselor-about-section">
    <h4>About <?= htmlspecialchars($name) ?></h4>
    <p><?= htmlspecialchars($bio) ?></p>
</div>
