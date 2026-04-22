<?php

namespace App\Filament\Widgets;

use App\Models\PenaltyRule;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select as ComponentsSelect;
use Filament\Forms\Components\Textarea as ComponentsTextarea;
use Filament\Forms\Components\TextInput as ComponentsTextInput;
use Filament\Forms\Components\Toggle as ComponentsToggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PenaltyRulesWidget extends BaseWidget
{
    protected static ?string $heading = 'Penalty Rules';

    protected int|string|array $columnSpan = 'full';

    public static function getDefaultForm(): array
    {
        return [
            ComponentsTextInput::make('name')
                ->label('Rule Name')
                ->placeholder('e.g. Standard Late Payment Penalty')
                ->required()
                ->maxLength(255),

            ComponentsTextarea::make('description')
                ->label('Description')
                ->rows(2),

            ComponentsSelect::make('frequency')
                ->label('Penalty Frequency')
                ->options([
                    'daily' => 'Daily (accrues every day)',
                    'monthly' => 'Monthly (accrues every 30 days)',
                ])
                ->required(),

            ComponentsSelect::make('value_type')
                ->label('Value Type')
                ->options([
                    'percentage' => 'Percentage (% of outstanding)',
                    'fixed' => 'Fixed Amount (₱)',
                ])
                ->required(),

            ComponentsTextInput::make('value')
                ->label(fn (Get $get) => $get('value_type') === 'percentage'
                    ? 'Rate (%)'
                    : 'Fixed Amount (₱)')
                ->numeric()
                ->minValue(0)
                ->step(0.01)
                ->suffix(fn (Get $get) => $get('value_type') === 'percentage' ? '%' : null)
                ->prefix(fn (Get $get) => $get('value_type') === 'fixed' ? '₱' : null)
                ->required(),

            ComponentsTextInput::make('grace_period_days')
                ->label('Grace Period (days)')
                ->numeric()
                ->minValue(0)
                ->default(0)
                ->helperText('Number of days before penalty starts accruing.'),

            ComponentsTextInput::make('max_penalty_cap')
                ->label('Maximum Penalty Cap (₱)')
                ->numeric()
                ->prefix('₱')
                ->minValue(0)
                ->placeholder('Leave blank for no cap')
                ->helperText('Optional. Penalty will never exceed this amount.'),

            Section::make('Escalation Settings')
                ->description('Gradually increase the penalty rate the longer the loan is overdue.')
                ->schema([
                    ComponentsToggle::make('is_escalating')
                        ->label('Enable Escalation')
                        ->default(false),

                    ComponentsTextInput::make('escalation_interval')
                        ->label('Escalate Every (days)')
                        ->numeric()
                        ->minValue(1)
                        ->placeholder('e.g. 30')
                        ->helperText('Rate increases after every N days overdue.')
                        ->visible(fn (Get $get) => $get('is_escalating'))
                        ->required(fn (Get $get) => $get('is_escalating')),

                    ComponentsTextInput::make('escalation_increment')
                        ->label('Increment per Interval')
                        ->numeric()
                        ->minValue(0)
                        ->step(0.01)
                        ->placeholder('e.g. 0.5')
                        ->helperText('How much the rate increases each interval (% or ₱).')
                        ->visible(fn (Get $get) => $get('is_escalating'))
                        ->required(fn (Get $get) => $get('is_escalating')),

                    ComponentsTextInput::make('escalation_max_value')
                        ->label('Maximum Rate / Amount')
                        ->numeric()
                        ->minValue(0)
                        ->step(0.01)
                        ->placeholder('Leave blank for no ceiling')
                        ->helperText('The escalated rate will never exceed this value.')
                        ->visible(fn (Get $get) => $get('is_escalating')),
                ]),

            ComponentsSelect::make('status')
                ->label('Status')
                ->options([
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                ])
                ->default('active')
                ->required(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(PenaltyRule::query())
            ->columns([
                TextColumn::make('name')
                    ->label('Rule Name')
                    ->searchable()
                    ->limit(30)
                    ->toggleable()
                    ->sortable(),

                BadgeColumn::make('frequency')
                    ->colors([
                        'info' => 'daily',
                        'warning' => 'monthly',
                    ]),

                BadgeColumn::make('value_type')
                    ->label('Type')
                    ->colors([
                        'success' => 'percentage',
                        'primary' => 'fixed',
                    ]),

                TextColumn::make('value')
                    ->label('Rate / Amount')
                    ->getStateUsing(function (PenaltyRule $record): string {
                        return $record->value_type === 'percentage'
                            ? number_format($record->value, 2).'%'
                            : '₱'.number_format($record->value, 2);
                    })
                    ->sortable(),

                TextColumn::make('grace_period_days')
                    ->label('Grace Period')
                    ->suffix(' days')
                    ->sortable(),

                TextColumn::make('max_penalty_cap')
                    ->label('Cap')
                    ->getStateUsing(function (PenaltyRule $record): string {
                        return $record->max_penalty_cap
                            ? '₱'.number_format($record->max_penalty_cap, 2)
                            : 'None';
                    }),

                IconColumn::make('is_escalating')
                    ->label('Escalating')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('status')
                    ->icon(fn (string $state): string => match ($state) {
                        'active' => 'heroicon-o-check-circle',
                        'inactive' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make()
                    ->form(static::getDefaultForm()),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->model(PenaltyRule::class)
                    ->form(static::getDefaultForm()),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s'); // Auto-refresh every 30 seconds
    }
}
