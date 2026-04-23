<?php

namespace App\Filament\Widgets;

use App\Models\ShareCapitalTransaction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;

class ShareCapitalTransactionCrudWidget extends BaseWidget
{
    protected static ?string $heading = 'Share Capital Transactions';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(ShareCapitalTransaction::query()->latest())
            ->columns([
                TextColumn::make('transaction_date')->label('Date')->date()->sortable(),
                TextColumn::make('profile_id')->label('Profile ID')->sortable(),
                TextColumn::make('amount')->label('Amount')->money('PHP')->sortable(),
                BadgeColumn::make('direction')->colors(['success' => 'credit', 'danger' => 'debit'])->sortable(),
                BadgeColumn::make('type')->sortable(),
                TextColumn::make('reference_no')->label('Reference'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->form([
                        Forms\Components\TextInput::make('profile_id')->label('Profile ID')->numeric()->required(),
                        Forms\Components\TextInput::make('amount')->label('Amount')->numeric()->required(),
                        Forms\Components\Select::make('direction')->options(['credit' => 'Credit', 'debit' => 'Debit'])->required(),
                        Forms\Components\Select::make('type')->options(['deposit' => 'Deposit', 'withdraw' => 'Withdraw', 'adjustment' => 'Adjustment'])->required(),
                        Forms\Components\DatePicker::make('transaction_date')->label('Transaction Date')->required(),
                        Forms\Components\TextInput::make('reference_no')->label('Reference Number')->maxLength(100),
                        Forms\Components\Textarea::make('notes')->maxLength(500),
                    ]),
            ])
            ->actions([
                EditAction::make()->form([
                    Forms\Components\TextInput::make('profile_id')->label('Profile ID')->numeric()->required(),
                    Forms\Components\TextInput::make('amount')->label('Amount')->numeric()->required(),
                    Forms\Components\Select::make('direction')->options(['credit' => 'Credit', 'debit' => 'Debit'])->required(),
                    Forms\Components\Select::make('type')->options(['deposit' => 'Deposit', 'withdraw' => 'Withdraw', 'adjustment' => 'Adjustment'])->required(),
                    Forms\Components\DatePicker::make('transaction_date')->label('Transaction Date')->required(),
                    Forms\Components\TextInput::make('reference_no')->label('Reference Number')->maxLength(100),
                    Forms\Components\Textarea::make('notes')->maxLength(500),
                ]),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('transaction_date', 'desc')
            ->paginated([10, 25, 50]);
    }
}
