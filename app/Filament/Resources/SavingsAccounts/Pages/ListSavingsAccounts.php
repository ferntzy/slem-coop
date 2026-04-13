<?php

namespace App\Filament\Resources\SavingsAccounts\Pages;

use App\Filament\Resources\SavingsAccounts\SavingsAccountResource;
use App\Models\MemberDetail;
use App\Models\SavingsAccount;
use App\Models\SavingsAccountTransaction;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ListSavingsAccounts extends ListRecords
{
    protected static string $resource = SavingsAccountResource::class;

    public function getTitle(): string | Htmlable
    {
        $user = Filament::auth()->user();

        if ($user?->isMember()) {
            return 'My Savings';
        }

        return parent::getTitle();
    }

    public function getView(): string
    {
        $user = Filament::auth()->user();

        if ($user?->isMember()) {
            return 'filament.resources.savings.pages.savings';
        }

        return parent::getView();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $user = Filament::auth()->user();

        if (! $user?->isMember()) {
            return parent::getViewData();
        }

        $memberDetail = MemberDetail::query()
            ->with(['branch', 'membershipType'])
            ->where('profile_id', $user->profile_id)
            ->first();

        $accounts = SavingsAccount::query()
            ->whereHas('member', fn (Builder $memberQuery): Builder => $memberQuery->where('profile_id', $user->profile_id))
            ->with('savingsType')
            ->orderByDesc('id')
            ->get();

        $accountIds = $accounts->pluck('id');

        /** @var Collection<int, SavingsAccountTransaction> $recentTransactions */
        $recentTransactions = $accountIds->isEmpty()
            ? collect()
            : SavingsAccountTransaction::query()
                ->whereIn('savings_account_id', $accountIds)
                ->with(['savingsAccount.savingsType', 'postedBy.profile'])
                ->orderByDesc('transaction_date')
                ->orderByDesc('id')
                ->limit(10)
                ->get();

        $totalBalance = $accounts->sum(fn (SavingsAccount $account): float => (float) $account->balance);

        return [
            ...parent::getViewData(),
            'memberDetail' => $memberDetail,
            'accounts' => $accounts,
            'recentTransactions' => $recentTransactions,
            'totalBalance' => $totalBalance,
        ];
    }

    protected function getHeaderActions(): array
    {
        if (Filament::auth()->user()?->isMember()) {
            return [];
        }

        return [
            CreateAction::make(),
        ];
    }
}
