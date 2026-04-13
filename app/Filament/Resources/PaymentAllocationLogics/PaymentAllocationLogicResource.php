<?php

namespace App\Filament\Resources\PaymentAllocationLogics;

use App\Filament\Resources\PaymentAllocationLogics\Pages\ManagePaymentAllocationLogic;
use App\Filament\Resources\PaymentAllocationLogics\Schemas\PaymentAllocationLogicForm;
use App\Models\PaymentAllocationSetting;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PaymentAllocationLogicResource extends Resource
{
    protected static ?string $model = PaymentAllocationSetting::class;
    protected static bool $shouldRegisterNavigation = false;    // protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static string|\UnitEnum|null $navigationGroup = 'For Deletion';

    protected static ?int $navigationSort = 9999;

    protected static ?string $navigationLabel = 'Payment Allocation Logic';

    protected static ?string $breadcrumb = 'Payment Allocation Logic';

    public static function form(Schema $schema): Schema
    {
        return PaymentAllocationLogicForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePaymentAllocationLogic::route('/'),
        ];
    }
}
