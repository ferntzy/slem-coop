<?php

namespace App\Filament\Resources\CollectionAndPostings\Pages;

use App\Filament\Resources\CollectionAndPostings\CollectionAndPostingResource;
use App\Services\LoanAccountBalanceService;
use App\Services\NotificationService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewCollectionAndPosting extends ViewRecord
{
    protected static string $resource = CollectionAndPostingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn (): bool => ! auth()->user()?->hasRole('Member')),

            Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check')
                ->color('success')
                ->requiresConfirmation()
                ->visible(function (): bool {
                    $record = $this->getRecord();

                    return $record->status === 'Draft'
                        && ! auth()->user()?->hasRole('Member');
                })
                ->action(function (): void {
                    $record = $this->getRecord();

                    if (auth()->user()?->hasRole('Member')) {
                        abort(403);
                    }

                    $record->status = 'Posted';
                    $record->audit_trail = [
                        'action' => 'Approved',
                        'user_id' => auth()->id(),
                        'timestamp' => now()->toISOString(),
                        'record_id' => $record->getKey(),
                    ];
                    $record->save();

                    $loan = $record->loanAccount;
                    if ($loan) {
                        app(LoanAccountBalanceService::class)->update($loan);
                    }

                    Notification::make()
                        ->title('Payment approved')
                        ->success()
                        ->send();

                    if ($loan?->profile_id) {
                        app(NotificationService::class)->notifyProfile(
                            $loan->profile_id,
                            'Payment approved',
                            "Payment #{$record->id} for loan account #{$loan->loan_account_id} has been approved."
                        );
                    }

                    app(NotificationService::class)->notifyAdmins(
                        'Payment approved',
                        "Collection #{$record->id} has been approved on loan account #{$record->loan_account_id}."
                    );
                }),

            Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(function (): bool {
                    $record = $this->getRecord();

                    return $record->status === 'Draft'
                        && ! auth()->user()?->hasRole('Member');
                })
                ->form([
                    Textarea::make('reason')
                        ->label('Reason')
                        ->required()
                        ->maxLength(500),
                ])
                ->action(function (array $data): void {
                    $record = $this->getRecord();

                    if (auth()->user()?->hasRole('Member')) {
                        abort(403);
                    }

                    $record->status = 'Void';
                    $record->audit_trail = [
                        'action' => 'Rejected',
                        'reason' => $data['reason'],
                        'user_id' => auth()->id(),
                        'timestamp' => now()->toISOString(),
                        'record_id' => $record->getKey(),
                    ];
                    $record->save();

                    Notification::make()
                        ->title('Payment rejected')
                        ->success()
                        ->send();

                    if ($record->loanAccount?->profile_id) {
                        app(NotificationService::class)->notifyPaymentVoided(
                            $record->loanAccount->profile_id,
                            $record->amount_paid,
                            $data['reason']
                        );
                    }

                    app(NotificationService::class)->notifyAdmins(
                        'Payment voided',
                        "Collection #{$record->id} (₱".number_format($record->amount_paid, 2).") has been voided. Reason: {$data['reason']}"
                    );
                }),
        ];
    }
}
