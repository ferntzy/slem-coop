<?php

namespace App\Filament\Widgets;

use App\Models\LoanApplication;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class LoanOfficerPriorityQueueWidget extends BaseWidget
{
    protected static ?string $heading = 'Loan Officer Priority Queue';

    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        $user = Auth::user();

        if (! $user || $user->isMember()) {
            return false;
        }

        return static::isLoanOfficerUser($user);
    }

    public function getColumnSpan(): int|string|array
    {
        return 'full';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->priorityQueueQuery())
            ->columns([
                TextColumn::make('loan_application_id')
                    ->label('#')
                    ->sortable()
                    ->width('70px'),

                TextColumn::make('member.profile.full_name')
                    ->label('Member')
                    ->icon('heroicon-o-user')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('member.profile', function (Builder $profileQuery) use ($search) {
                            $profileQuery->where('first_name', 'like', "%{$search}%")
                                ->orWhere('middle_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    }),

                TextColumn::make('type.name')
                    ->label('Loan Type')
                    ->badge()
                    ->color('info'),

                TextColumn::make('amount_requested')
                    ->label('Amount')
                    ->money('PHP')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Approved' => 'success',
                        'Pending' => 'warning',
                        'Under Review' => 'info',
                        'Rejected' => 'danger',
                        'Cancelled' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('collateral_status')
                    ->label('Collateral')
                    ->badge()
                    ->formatStateUsing(function ($state, LoanApplication $record): string {
                        if ((float) $record->amount_requested <= 15000) {
                            return 'Not Required';
                        }

                        return $state ?: 'Pending Verification';
                    })
                    ->color(function ($state, LoanApplication $record): string {
                        $normalizedState = (float) $record->amount_requested <= 15000
                            ? 'Not Required'
                            : ($state ?: 'Pending Verification');

                        return match ($normalizedState) {
                            'Approved' => 'success',
                            'Pending Verification' => 'warning',
                            'Rejected' => 'danger',
                            default => 'gray',
                        };
                    }),

                TextColumn::make('created_at')
                    ->label('Queue Age')
                    ->formatStateUsing(function ($state): string {
                        if (! $state) {
                            return '—';
                        }

                        $days = Carbon::parse($state)->diffInDays(now());

                        return $days.' day'.($days === 1 ? '' : 's');
                    })
                    ->description(fn (LoanApplication $record): ?string => $record->created_at?->diffForHumans())
                    ->color(function ($state): string {
                        if (! $state) {
                            return 'gray';
                        }

                        $days = Carbon::parse($state)->diffInDays(now());

                        return $days >= 14 ? 'danger' : ($days >= 7 ? 'warning' : 'success');
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Pending' => 'Pending',
                        'Under Review' => 'Under Review',
                        'Approved' => 'Approved',
                    ]),

                Tables\Filters\SelectFilter::make('collateral_status')
                    ->label('Collateral')
                    ->options([
                        'Pending Verification' => 'Pending Verification',
                        'Approved' => 'Approved',
                        'Rejected' => 'Rejected',
                    ]),
            ])
            ->defaultSort('created_at', 'asc')
            ->striped()
            ->paginated([5, 10, 25])
            ->emptyStateIcon('heroicon-o-inbox')
            ->emptyStateHeading('No queued applications')
            ->emptyStateDescription('Applications requiring loan officer action will appear here.')
            ->poll('30s');
    }

    protected function priorityQueueQuery(): Builder
    {
        $query = LoanApplication::query()
            ->with(['member.profile', 'type'])
            ->where(function (Builder $pipelineQuery) {
                $pipelineQuery->whereIn('status', ['Pending', 'Under Review'])
                    ->orWhere(function (Builder $releaseReadyQuery) {
                        $releaseReadyQuery->where('status', 'Approved')
                            ->whereDoesntHave('loanAccount');
                    });
            });

        $user = Auth::user();

        if ($user?->isBranchScoped() && $user->branchId()) {
            $query->whereHas('member', fn (Builder $memberQuery) => $memberQuery->where('branch_id', $user->branchId()));
        }

        return $query;
    }

    protected static function isLoanOfficerUser(User $user): bool
    {
        return $user->hasAnyRole([
            'loan_officer',
            'Loan Officer',
            'hq_loan_officer',
            'HQ Loan Officer',
            'loan_manager',
            'Loan Manager',
            'credit_committee',
            'Credit Committee',
        ]);
    }
}
