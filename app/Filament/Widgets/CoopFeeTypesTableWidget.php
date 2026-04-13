<?php

namespace App\Filament\Widgets;

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

class CoopFeeTypesTableWidget extends BaseWidget
{
    protected static ?string $heading = 'Manage Fee Types';

    protected int|string|array $columnSpan = 'full';

    private function typeForm(): array
    {
        return [
            Forms\Components\TextInput::make('name')
                ->label('Type Name')
                ->placeholder('e.g. Insurance')
                ->required()
                ->maxLength(100),

            Forms\Components\TextInput::make('key')
                ->label('Key (slug)')
                ->placeholder('e.g. insurance')
                ->helperText('Lowercase, underscores only. Auto-generated if left blank.')
                ->maxLength(100),

            Forms\Components\Textarea::make('description')
                ->label('Description')
                ->rows(2),

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
            ->query(CoopFeeType::query())
            ->columns([
                TextColumn::make('name')->label('Type Name')->searchable(),
                TextColumn::make('key')->label('Key')->badge()->color('gray'),
                TextColumn::make('description')->limit(40)->placeholder('—'),
                IconColumn::make('status')
                    ->icon(fn(string $state): string => match ($state) {
                        'active'   => 'heroicon-o-check-circle',
                        'inactive' => 'heroicon-o-x-circle',
                        default    => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'active'   => 'success',
                        'inactive' => 'danger',
                        default    => 'gray',
                    }),
            ])
            ->headerActions([
                CreateAction::make()
                    ->model(CoopFeeType::class)
                    ->form($this->typeForm())
                    ->mutateFormDataUsing(function (array $data): array {
                        // Auto-generate key from name if not provided
                        if (empty($data['key'])) {
                            $data['key'] = str($data['name'])->lower()->slug('_')->toString();
                        }
                        return $data;
                    }),
            ])
            ->actions([
                ActionsEditAction::make()
                    ->form($this->typeForm())
                    ->mutateFormDataUsing(function (array $data): array {
                        if (empty($data['key'])) {
                            $data['key'] = str($data['name'])->lower()->slug('_')->toString();
                        }
                        return $data;
                    }),
                ActionsDeleteAction::make(),
            ])
            ->bulkActions([
                ActionsDeleteBulkAction::make(),
            ]);
    }
}
