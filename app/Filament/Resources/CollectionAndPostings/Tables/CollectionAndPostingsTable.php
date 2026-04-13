<?php

namespace App\Filament\Resources\CollectionAndPostings\Tables;

use App\Services\LoanAccountBalanceService;
use App\Services\NotificationService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CollectionAndPostingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->heading('Posted Payments')

            ->columns([

               

                TextColumn::make('member_name')
                    ->label('Member')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('loan_number')
                    ->label('Loan #')
                    ->searchable(),

                TextColumn::make('amount_paid')
                    ->label('Amount Paid')
                    ->money('PHP')
                    ->sortable(),

                TextColumn::make('payment_date')
                    ->label('Payment Date')
                    ->date('M d, Y')
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label('Method')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'Cash' => 'success',
                        'Bank Transfer' => 'info',
                        'Bank Deposit' => 'warning',
                        'Check' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'Posted' => 'success',
                        'Draft' => 'warning',
                        'Void' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('postedBy.name')
                    ->label('Posted By')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Posted At')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'Posted' => 'Posted',
                        'Draft' => 'Draft',
                        'Void' => 'Void',
                    ]),

                SelectFilter::make('payment_method')
                    ->label('Payment Method')
                    ->options([
                        'Cash' => 'Cash',
                        'Bank Transfer' => 'Bank Transfer',
                        'Bank Deposit' => 'Bank Deposit',
                        'Check' => 'Check',
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),

                    Action::make('approve')
                        ->label('Approve')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn ($record): bool => $record->status === 'Draft' && ! auth()->user()?->hasRole('Member'))
                        ->action(function ($record): void {
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

                                // Send payment confirmation notification
                                if (! $record->confirmation_notification_sent_at) {
                                    $profile = $loan->profile;
                                    if ($profile) {
                                        app(NotificationService::class)->sendPaymentConfirmation(
                                            $profile->profile_id,
                                            $record->amount_paid,
                                            $record->loan_number,
                                        );
                                        $record->update(['confirmation_notification_sent_at' => now()]);
                                    }
                                }
                            }

                            Notification::make()
                                ->title('Payment approved')
                                ->success()
                                ->send();
                        }),

                    Action::make('reject')
                        ->label('Reject')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn ($record): bool => $record->status === 'Draft' && ! auth()->user()?->hasRole('Member'))
                        ->form([
                            Textarea::make('reason')
                                ->label('Reason')
                                ->required()
                                ->maxLength(500),
                        ])
                        ->action(function ($record, array $data): void {
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
                        }),
                ])->tooltip('Actions'),
            ])
            ->recordActionsPosition(RecordActionsPosition::BeforeColumns)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
