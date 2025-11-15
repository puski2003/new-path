<?php
require_once __DIR__ . '/../../recovery-plans.model.php';

class CreateObQuestion2Model
{
    public static function getScales(): array
    {
        return RecoveryPlansAdminModel::getAllScales();
    }

    public static function create(array $input): array
    {
        $data = [
            'questionText' => $input['questionText'] ?? '',
            'scaleId' => (int) ($input['scaleId'] ?? 1),
            'path' => $input['path'] ?? 'BOTH',
            'weight' => $input['weight'] ?? 1.0,
            'status' => $input['status'] ?? 'ACTIVE',
        ];

        if (empty($data['questionText'])) {
            return [
                'ok' => false,
                'message' => 'Question text is required.',
            ];
        }

        $ok = RecoveryPlansAdminModel::createStep2Question($data);

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