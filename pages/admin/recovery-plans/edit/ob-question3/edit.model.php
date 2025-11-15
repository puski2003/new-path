<?php
require_once __DIR__ . '/../../recovery-plans.model.php';

class EditObQuestion3Model
{
    public static function getQuestion(int $id): ?array
    {
        return RecoveryPlansAdminModel::getStep3QuestionById($id);
    }

    public static function getScales(): array
    {
        return RecoveryPlansAdminModel::getAllScales();
    }

    public static function getModules(): array
    {
        return RecoveryPlansAdminModel::getAllModules();
    }

    public static function update(int $id, array $input): array
    {
        $data = [
            'questionText' => $input['questionText'] ?? '',
            'scaleId' => (int) ($input['scaleId'] ?? 1),
            'moduleId' => (int) ($input['moduleId'] ?? 1),
            'weight' => $input['weight'] ?? 1.0,
            'status' => $input['status'] ?? 'ACTIVE',
        ];

        $ok = RecoveryPlansAdminModel::updateStep3Question($id, $data);

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