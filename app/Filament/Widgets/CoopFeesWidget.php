<?php

namespace App\Filament\Widgets;

use App\Models\CoopFee;
use App\Models\CoopFeeType;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CoopFeesWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $stats = [];

        // Dynamically build a stat for each active fee type
        $types = CoopFeeType::where('status', 'active')->get();

        foreach ($types as $type) {
            $activeFees = CoopFee::where('coop_fee_type_id', $type->id)
                ->where('status', 'active')
                ->get();

            if ($activeFees->isEmpty()) {
                $value = '—';
            } elseif ($activeFees->count() === 1) {
                $fee = $activeFees->first();
                $value = $fee->is_percentage
                    ? number_format($fee->percentage, 2).'%'
                    : '₱'.number_format($fee->amount, 2);
            } else {
                // Multiple fees of same type — show count + breakdown
                $value = $activeFees->count().' fees';
            }

            $stats[] = Stat::make($type->name, $value)
                ->description($type->description ?? 'Active rate')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary');
        }

        // Total active fees count
        $stats[] = Stat::make('Total Active Fees', CoopFee::where('status', 'active')->count())
            ->description('Across all fee types')
            ->descriptionIcon('heroicon-m-check-circle')
            ->color('success');

        return $stats;
    }
}
