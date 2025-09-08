<?php
require_once __DIR__ . '/counselor-view.model.php';

$counselorId = (int) (Request::get('id') ?? 0);
$counselor = CounselorViewModel::getCounselor($counselorId);
$recentSessions = $counselor ? CounselorViewModel::getCounselorSessions($counselorId) : [];
$recentReviews = $counselor ? CounselorViewModel::getCounselorReviews($counselorId) : [];
$error = $counselor ? '' : 'Counselor not found.';
