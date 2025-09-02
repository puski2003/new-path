<?php
require_once __DIR__ . '/view.model.php';

$userId = (int) (Request::get('id') ?? 0);
$viewUser = ViewUserModel::getUser($userId);
$error = $viewUser ? '' : 'User not found.';
