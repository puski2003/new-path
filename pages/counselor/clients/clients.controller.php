<?php

$clients = CounselorClientsModel::getAll((int) ($user['counselorId'] ?? 0));
$searchPlaceholder  = 'Search clients';
$searchId='clientSearch';
// $searchFilterType   = 'clients';
