<?php

namespace App\Filament\Resources\LoanApplications\RelationManagers;

use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;

class CashflowsRelationManager extends RelationManager
{
    protected static string $relationship = 'cashflows';

    protected static ?string $title = 'Cash Flow';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Monthly Income')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('salary')
                            ->label('Salary / Wages')
                            ->numeric()
                            ->default(0)
                            ->prefix('₱')
                            ->live(),

                        TextInput::make('business_income')
                            ->label('Business Income')
                            ->numeric()
                            ->default(0)
                            ->prefix('₱')
                            ->live(),

                        TextInput::make('remittances')
                            ->label('Remittances')
                            ->numeric()
                            ->default(0)
                            ->prefix('₱')
                            ->live(),

                        TextInput::make('other_income')
                            ->label('Other Income')
                            ->numeric()
                            ->default(0)
                            ->prefix('₱')
                            ->live(),
                    ]),
                ]),

            Section::make('Monthly Expenses')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('living_expenses')
                            ->label('Living Expenses')
                            ->numeric()
                            ->default(0)
                            ->prefix('₱')
                            ->required()
                            ->live(),

                        TextInput::make('business_expenses')
                            ->label('Business Expenses')
                            ->numeric()
                            ->default(0)
                            ->prefix('₱')
                            ->live(),

                        TextInput::make('existing_loan_payments')
                            ->label('Existing Loan Payments')
                            ->numeric()
                            ->default(0)
                            ->prefix('₱')
                            ->live(),

                        TextInput::make('other_expenses')
                            ->label('Other Expenses')
                            ->numeric()
                            ->default(0)
                            ->prefix('₱')
                            ->live(),
                    ]),
                ]),

            Section::make('Cash Flow Analysis')
                ->schema([
                    Grid::make(2)->schema([
                        Placeholder::make('total_income')
                            ->label('Total Income')
                            ->content(function (callable $get) {
                                $total =
                                    (float) ($get('salary') ?? 0) +
                                    (float) ($get('business_income') ?? 0) +
                                    (float) ($get('remittances') ?? 0) +
                                    (float) ($get('other_income') ?? 0);

                                return '₱' . number_format($total, 2);
                            }),

                        Placeholder::make('total_expenses')
                            ->label('Total Expenses')
                            ->content(function (callable $get) {
                                $total =
                                    (float) ($get('living_expenses') ?? 0) +
                                    (float) ($get('business_expenses') ?? 0) +
                                    (float) ($get('existing_loan_payments') ?? 0) +
                                    (float) ($get('other_expenses') ?? 0);

                                return '₱' . number_format($total, 2);
                            }),

                        Placeholder::make('net_cash_flow')
                            ->label('Net Cash Flow')
                            ->content(function (callable $get) {
                                $income =
                                    (float) ($get('salary') ?? 0) +
                                    (float) ($get('business_income') ?? 0) +
                                    (float) ($get('remittances') ?? 0) +
                                    (float) ($get('other_income') ?? 0);

                                $expenses =
                                    (float) ($get('living_expenses') ?? 0) +
                                    (float) ($get('business_expenses') ?? 0) +
                                    (float) ($get('existing_loan_payments') ?? 0) +
                                    (float) ($get('other_expenses') ?? 0);

                                return '₱' . number_format($income - $expenses, 2);
                            }),

                        Placeholder::make('allowed_payment')
                            ->label('Allowed Payment (40%)')
                            ->content(function (callable $get) {
                                $income =
                                    (float) ($get('salary') ?? 0) +
                                    (float) ($get('business_income') ?? 0) +
                                    (float) ($get('remittances') ?? 0) +
                                    (float) ($get('other_income') ?? 0);

                                $expenses =
                                    (float) ($get('living_expenses') ?? 0) +
                                    (float) ($get('business_expenses') ?? 0) +
                                    (float) ($get('existing_loan_payments') ?? 0) +
                                    (float) ($get('other_expenses') ?? 0);

                                $net = $income - $expenses;

                                return '₱' . number_format($net * 0.40, 2);
                            }),
                    ]),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->label('Category')
                    ->weight('medium'),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('PHP')
                    ->alignEnd()
                    ->weight('bold'),
            ])
            ->groups([
                Group::make('row_type')
                    ->label('Type')
                    ->getTitleFromRecordUsing(fn ($record) => ucfirst($record->row_type)),
            ])
            ->defaultGroup('row_type')
            ->headerActions([
                Action::make('addCashFlow')
                    ->label('Edit')
                    ->mountUsing(function ($form) {
                        $record = $this->getOwnerRecord();

                        $form->fill([
                            'salary' => $this->getAmount($record, 'salary', 'income'),
                            'business_income' => $this->getAmount($record, 'business_income', 'income'),
                            'remittances' => $this->getAmount($record, 'remittances', 'income'),
                            'other_income' => $this->getAmount($record, 'other_income', 'income'),
                            'living_expenses' => $this->getAmount($record, 'living_expenses', 'expense'),
                            'business_expenses' => $this->getAmount($record, 'business_expenses', 'expense'),
                            'existing_loan_payments' => $this->getAmount($record, 'existing_loan_payments', 'expense'),
                            'other_expenses' => $this->getAmount($record, 'other_expenses', 'expense'),
                        ]);
                    })
                    ->form(fn (Schema $schema) => $this->form($schema))
                    ->action(function (array $data) {
                        $record = $this->getOwnerRecord();

                        $record->cashflows()->delete();

                        $rows = [
                            ['label' => 'Salary / Wages', 'row_type' => 'income', 'category' => 'salary', 'amount' => $data['salary'] ?? 0],
                            ['label' => 'Business Income', 'row_type' => 'income', 'category' => 'business_income', 'amount' => $data['business_income'] ?? 0],
                            ['label' => 'Remittances', 'row_type' => 'income', 'category' => 'remittances', 'amount' => $data['remittances'] ?? 0],
                            ['label' => 'Other Income', 'row_type' => 'income', 'category' => 'other_income', 'amount' => $data['other_income'] ?? 0],
                            ['label' => 'Living Expenses', 'row_type' => 'expense', 'category' => 'living_expenses', 'amount' => $data['living_expenses'] ?? 0],
                            ['label' => 'Business Expenses', 'row_type' => 'expense', 'category' => 'business_expenses', 'amount' => $data['business_expenses'] ?? 0],
                            ['label' => 'Existing Loan Payments', 'row_type' => 'expense', 'category' => 'existing_loan_payments', 'amount' => $data['existing_loan_payments'] ?? 0],
                            ['label' => 'Other Expenses', 'row_type' => 'expense', 'category' => 'other_expenses', 'amount' => $data['other_expenses'] ?? 0],
                        ];

                        foreach ($rows as $row) {
                            if ((float) $row['amount'] > 0) {
                                $record->cashflows()->create([
                                    'label' => $row['label'],
                                    'row_type' => $row['row_type'],
                                    'category' => $row['category'],
                                    'amount' => $row['amount'],
                                    'notes' => null,
                                ]);
                            }
                        }

                        Notification::make()
                            ->title('Cash flow saved successfully.')
                            ->success()
                            ->send();
                    })
                    ->modalWidth('5xl'),
            ])
            ->actions([
                // Action::make('editFullCashFlow')
                //     ->label('Edit')
                //     ->icon('heroicon-o-pencil-square')
                //     ->mountUsing(function ($form) {x
                //         $record = $this->getOwnerRecord();

                //         $form->fill([
                //             'salary' => $this->getAmount($record, 'salary', 'income'),
                //             'business_income' => $this->getAmount($record, 'business_income', 'income'),
                //             'remittances' => $this->getAmount($record, 'remittances', 'income'),
                //             'other_income' => $this->getAmount($record, 'other_income', 'income'),
                //             'living_expenses' => $this->getAmount($record, 'living_expenses', 'expense'),
                //             'business_expenses' => $this->getAmount($record, 'business_expenses', 'expense'),
                //             'existing_loan_payments' => $this->getAmount($record, 'existing_loan_payments', 'expense'),
                //             'other_expenses' => $this->getAmount($record, 'other_expenses', 'expense'),
                //         ]);
                //     })
                //     ->form(fn (Schema $schema) => $this->form($schema))
                //     ->action(function (array $data) {
                //         $record = $this->getOwnerRecord();

                //         $record->cashflows()->delete();

                //         $rows = [
                //             ['label' => 'Salary / Wages', 'row_type' => 'income', 'category' => 'salary', 'amount' => $data['salary'] ?? 0],
                //             ['label' => 'Business Income', 'row_type' => 'income', 'category' => 'business_income', 'amount' => $data['business_income'] ?? 0],
                //             ['label' => 'Remittances', 'row_type' => 'income', 'category' => 'remittances', 'amount' => $data['remittances'] ?? 0],
                //             ['label' => 'Other Income', 'row_type' => 'income', 'category' => 'other_income', 'amount' => $data['other_income'] ?? 0],
                //             ['label' => 'Living Expenses', 'row_type' => 'expense', 'category' => 'living_expenses', 'amount' => $data['living_expenses'] ?? 0],
                //             ['label' => 'Business Expenses', 'row_type' => 'expense', 'category' => 'business_expenses', 'amount' => $data['business_expenses'] ?? 0],
                //             ['label' => 'Existing Loan Payments', 'row_type' => 'expense', 'category' => 'existing_loan_payments', 'amount' => $data['existing_loan_payments'] ?? 0],
                //             ['label' => 'Other Expenses', 'row_type' => 'expense', 'category' => 'other_expenses', 'amount' => $data['other_expenses'] ?? 0],
                //         ];

                //         foreach ($rows as $row) {
                //             if ((float) $row['amount'] > 0) {
                //                 $record->cashflows()->create([
                //                     'label' => $row['label'],
                //                     'row_type' => $row['row_type'],
                //                     'category' => $row['category'],
                //                     'amount' => $row['amount'],
                //                     'notes' => null,
                //                 ]);
                //             }
                //         }

                //         Notification::make()
                //             ->title('Cash flow updated successfully.')
                //             ->success()
                //             ->send();
                //     })
                //     ->modalWidth('5xl'),

                DeleteAction::make()
                    ->label('Clear'),
            ])
            ->bulkActions([]);
    }

    protected function getAmount($record, string $category, string $rowType): float
    {
        return (float) ($record->cashflows()
            ->where('category', $category)
            ->where('row_type', $rowType)
            ->value('amount') ?? 0);
    }
}
