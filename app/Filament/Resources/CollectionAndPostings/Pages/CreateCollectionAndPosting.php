<?php

namespace App\Filament\Resources\CollectionAndPostings\Pages;

use App\Filament\Resources\CollectionAndPostings\CollectionAndPostingResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\LoanAccount;
use App\Services\LoanAccountBalanceService;

class CreateCollectionAndPosting extends CreateRecord
{
    protected static string $resource = CollectionAndPostingResource::class;

    protected function afterCreate(): void
    {
        $record = $this->record;

        if ($record->status !== 'Posted') {
            return;
        }

        $loanAccount = LoanAccount::where('loan_number', $record->loan_number)->first();

        if ($loanAccount) {
            app(LoanAccountBalanceService::class)->update($loanAccount);
        }
    }
}
