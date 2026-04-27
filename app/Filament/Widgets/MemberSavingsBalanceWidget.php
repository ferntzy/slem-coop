<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\MemberDetails\MemberDetailResource;
use App\Models\SavingsAccountTransaction;
use App\Services\MemberSavingsBalanceService;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class MemberSavingsBalanceWidget extends Widget
{
    protected string $view = 'filament.widgets.member-savings-balance-widget';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::user()?->isMember() ?? false;
    }

    public function getFormattedBalance(): string
    {
        return '₱'.number_format($this->getTotalBalance(), 2);
    }

    public function getSavingsAccountCount(): int
    {
        $user = Auth::user();

        if (! $user?->profile_id) {
            return 0;
        }

        return SavingsAccountTransaction::query()
            ->where('profile_id', $user->profile_id)
            ->distinct('savings_type_id')
            ->count('savings_type_id');
    }

    public function getSavingsAccountLabel(): string
    {
        $accountCount = $this->getSavingsAccountCount();

        return $accountCount === 1
            ? '1 savings account'
            : number_format($accountCount).' savings accounts';
    }

    public function getSavingsPageUrl(): string
    {
        $profile = Auth::user()?->profile;
        $memberDetail = $profile?->memberDetail;

        if ($memberDetail) {
            return MemberDetailResource::getUrl('view', ['record' => $memberDetail]);
        }

        return MemberDetailResource::getUrl('index');
    }

    protected function getTotalBalance(): float
    {
        $user = Auth::user();

        if (! $user?->profile_id) {
            return 0.0;
        }

        return app(MemberSavingsBalanceService::class)
            ->getRegularSavingsBalance((int) $user->profile_id);
    }
}
