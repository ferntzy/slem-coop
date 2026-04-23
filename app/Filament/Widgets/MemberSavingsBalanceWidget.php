<?php

namespace App\Filament\Widgets;

use App\Models\SavingsAccount;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
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

        return SavingsAccount::query()
            ->whereHas('member', fn (Builder $memberQuery): Builder => $memberQuery->where('profile_id', $user->profile_id))
            ->count();
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
        return route('filament.admin.resources.savings-accounts.index');
    }

    protected function getTotalBalance(): float
    {
        $user = Auth::user();

        if (! $user?->profile_id) {
            return 0.0;
        }

        return SavingsAccount::query()
            ->whereHas('member', fn (Builder $memberQuery): Builder => $memberQuery->where('profile_id', $user->profile_id))
            ->get()
            ->sum(fn (SavingsAccount $account): float => (float) $account->balance);
    }
}
