<?php

namespace App\Filament\Resources\SavingsAccounts\Tables;

use App\Models\LoanAccount;
use App\Models\LoanApplication;
use App\Models\LoanApplicationCollstat;
use App\Models\LoanApplicationStatusLog;
use App\Models\PenaltyRule;
use App\Models\SavingsAccountDetail;
use App\Models\SavingsAccountTransaction;
use App\Services\CoopFeeCalculatorService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;


class SavingsAccountsTable
{
    public static function table(Table $table): Table
    {
        $isMember = Filament::auth()->user()?->isMember() ?? false;

        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $user = auth()->user();
                if (!($user?->hasAnyRole(['Admin', 'super_admin']) ?? false)) {
                    $query->where('profile_id', $user?->profile_id);
                }

                return $query;
            })
            ->columns([
                TextColumn::make('member.full_name')
                    ->label('Member Name')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('member.profile', function (Builder $q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->leftJoin('profiles', 'savings_accounts.profile_id', '=', 'profiles.profile_id')
                            ->orderBy('profiles.first_name', $direction)
                            ->orderBy('profiles.last_name', $direction)
                            ->select('loan_applications.*');
                    }),

                TextColumn::make('savingsType.name')
                    ->label('Type')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('type')
                    ->label('Deposit or Withdrawal')
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('PHP')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Approved' => 'success',
                        'Pending' => 'warning',
                        'Rejected' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),


            ])->bulkActions([])
            ->recordActionsPosition(\Filament\Tables\Enums\RecordActionsPosition::BeforeColumns)
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'Pending' => 'Pending',
                        'Approved' => 'Approved',
                        'Rejected' => 'Rejected',
                    ]),

                SelectFilter::make('savings_type_id')
                    ->label('Savings Type')
                    ->relationship('savingsType', 'name'),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('approve')
                        ->label('Approve')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(function ($record): bool {
                            $user = auth()->user();

                            return in_array($record->status, ['Pending'], true)
                                && ($user?->hasAnyRole(['Admin', 'super_admin']) ?? false);
                        })
                        ->form([
                            Textarea::make('notes')
                                ->label('Notes'),
                        ])
                        ->action(function ($record, array $data) {
                            if (! (auth()->user()?->hasAnyRole(['Admin', 'super_admin']) ?? false)) {
                                Notification::make()
                                    ->title('Unauthorized')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            if ($record->status === 'Approved') {
                                Notification::make()
                                    ->title('Already approved')
                                    ->warning()
                                    ->send();
                                return;
                            }
                            $record->update([
                                'status'      => 'Approved',
                                'approved_at' => now(),
                            ]);

                            if ($record->type === 'deposit') {
                                $type = 'deposit';
                            } elseif ($record->type === 'withdrawal') {
                                $type = 'withdrawal';
                            }

                            SavingsAccountTransaction::create([
                                'savings_account_id' => $record->id,
                                $type           => $record->amount,
                                'transaction_date'  => now(),
                                'notes'          => $data['notes'],
                                'posted_by_user_id' =>auth()->id(),
                            ]);

                            Notification::make()
                                ->title('Savings Approved')
                                ->success()
                                ->send();
                        }),

                    Action::make('reject')
                        ->label('Reject')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(function ($record): bool {
                            $user = auth()->user();

                            return in_array($record->status, ['Pending', 'Under Review'], true)
                                && ($user?->hasAnyRole(['Admin', 'super_admin']) ?? false);
                        })
                        ->form([Textarea::make('notes')->required()])
                        ->action(function ($record, array $data) {
                            if (! (auth()->user()?->hasAnyRole(['Admin', 'super_admin']) ?? false)) {
                                Notification::make()
                                    ->title('Unauthorized')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            $from = $record->status;
                            $record->update(['status' => 'Rejected']);

                            Notification::make()
                                ->title('Rejected')
                                ->success()
                                ->send();
                        }),

                    Action::make('cancel')
                        ->label('Cancel')
                        ->icon('heroicon-o-x-mark')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->visible(function ($record): bool {
                            $user = auth()->user();

                            if (! $user) {
                                return false;
                            }

                            return in_array($record->status, ['Pending', 'Under Review'], true)
                                && $user->hasAnyRole(['Admin', 'super_admin']);
                        })
                        ->action(function ($record) {
                            $user = auth()->user();

                            $canCancel = match (true) {
                                ! $user => false,
                                default => in_array($record->status, ['Pending', 'Under Review'], true)
                                    && $user->hasAnyRole(['Admin', 'super_admin']),
                            };

                            if (! $canCancel) {
                                Notification::make()
                                    ->title('Unauthorized')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            $from = $record->status;
                            $record->update(['status' => 'Cancelled']);

                            LoanApplicationStatusLog::create([
                                'loan_application_id' => $record->loan_application_id,
                                'from_status'         => $from,
                                'to_status'           => 'Cancelled',
                                'changed_by_user_id'  => auth()->id(),
                                'changed_at'          => now(),
                            ]);

                            Notification::make()
                                ->title('Cancelled')
                                ->success()
                                ->send();
                        }),

                ])->tooltip('Actions'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
