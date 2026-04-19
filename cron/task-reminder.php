<?php
/**
 * task-reminder.php — daily cron that notifies users with consecutive missed tasks.
 *
 * Run daily:
 *   0 8 * * * php /path/to/new-path/cron/task-reminder.php
 *
 * To tune thresholds, pass arguments:
 *   php cron/task-reminder.php --window=7 --threshold=3
 *
 * Or edit the defaults inside NotificationService::remindAllMissedTasks().
 */

define('ROOT', dirname(__DIR__));

require_once ROOT . '/config/env.php';
require_once ROOT . '/config/database.php';
require_once ROOT . '/core/NotificationService.php';

$windowDays    = (int)($_SERVER['argv'][1] ?? 7);
$missThreshold = (int)($_SERVER['argv'][2] ?? 3);

Database::setUpConnection();
NotificationService::remindAllMissedTasks($windowDays, $missThreshold);

exit(0);