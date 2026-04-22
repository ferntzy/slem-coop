<?php

namespace App\Filament\Widgets;

use App\Models\DailyCollectionEntry;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class CollectionsTodayWidget extends BaseWidget
{
    protected static ?string $heading = "Today's Collection Summary";

    protected static ?int $sort = 7;

    public static function canView(): bool
    {
        return ! Auth::user()->isMember();
    }

    public function getColumnSpan(): int|string|array
    {
        return 'full';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                DailyCollectionEntry::query()
                    ->with(['aoUser.profile'])
                    ->whereDate('collection_date', today())
                    ->latest()
            )
            ->columns([
                TextColumn::make('aoUser.profile.first_name')
                    ->label('Account Officer')
                    ->formatStateUsing(fn ($record) => $record->aoUser?->profile?->first_name.' '.
                        $record->aoUser?->profile?->last_name
                    )
                    ->searchable(query: fn ($query, $search) => $query->whereHas('aoUser.profile', fn ($q) => $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                    )
                    )
                    ->icon('heroicon-o-user'),

                TextColumn::make('collection_date')
                    ->label('Date')
                    ->date('M d, Y')
                    ->sortable(),

                TextColumn::make('transaction_count')
                    ->label('Transactions')
                    ->alignCenter()
                    ->badge()
                    ->color('info'),

                TextColumn::make('system_total')
                    ->label('System Total')
                    ->money('PHP')
                    ->sortable(),

                TextColumn::make('cash_on_hand')
                    ->label('Cash on Hand')
                    ->money('PHP')
                    ->sortable(),

                TextColumn::make('variance')
                    ->label('Variance')
                    ->money('PHP')
                    ->color(fn ($state): string => $state == 0 ? 'success' : ($state > 0 ? 'warning' : 'danger'))
                    ->weight('bold'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Verified' => 'success',
                        'Submitted' => 'info',
                        default => 'warning',
                    }),

                TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->dateTime('M d H:i')
                    ->placeholder('—'),
            ])
            ->defaultSort('collection_date', 'desc')
            ->striped()
            ->paginated([5, 10, 25])
            ->emptyStateIcon('heroicon-o-inbox')
            ->emptyStateHeading('No collections today')
            ->emptyStateDescription('Collection entries will appear here once submitted.')
            ->poll('60s');
    }
}
