<?php
require_once __DIR__ . '/../../../common/user.head.php';
require_once __DIR__ . '/write.model.php';

$userId = (int)$user['id'];
$entryId = (int)($_GET['id'] ?? 0);

$existing = null;
if ($entryId > 0) {
    $existing = WriteModel::getEntry($entryId, $userId);
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = [
        'entry_id' => (int)($_POST['entry_id'] ?? 0),
        'title' => trim($_POST['title'] ?? ''),
        'content' => trim($_POST['content'] ?? ''),
        'category_id' => (int)($_POST['category_id'] ?? 0),
        'mood' => trim($_POST['mood'] ?? ''),
        'is_highlight' => isset($_POST['is_highlight']) ? 1 : 0,
    ];

    if (strlen($postData['content']) < 1) {
        $error = 'Entry content cannot be empty.';
    } else {
        WriteModel::save($userId, $postData);
        Response::redirect('/user/recovery/journal?status=success');
    }
}

$categories = WriteModel::getCategories($userId);

$moods = [
    'Grateful'    => '🙏',
    'Hopeful'     => '🌱',
    'Calm'        => '😌',
    'Proud'       => '💪',
    'Motivated'   => '🔥',
    'Anxious'     => '😰',
    'Sad'         => '😔',
    'Overwhelmed' => '😓',
];

$data = [
    'existing' => $existing,
    'categories' => $categories,
    'error' => $error,
    'moods' => $moods,
];

$pageTitle = $existing ? 'Edit Entry' : 'New Journal Entry';
$pageStyle = ['user/recovery', 'user/journal'];

require __DIR__ . '/write.view.php';