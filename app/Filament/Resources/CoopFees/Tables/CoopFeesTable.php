<?php

namespace App\Filament\Resources\CoopFeeResource\Tables;

use App\Models\CoopFee;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;

class CoopFeesTable
{
    public static function configure(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                BadgeColumn::make('type')
                    ->colors([
                        'warning' => 'shared_capital',
                        'success' => 'insurance',
                        'info' => 'processing_fee',
                    ]),

                TextColumn::make('percentage')
                    ->label('Value')
                    ->badge()
                    ->color(fn (CoopFee $record): string => match ($record->type) {
                        'shared_capital' => 'warning',
                        'insurance' => 'success',
                        'processing_fee' => 'info',
                        default => 'gray',
                    })
                    ->getStateUsing(fn (CoopFee $record) =>
                        $record->is_percentage
                            ? $record->percentage . '%'
                            : '₱' . number_format($record->amount, 2)
                    ),

                TextColumn::make('description')
                    ->limit(40)
                    ->toggleable(),

                IconColumn::make('status')
                    ->boolean()
                    ->trueIcon('heroicon-m-check-circle')
                    ->falseIcon('heroicon-m-x-circle'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'shared_capital' => 'Shared Capital',
                        'insurance' => 'Insurance',
                        'processing_fee' => 'Processing Fee',
                    ]),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
