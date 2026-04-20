<?php

class ExtendSuccessModel
{
    public static function getForDisplay(int $extensionId, int $userId): ?array
    {
        $ext = ExtendModel::getFullById($extensionId);
        if (!$ext || $ext['userId'] !== $userId || $ext['status'] !== 'paid') {
            return null;
        }

        $totalMinutes = $ext['originalDuration'] + $ext['extendedMinutes'];
        $newEndTs     = strtotime((string)$ext['sessionDatetime']) + ($totalMinutes * 60);

        return array_merge($ext, [
            'newEndLabel'      => date('g:i A', $newEndTs),
            'sessionDateLabel' => date('F j, Y \a\t g:i A', strtotime((string)$ext['sessionDatetime'])),
        ]);
    }
}
