<?php

namespace App\Filament\Resources\MembershipApplications\Tables;

use App\Models\MemberDetail;
use App\Models\Profile;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MembershipApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),

                     Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => in_array($record->status, ['pending', 'under_review'], true))
                    ->action(function ($record) {
                        if ($record->approved_at) {
                            Notification::make()
                                ->title('Already approved')
                                ->warning()
                                ->send();
                
                            return;
                        }
                
                        $record->update([
                            'status'      => 'approved',
                            'approved_at' => now(),
                            'updated_by'  => auth()->id(),
                        ]);
                
                        $record->refresh();
                
                        $exists = MemberDetail::where('profile_id', $record->profile_id)->exists();
                
                        if (! $exists) {
                            Notification::make()
                                ->title('Missing branch assignment')
                                ->warning()
                                ->body('This application has no branch assignment to use for member creation.')
                                ->send();
                
                            return;
                        }
                
                        $profile = Profile::where('profile_id', $record->profile_id)->first();
                
                        if (! User::where('profile_id', $record->profile_id)->exists()) {
                            $currentYear = Carbon::now()->year;
                            $latest = User::where('coop_id', 'like', "COOP-{$currentYear}-%")
                                ->orderByDesc('coop_id')
                                ->first();
                
                            $newNumber = $latest ? ((int) substr($latest->coop_id, -3)) + 1 : 1;
                            $formattedNumber = str_pad($newNumber, 3, '0', STR_PAD_LEFT);
                            $newCoopId = "COOP-{$currentYear}-{$formattedNumber}";
                
                            $pin = random_int(1000, 9999);
                
                           $user = app(\App\Services\NotificationService::class)->createUserWithAutoPassword($profile);

                        if ($user) {
                            $user->update([
                                'coop_id' => $newCoopId,
                            ]);
                        }
                        
                        }
                
                        Notification::make()
                            ->title('Application Approved')
                            ->body('Member account created. Login credentials sent to ' . $profile->email . '.')
                            ->success()
                            ->send();
                    }),
                    Action::make('reject')
                        ->label('Reject')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn ($record) => in_array($record->status, ['pending', 'under_review'], true))
                        ->form([Textarea::make('reason')->label('Reason for Rejection')->required()])
                        ->action(function ($record, array $data) {
                            $record->update([
                                'status' => 'rejected',
                                'rejected_at' => now(),
                                'remarks' => $data['reason'],
                                'updated_by' => auth()->id(),
                            ]);

                            Notification::make()
                                ->title('Application Rejected')
                                ->danger()
                                ->send();
                        }),
                ])->tooltip('Actions'),
            ], position: RecordActionsPosition::BeforeCells)
            ->columns([
                TextColumn::make('full_name')
                    ->label('Applicant')
                    ->getStateUsing(fn ($record) => trim(
                        $record->first_name.' '.
                        ($record->middle_name ? $record->middle_name.' ' : '').
                        $record->last_name
                    ))
                    ->searchable(['first_name', 'last_name']),

                TextColumn::make('membershipType.name')
                    ->label('Membership Type')
                    ->sortable(),

                TextColumn::make('application_date')
                    ->label('Date Applied')
                    ->date()
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'under_review' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('remarks')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('creator.name')
                    ->label('Created By')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'under_review' => 'Under Review',
                        'rejected' => 'Rejected',
                    ]),

                SelectFilter::make('membership_type_id')
                    ->label('Membership Type')
                    ->relationship('membershipType', 'name'),
            ])
            ->modifyQueryUsing(fn ($query) => $query->whereNotIn('status', ['approved']))
            ->defaultSort('created_at', 'desc');
    }
}
