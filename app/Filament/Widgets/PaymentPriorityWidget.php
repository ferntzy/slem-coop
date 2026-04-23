<?php

namespace App\Filament\Widgets;

use App\Models\PaymentAllocationConfig;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class PaymentPriorityWidget extends TableWidget
{
    // Make the widget take up the full width of the dashboard
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => PaymentAllocationConfig::query())
            ->heading('Payment Allocation Priority')
            ->description('Drag and drop rows to set the order of payment (top to bottom).')

            // 1. Enable the drag-and-drop reordering
            ->reorderable('sort_order')
            ->defaultSort('sort_order', 'asc')

            // 2. Define the columns
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Priority')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Fee Name')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('column_name')
                    ->label('key')
                    ->fontFamily('mono')
                    ->color('gray'),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean() // Automatically uses check for true, x for false
                    ->trueIcon('heroicon-o-check-circle') // The circular checkmark
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success') // Green
                    ->falseColor('danger'), // Red
            ])

            ->actions([
                EditAction::make()
                    ->form([
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('column_name')
                            ->required()
                            ->helperText('This must match your loan_applications table column.'),
                        Toggle::make('is_active')
                            ->label('Active in Waterfall'),
                    ]),
            ])

            // 4. Add a Create button in the header if you need to add new fee types
            ->headerActions([
                CreateAction::make()
                    ->label('Add New Fee Type')
                    ->form([
                        TextInput::make('name')->required(),
                        TextInput::make('column_name')->required(),
                        Toggle::make('is_active')->default(true),
                    ]),
            ]);
    }
}
