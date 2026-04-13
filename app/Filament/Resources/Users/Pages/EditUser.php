<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $record = $this->record;
        $originalData = $record->getOriginal();

        $changedFields = [];

        if ($originalData['is_active'] !== $record->is_active) {
            $oldStatus = $originalData['is_active'] ? 'Active' : 'Inactive';
            $newStatus = $record->is_active ? 'Active' : 'Inactive';
            $changedFields[] = "Status: {$oldStatus} → {$newStatus}";
            app(\App\Services\NotificationService::class)->notifyUserStatusChanged(
                $record,
                $oldStatus,
                $newStatus
            );
        }

        if ($originalData['username'] !== $record->username) {
            $changedFields[] = "Username: {$originalData['username']} → {$record->username}";
        }

        if (! empty($changedFields)) {
            app(\App\Services\NotificationService::class)->notifyAdminOfAccountChange(
                'User Account Updated',
                "User {$record->username} was updated: ".implode(', ', $changedFields)
            );
        }
    }
}
