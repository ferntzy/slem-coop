<?php

namespace App\Filament\Widgets;

use App\Models\LoanAccount;
use App\Services\LoanScheduleService;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class MemberLoanScheduleWidget extends Widget
{
    protected string $view = 'filament.widgets.member-loan-schedule-widget';

    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 'full';

    // Currently selected loan account id
    public ?int $selectedLoanId = null;

    public static function canView(): bool
    {
        return Auth::user()->isMember();
    }

    public function mount(): void
    {
        // Auto-select the first active loan on load
        $first = LoanAccount::where('profile_id', Auth::user()->profile_id)
            ->whereIn('status', ['Active', 'Restructured'])
            ->orderBy('loan_account_id')
            ->first();

        $this->selectedLoanId = $first?->loan_account_id;
    }

    public function selectLoan(int $loanAccountId): void
    {
        $this->selectedLoanId = $loanAccountId;
    }

    public function getLoans(): Collection
    {
        return LoanAccount::where('profile_id', Auth::user()->profile_id)
            ->whereIn('status', ['Active', 'Restructured'])
            ->orderBy('loan_account_id')
            ->get();
    }

    public function getSchedule(): array
    {
        if (! $this->selectedLoanId) {
            return [];
        }

        $loan = LoanAccount::find($this->selectedLoanId);

        if (! $loan) {
            return [];
        }

        return app(LoanScheduleService::class)->build($loan);
    }

    public function getSelectedLoan(): ?LoanAccount
    {
        return $this->selectedLoanId
            ? LoanAccount::find($this->selectedLoanId)
            : null;
    }
}
