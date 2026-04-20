<?php

$errorMessage = null;
if (Request::isPost()) {
    $uploadedDocPath = null;
    if (!empty($_FILES['documentsFile']) && (int)($_FILES['documentsFile']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $uploadedDocPath = CounselorApplicationsModel::handleDocumentUpload($_FILES['documentsFile']);
        if ($uploadedDocPath === null) {
            $errorMessage = 'Failed to upload supporting document. Please use JPG, PNG, PDF, DOC, or DOCX (max 10 MB).';
        }
    }

    if ($errorMessage === null) {
        $input = $_POST;
        if ($uploadedDocPath !== null) {
            $input['documentsUrl'] = $uploadedDocPath;
        }
        $result = CounselorApplicationsModel::submit($input);
        if (!empty($result['ok'])) {
            Response::redirect('/counselor/application-success');
        }
        $errorMessage = $result['error'] ?? 'Error submitting application. Please try again.';
    }
}
