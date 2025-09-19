<?php
require_once __DIR__ . '/../../common/admin.head.php';
require_once __DIR__ . '/../model.php';

if (Request::isPost()) {
    $helpCenterId = (int) (Request::post('helpCenterId') ?? 0);
    if ($helpCenterId > 0) {
        HelpCenterModel::deleteHelpCenter($helpCenterId);
    }
}
Response::redirect('/admin/resources?tab=help-centers');
