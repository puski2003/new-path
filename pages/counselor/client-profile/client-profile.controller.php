<?php

$clientId = (int) (Request::get('id') ?? 0);
if ($clientId <= 0) {
    Response::redirect('/counselor/clients');
}

$clientProfile = CounselorClientProfileModel::getProfile((int) ($user['counselorId'] ?? 0), $clientId);
if (!$clientProfile) {
    Response::redirect('/counselor/clients');
}
