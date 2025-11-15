<?php
require_once __DIR__ . '/../../common/admin.head.php';

// Delegate to the shared AI generation logic (same as counselor endpoint,
// but protected by admin auth instead of counselor auth).
require_once __DIR__ . '/ai-generate.php';
