<?php
require_once __DIR__ . '/../../recovery-plans.model.php';

class EditObQuestion2Model
{
    public static function getQuestion(int $id): ?array
    {
        return RecoveryPlansAdminModel::getStep2QuestionById($id);
    }

    public static function getScales(): array
    {
        return RecoveryPlansAdminModel::getAllScales();
    }

    public static function update(int $id, array $input): array
    {
        $data = [
            'questionText' => $input['questionText'] ?? '',
            'scaleId' => (int) ($input['scaleId'] ?? 1),
            'path' => $input['path'] ?? 'BOTH',
            'weight' => $input['weight'] ?? 1.0,
            'status' => $input['status'] ?? 'ACTIVE',
        ];

        $ok = RecoveryPlansAdminModel::updateStep2Question($id, $data);

        if ($ok) {
            return [
                'ok' => true,
                'type' => 'success',
                'message' => 'Question updated successfully.',
            ];
        }

        return [
            'ok' => false,
            'message' => 'Failed to update question.',
        ];
    }
}