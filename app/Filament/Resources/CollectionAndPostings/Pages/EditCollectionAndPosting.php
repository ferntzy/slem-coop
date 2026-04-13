<?php

namespace App\Filament\Resources\CollectionAndPostings\Pages;

use App\Filament\Resources\CollectionAndPostings\CollectionAndPostingResource;
use App\Models\LoanAccount;
use App\Services\LoanAccountBalanceService;
use App\Services\NotificationService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCollectionAndPosting extends EditRecord
{
    protected static string $resource = CollectionAndPostingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $record = $this->record;
        $originalData = $record->getOriginal();

        if ($record->status !== 'Posted') {
            return;
        }

        $loanAccount = LoanAccount::where('loan_number', $record->loan_number)->first();

        if ($loanAccount) {
            app(LoanAccountBalanceService::class)->update($loanAccount);
        }

        // Notify of amount changes
        if ($originalData['amount_paid'] !== $record->amount_paid && $loanAccount?->profile_id) {
            app(NotificationService::class)->notifyPaymentEdited(
                $loanAccount->profile_id,
                $originalData['amount_paid'],
                $record->amount_paid
            );

            app(NotificationService::class)->notifyAdmins(
                'Payment amount updated',
                "Collection #{$record->id} amount changed from ₱".number_format($originalData['amount_paid'], 2)
                .' to ₱'.number_format($record->amount_paid, 2)
            );
        }
    }
}
