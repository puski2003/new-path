<?php
require_once __DIR__ . '/../../recovery-plans.model.php';

class CreateObQuestion3Model
{
    public static function getScales(): array
    {
        return RecoveryPlansAdminModel::getAllScales();
    }

    public static function getModules(): array
    {
        return RecoveryPlansAdminModel::getAllModules();
    }

    public static function create(array $input): array
    {
        $data = [
            'questionText' => $input['questionText'] ?? '',
            'scaleId' => (int) ($input['scaleId'] ?? 1),
            'moduleId' => (int) ($input['moduleId'] ?? 1),
            'weight' => $input['weight'] ?? 1.0,
            'status' => $input['status'] ?? 'ACTIVE',
        ];

        if (empty($data['questionText'])) {
            return [
                'ok' => false,
                'message' => 'Question text is required.',
            ];
        }

        $ok = RecoveryPlansAdminModel::createStep3Question($data);

        if ($ok) {
            return [
                'ok' => true,
                'type' => 'success',
                'message' => 'Question created successfully.',
            ];
        }

        return [
            'ok' => false,
            'message' => 'Failed to create question.',
        ];
    }
}