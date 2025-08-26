<?php
/*
 * Counselor section-card partial — OPENS a dashboard-card div.
 * The INCLUDING layout is responsible for closing the </div>.
 *
 * Usage:
 *   $cardTitle  = 'Section Title';          // optional
 *   $cardAction = '<a href="…">…</a>';      // optional HTML placed in card-header alongside title
 *   $cardClass  = 'counselor-list-card';    // optional extra CSS class(es), defaults to counselor-list-card
 *   require __DIR__ . '/../common/counselor.section-card.php';
 *   // ... inner content ...
 *   </div>
 */
$cardClass = $cardClass ?? 'counselor-list-card';
?>
<div class="dashboard-card <?= htmlspecialchars($cardClass) ?>">
<?php if (!empty($cardTitle) || !empty($cardAction)): ?>
    <div class="card-header">
        <?php if (!empty($cardTitle)): ?><h3><?= htmlspecialchars($cardTitle) ?></h3><?php endif; ?>
        <?php if (!empty($cardAction)): echo $cardAction; endif; ?>
    </div>
<?php endif; ?>
