<?php

namespace App\Filament\Widgets;

use App\Models\CoopFee;
use App\Models\CoopFeeType;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction as ActionsEditAction;
use Filament\Actions\DeleteAction as ActionsDeleteAction;
use Filament\Actions\DeleteBulkAction as ActionsDeleteBulkAction;
use Filament\Forms;
use Filament\Schemas\Components\Utilities\Get;

class CoopFeesTableWidget extends BaseWidget
{
    protected static ?string $heading = 'Manage Coop Fees';

    protected int|string|array $columnSpan = 'full';

    private function feeForm(): array
    {
        return [
            Forms\Components\Select::make('coop_fee_type_id')
                ->label('Applies To')
                ->options(
                    CoopFeeType::where('status', 'active')->pluck('name', 'id')
                )
                ->required()
                ->searchable()
                ->helperText('Choose where this fee applies: Loan Application, Restructure, or Reloan.'),

            Forms\Components\Select::make('type')
                ->label('Fee Kind')
                ->options([
                    CoopFee::TYPE_SHARED_CAPITAL => 'Shared Capital',
                    CoopFee::TYPE_INSURANCE => 'Insurance',
                    CoopFee::TYPE_PROCESSING_FEE => 'Processing Fee',
                ])
                ->required()
                ->helperText('This is the actual fee kind used by the calculator.'),

            Forms\Components\Toggle::make('is_percentage')
                ->label('Percentage-based?')
                ->default(false)
                ->live(),

            Forms\Components\TextInput::make('percentage')
                ->label('Percentage (%)')
                ->numeric()
                ->suffix('%')
                ->minValue(0)
                ->maxValue(100)
                ->visible(fn (Get $get) => $get('is_percentage'))
                ->required(fn (Get $get) => $get('is_percentage')),

            Forms\Components\TextInput::make('amount')
                ->label('Fixed Amount (₱)')
                ->numeric()
                ->prefix('₱')
                ->minValue(0)
                ->visible(fn (Get $get) => ! $get('is_percentage'))
                ->required(fn (Get $get) => ! $get('is_percentage')),

            Forms\Components\TextInput::make('name')
                ->label('Label / Name')
                ->placeholder('e.g. Insurance – Life Cover')
                ->helperText('Optional custom label to distinguish fees.')
                ->maxLength(255),

            Forms\Components\Textarea::make('description')
                ->label('Description')
                ->rows(3),

            Forms\Components\Select::make('status')
                ->label('Status')
                ->options([
                    'active'   => 'Active',
                    'inactive' => 'Inactive',
                ])
                ->default('active')
                ->required(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(CoopFee::query()->with('feeType'))
            ->columns([
                TextColumn::make('feeType.name')
                    ->label('Applies To')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('type_label')
                    ->label('Fee Kind')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('name')
                    ->label('Name')
                    ->placeholder('—')
                    ->limit(30),

                TextColumn::make('value')
                    ->label('Value')
                    ->getStateUsing(
                        fn (CoopFee $record) =>
                        $record->is_percentage
                            ? number_format((float) $record->percentage, 2) . '%'
                            : '₱' . number_format((float) $record->amount, 2)
                    ),

                TextColumn::make('description')
                    ->limit(40)
                    ->placeholder('—'),

                IconColumn::make('status')
                    ->icon(fn (string $state): string => match ($state) {
                        'active'   => 'heroicon-o-check-circle',
                        'inactive' => 'heroicon-o-x-circle',
                        default    => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'active'   => 'success',
                        'inactive' => 'danger',
                        default    => 'gray',
                    }),
            ])
            ->headerActions([
                CreateAction::make()
                    ->model(CoopFee::class)
                    ->form($this->feeForm()),
            ])
            ->actions([
                ActionsEditAction::make()
                    ->form($this->feeForm()),
                ActionsDeleteAction::make(),
            ])
            ->bulkActions([
                ActionsDeleteBulkAction::make(),
            ]);
    }
}