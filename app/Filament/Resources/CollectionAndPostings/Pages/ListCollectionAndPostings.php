<?php

namespace App\Filament\Resources\CollectionAndPostings\Pages;

use App\Filament\Resources\CollectionAndPostings\CollectionAndPostingResource;
use App\Models\CollectionAndPosting;
use App\Models\LoanAccount;
use App\Models\MemberDetail;
use App\Services\LoanAccountBalanceService;
use App\Services\LoanScheduleService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;

class ListCollectionAndPostings extends ListRecords
{
    protected static string $resource = CollectionAndPostingResource::class;

    protected function isMemberUser(): bool
    {
        return Auth::user()?->hasRole('member') ?? false;
    }

    protected function getLoggedInMemberId(): ?int
    {
        $profileId = Auth::user()?->profile_id;

        if (! $profileId) {
            return null;
        }

        return MemberDetail::where('profile_id', $profileId)->value('id');
    }

    protected function getLoggedInMemberLoanIds()
    {
        $memberId = $this->getLoggedInMemberId();

        if (! $memberId) {
            return collect();
        }

        return LoanAccount::whereHas('loanApplication', function ($q) use ($memberId) {
            $q->where('member_id', $memberId);
        })->pluck('loan_account_id');
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        if ($this->isMemberUser()) {
            $loanIds = $this->getLoggedInMemberLoanIds();
            $query->whereIn('loan_account_id', $loanIds);
        }

        return $query;
    }

    protected function getHeaderActions(): array
    {
        return [

            Action::make('recordPayment')
                ->label('Record Payment')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->modalHeading('Record Cash Payment')
                ->modalDescription('Search for a member, select their loan, then enter payment details.')
                ->modalWidth('3xl')
                ->modalSubmitActionLabel(fn (): string => $this->isMemberUser() ? 'Submit for Approval' : 'Post Payment')
                ->form([
                    Wizard::make([

                        Step::make('Member & Loan')
                            ->icon('heroicon-o-user')
                            ->schema([

                                Placeholder::make('step1_desc')
                                    ->label('')
                                    ->content(new HtmlString('
                                        <div style="padding:.7rem 1rem;background:#f0f9ff;border-left:3px solid #0ea5e9;border-radius:0 .5rem .5rem 0;">
                                            <div style="font-size:.82rem;font-weight:700;color:#0c4a6e;">Step 1 of 2 — Member &amp; Loan Selection</div>
                                            <div style="font-size:.75rem;color:#64748b;margin-top:2px;">Search for a member by name or number, then select their active loan from the table below.</div>
                                        </div>
                                    '))
                                    ->columnSpanFull(),

                                Select::make('member_id')
                                    ->label('Search Member')
                                    ->placeholder('Type member name or number...')
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->default(fn () => $this->isMemberUser()
                                        ? $this->getLoggedInMemberId()
                                        : null)
                                    ->disabled(fn () => $this->isMemberUser())
                                    ->dehydrated()
                                    ->afterStateUpdated(fn (callable $set) => $set('loan_account_id', null))
                                    ->getSearchResultsUsing(function (string $search) {
                                        if ($this->isMemberUser()) {
                                            $member = MemberDetail::with('profile')
                                                ->where('profile_id', Auth::user()->profile_id)
                                                ->first();

                                            if (! $member) {
                                                return [];
                                            }

                                            return [
                                                $member->id => ($member->member_no ? "[{$member->member_no}] " : '')
                                                    .trim(($member->profile?->first_name ?? '').' '.($member->profile?->last_name ?? '')),
                                            ];
                                        }

                                        return MemberDetail::with('profile')
                                            ->where(function ($q) use ($search) {
                                                $q->whereHas('profile', fn ($pq) => $pq
                                                    ->where('first_name', 'like', "%{$search}%")
                                                    ->orWhere('last_name', 'like', "%{$search}%")
                                                    ->orWhereRaw("CONCAT(first_name,' ',last_name) like ?", ["%{$search}%"])
                                                )->orWhere('member_no', 'like', "%{$search}%");
                                            })
                                            ->limit(20)->get()
                                            ->mapWithKeys(fn ($m) => [
                                                $m->id => ($m->member_no ? "[{$m->member_no}] " : '')
                                                    .trim(($m->profile?->first_name ?? '').' '.($m->profile?->last_name ?? '')),
                                            ]);
                                    })
                                    ->getOptionLabelUsing(function ($value) {
                                        $m = MemberDetail::with('profile')->find($value);

                                        if (! $m) {
                                            return $value;
                                        }

                                        return ($m->member_no ? "[{$m->member_no}] " : '')
                                            .trim(($m->profile?->first_name ?? '').' '.($m->profile?->last_name ?? ''));
                                    })
                                    ->columnSpanFull(),

                                Placeholder::make('loan_table')
                                    ->label('Loans')
                                    ->content(function (Get $get) {
                                        $memberId = $get('member_id');
                                        $selectedId = $get('loan_account_id');

                                        if (! $memberId) {
                                            return new HtmlString('
                                                <div style="text-align:center;padding:2rem;color:#9ca3af;font-size:.85rem;
                                                            background:#f9fafb;border-radius:.75rem;border:1px dashed #e5e7eb;">
                                                    Search and select a member above to view their loans.
                                                </div>
                                            ');
                                        }

                                        if ($this->isMemberUser()) {
                                            $allowedMemberId = $this->getLoggedInMemberId();

                                            if ((int) $memberId !== (int) $allowedMemberId) {
                                                return new HtmlString('
                                                    <div style="text-align:center;padding:2rem;color:#ef4444;font-size:.85rem;
                                                                background:#fef2f2;border-radius:.75rem;border:1px dashed #fca5a5;">
                                                        You can only view your own loan records.
                                                    </div>
                                                ');
                                            }
                                        }

                                        $loans = LoanAccount::whereHas('loanApplication',
                                            fn ($q) => $q->where('member_id', $memberId))
                                            ->whereIn('status', ['Active', 'Restructured'])
                                            ->orderByRaw("FIELD(status, 'Active', 'Restructured')")
                                            ->get();

                                        if ($loans->isEmpty()) {
                                            return new HtmlString('
                                                <div style="text-align:center;padding:2rem;color:#ef4444;font-size:.85rem;
                                                            background:#fef2f2;border-radius:.75rem;border:1px dashed #fca5a5;">
                                                    No loans found for this member.
                                                </div>
                                            ');
                                        }

                                        $rows = '';
                                        foreach ($loans as $loan) {

                                            $isRestructured = $loan->status === 'Restructured';
                                            $isSelected = $selectedId == $loan->loan_account_id;

                                            $rowBg = $isSelected
                                                ? 'background:#eff6ff;'
                                                : ($isRestructured ? 'background:#fafafa;opacity:.7;' : 'background:#fff;');

                                            $cursor = $isRestructured ? 'cursor:not-allowed;' : 'cursor:pointer;';

                                            $maturity = $loan->maturity_date
                                                ? Carbon::parse($loan->maturity_date)->format('M d, Y')
                                                : '—';

                                            $schedule = app(LoanScheduleService::class)->build($loan);
                                            $nextDueRow = collect($schedule)->first(
                                                fn ($row) => round((float) ($row['unpaid_amount'] ?? 0), 2) > 0
                                            );

                                            $currentDueAmt = $nextDueRow
                                                ? '₱'.number_format((float) $nextDueRow['unpaid_amount'], 2)
                                                : '₱'.number_format((float) $loan->balance, 2);

                                            $dueTd = "<td style='padding:.7rem .9rem;font-size:.78rem;color:#9ca3af;'>—</td>";
                                            if ($nextDueRow && ! empty($nextDueRow['due_date'])) {
                                                $parsedDue = Carbon::parse($nextDueRow['due_date']);
                                                $dueLabel = $parsedDue->format('M d, Y');
                                                if ($parsedDue->isPast()) {
                                                    $dueColor = '#ef4444';
                                                } elseif ($parsedDue->diffInDays(now()) <= 7) {
                                                    $dueColor = '#f59e0b';
                                                } else {
                                                    $dueColor = '#059669';
                                                }
                                                $dueTd = "<td style='padding:.7rem .9rem;font-size:.78rem;font-weight:600;color:{$dueColor};'>{$dueLabel}</td>";
                                            }

                                            if ($isRestructured) {
                                                $statusBadge = "
                                                    <span title='This loan was restructured and is no longer payable. Only the Active loan can be paid.'
                                                          style='font-size:.65rem;font-weight:700;padding:3px 8px;border-radius:999px;
                                                                 background:#fef3c7;color:#d97706;cursor:help;'>
                                                        Restructured
                                                    </span>
                                                ";
                                            } else {
                                                $statusBadge = "
                                                    <span style='font-size:.65rem;font-weight:700;padding:3px 8px;border-radius:999px;
                                                                 background:#ecfdf5;color:#059669;'>
                                                        Active
                                                    </span>
                                                ";
                                            }

                                            $clickJs = "
                                                (function(){
                                                    var sel = document.querySelector('[wire\\\\:id] select[id*=loan_account_id], select[id*=loan_account_id]');
                                                    if(!sel) return;
                                                    sel.value = '{$loan->loan_account_id}';
                                                    sel.dispatchEvent(new Event('change', {bubbles:true}));
                                                })();
                                            ";

                                            $rows .= "
                                                <tr style='border-bottom:1px solid #f3f4f6;{$cursor}{$rowBg}transition:background .1s;'
                                                    onclick=\"".($isRestructured ? '' : $clickJs)."\">
                                                    <td style='padding:.7rem .9rem;'>
                                                        <input type='radio' name='cp_loan_radio'
                                                            value='{$loan->loan_account_id}'
                                                            ".($isSelected ? 'checked' : '').'
                                                            '.($isRestructured ? 'disabled' : '')."
                                                            style='".($isRestructured ? 'cursor:not-allowed;opacity:.4;' : 'cursor:pointer;')."width:16px;height:16px;accent-color:#1e3a5f;'
                                                            onclick='event.stopPropagation();this.closest(\"tr\").click();'>
                                                    </td>
                                                    <td style='padding:.7rem .9rem;font-size:.78rem;font-weight:700;color:#1e3a5f;'>
                                                        LA-{$loan->loan_account_id}
                                                    </td>
                                                    <td style='padding:.7rem .9rem;font-size:.78rem;color:#111827;font-weight:600;'>
                                                        ₱".number_format($loan->balance, 2)."
                                                    </td>
                                                    <td style='padding:.7rem .9rem;font-size:.78rem;color:#374151;font-weight:600;'>
                                                        ".($isRestructured ? '<span style="color:#9ca3af;">—</span>' : $currentDueAmt)."
                                                    </td>
                                                    <td style='padding:.7rem .9rem;font-size:.78rem;color:#6b7280;'>
                                                        {$maturity}
                                                    </td>
                                                    <td style='padding:.7rem .9rem;font-size:.78rem;color:#6b7280;'>
                                                        {$loan->term_months} mos.
                                                    </td>
                                                    ".($isRestructured ? "<td style='padding:.7rem .9rem;font-size:.78rem;color:#9ca3af;'>—</td>" : $dueTd)."
                                                    <td style='padding:.7rem .9rem;'>
                                                        {$statusBadge}
                                                    </td>
                                                </tr>
                                            ";
                                        }

                                        return new HtmlString("
                                            <div style='border:1px solid #e5e7eb;border-radius:.75rem;overflow:hidden;'>
                                                <table style='width:100%;border-collapse:collapse;'>
                                                    <thead>
                                                        <tr style='background:#f9fafb;'>
                                                            <th style='padding:.5rem .9rem;width:36px;'></th>
                                                            <th style='padding:.5rem .9rem;text-align:left;font-size:.62rem;color:#6b7280;font-weight:700;text-transform:uppercase;letter-spacing:.06em;'>Loan #</th>
                                                            <th style='padding:.5rem .9rem;text-align:left;font-size:.62rem;color:#6b7280;font-weight:700;text-transform:uppercase;letter-spacing:.06em;'>Balance</th>
                                                            <th style='padding:.5rem .9rem;text-align:left;font-size:.62rem;color:#6b7280;font-weight:700;text-transform:uppercase;letter-spacing:.06em;'>Current Due</th>
                                                            <th style='padding:.5rem .9rem;text-align:left;font-size:.62rem;color:#6b7280;font-weight:700;text-transform:uppercase;letter-spacing:.06em;'>Maturity</th>
                                                            <th style='padding:.5rem .9rem;text-align:left;font-size:.62rem;color:#6b7280;font-weight:700;text-transform:uppercase;letter-spacing:.06em;'>Term</th>
                                                            <th style='padding:.5rem .9rem;text-align:left;font-size:.62rem;color:#6b7280;font-weight:700;text-transform:uppercase;letter-spacing:.06em;'>Due Date</th>
                                                            <th style='padding:.5rem .9rem;text-align:left;font-size:.62rem;color:#6b7280;font-weight:700;text-transform:uppercase;letter-spacing:.06em;'>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>{$rows}</tbody>
                                                </table>
                                            </div>
                                            <div style='margin-top:.5rem;font-size:.72rem;color:#9ca3af;'>
                                                ℹ️ Loans marked <strong style='color:#d97706;'>Restructured</strong> are shown for reference only and cannot be paid.
                                                Only <strong style='color:#059669;'>Active</strong> loans are payable.
                                            </div>
                                        ");
                                    })
                                    ->visible(fn (Get $get) => (bool) $get('member_id'))
                                    ->columnSpanFull(),

                                Select::make('loan_account_id')
                                    ->hiddenLabel()
                                    ->required()
                                    ->dehydrated()
                                    ->live()
                                    ->options(function (Get $get) {
                                        $memberId = $get('member_id');
                                        if (! $memberId) {
                                            return [];
                                        }

                                        if ($this->isMemberUser()) {
                                            $allowedMemberId = $this->getLoggedInMemberId();

                                            if ((int) $memberId !== (int) $allowedMemberId) {
                                                return [];
                                            }
                                        }

                                        return LoanAccount::whereHas('loanApplication',
                                            fn ($q) => $q->where('member_id', $memberId))
                                            ->where('status', 'Active')
                                            ->get()
                                            ->mapWithKeys(fn ($l) => [
                                                $l->loan_account_id => "LA-{$l->loan_account_id}",
                                            ]);
                                    })
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if (! $state) {
                                            return;
                                        }

                                        $set('schedule_periods_json', '[]');
                                        $set('scheduled_due_date', null);
                                        $set('amount_paid', '0.00');
                                    })
                                    ->extraAttributes([
                                        'style' => 'position:absolute;opacity:0;height:1px;width:1px;overflow:hidden;pointer-events:none;margin:0;padding:0;',
                                    ])
                                    ->columnSpanFull(),

                            ]),

                        Step::make('Payment Details')
                            ->icon('heroicon-o-banknotes')
                            ->schema([

                                Placeholder::make('step2_desc')
                                    ->label('')
                                    ->content(new HtmlString('
                                        <div style="padding:.7rem 1rem;background:#f0fdf4;border-left:3px solid #10b981;border-radius:0 .5rem .5rem 0;">
                                            <div style="font-size:.82rem;font-weight:700;color:#064e3b;">Step 2 of 2 — Payment Details</div>
                                            <div style="font-size:.75rem;color:#64748b;margin-top:2px;">Enter the amount, payment date, and upload proof. The OR number is auto-generated.</div>
                                        </div>
                                    '))
                                    ->columnSpanFull(),

                                Placeholder::make('schedule_picker')
                                    ->label('Amortization Schedule')
                                    ->content(function (Get $get) {
                                        $loanAccountId = $get('loan_account_id');

                                        if (! $loanAccountId) {
                                            return new HtmlString('
                                                <div style="padding:1rem;background:#f9fafb;border:1px dashed #e5e7eb;border-radius:.75rem;color:#6b7280;font-size:.85rem;">
                                                    Please go back to Step 1 and select a loan.
                                                </div>
                                            ');
                                        }

                                        $loan = LoanAccount::find($loanAccountId);

                                        if (! $loan) {
                                            return new HtmlString('
                                                <div style="padding:1rem;background:#fef2f2;border:1px dashed #fecaca;border-radius:.75rem;color:#991b1b;font-size:.85rem;">
                                                    Loan account not found.
                                                </div>
                                            ');
                                        }

                                        if ($loan->status === 'Restructured') {
                                            return new HtmlString('
                                                <div style="padding:1rem;background:#fef3c7;border:1px dashed #fcd34d;border-radius:.75rem;color:#92400e;font-size:.85rem;">
                                                    This loan has been restructured and is no longer payable. Please select the Active loan instead.
                                                </div>
                                            ');
                                        }

                                        if ($this->isMemberUser()) {
                                            $allowedMemberId = $this->getLoggedInMemberId();

                                            $isAllowedLoan = LoanAccount::where('loan_account_id', $loanAccountId)
                                                ->whereHas('loanApplication', fn ($q) => $q->where('member_id', $allowedMemberId))
                                                ->exists();

                                            if (! $isAllowedLoan) {
                                                return new HtmlString('
                                                    <div style="padding:1rem;background:#fef2f2;border:1px dashed #fecaca;border-radius:.75rem;color:#991b1b;font-size:.85rem;">
                                                        You can only view the schedule of your own loan account.
                                                    </div>
                                                ');
                                            }
                                        }

                                        $schedule = app(LoanScheduleService::class)->build($loan);

                                        if (empty($schedule)) {
                                            return new HtmlString('
                                                <div style="padding:1rem;background:#f9fafb;border:1px dashed #e5e7eb;border-radius:.75rem;color:#6b7280;font-size:.85rem;">
                                                    No amortization schedule available.
                                                </div>
                                            ');
                                        }

                                        $rows = '';

                                        foreach ($schedule as $row) {
                                            $period = (int) ($row['period'] ?? 0);
                                            $status = (string) ($row['status'] ?? 'Unpaid');
                                            $dueDate = e($row['due_date'] ?? '—');
                                            $scheduledAmortization = (float) ($row['scheduled_amortization'] ?? 0);
                                            $penalty = (float) ($row['penalty'] ?? 0);
                                            $totalPaid = (float) ($row['total_paid'] ?? 0);
                                            $unpaidAmount = (float) ($row['unpaid_amount'] ?? 0);
                                            $isPaid = $status === 'Paid' || $unpaidAmount <= 0;

                                            $statusStyle = match ($status) {
                                                'Paid' => 'background:#dcfce7;color:#166534;',
                                                'Partial' => 'background:#fef3c7;color:#92400e;',
                                                'Late' => 'background:#fee2e2;color:#991b1b;',
                                                'Partial / Late' => 'background:#fde68a;color:#92400e;',
                                                default => 'background:#e5e7eb;color:#374151;',
                                            };

                                            $checkboxHtml = '';

                                            if (! $isPaid) {
                                                $checkboxHtml = "
                                                    <input
                                                        type='checkbox'
                                                        id='period_{$period}'
                                                        data-period='{$period}'
                                                        data-unpaid='{$unpaidAmount}'
                                                        style='cursor:pointer;width:16px;height:16px;accent-color:#1e3a5f;'
                                                        onchange=\"
                                                            var clicked = this;
                                                            var clickedPeriod = parseInt(clicked.dataset.period, 10);

                                                            var all = Array.from(document.querySelectorAll('#schedule_table input[type=checkbox]'));

                                                            if (clicked.checked) {
                                                                var blocked = all.some(function(cb) {
                                                                    var p = parseInt(cb.dataset.period, 10);
                                                                    return p < clickedPeriod && !cb.checked;
                                                                });

                                                                if (blocked) {
                                                                    clicked.checked = false;
                                                                    alert('Please select the earliest unpaid due first.');
                                                                    return;
                                                                }
                                                            } else {
                                                                all.forEach(function(cb) {
                                                                    var p = parseInt(cb.dataset.period, 10);
                                                                    if (p > clickedPeriod) {
                                                                        cb.checked = false;
                                                                    }
                                                                });
                                                            }

                                                            var checked = all.filter(function(cb) { return cb.checked; });

                                                            var total = checked.reduce(function(sum, cb) {
                                                                return sum + parseFloat(cb.dataset.unpaid || 0);
                                                            }, 0);

                                                            var selectedPeriods = checked.map(function(cb) {
                                                                return parseInt(cb.dataset.period, 10);
                                                            });

                                                            var amountField = document.getElementById('amount_paid_field');

                                                            if (amountField) {
                                                                amountField.value = total.toFixed(2);
                                                                amountField.dispatchEvent(new Event('input', { bubbles: true }));
                                                                amountField.dispatchEvent(new Event('change', { bubbles: true }));
                                                                amountField.dispatchEvent(new Event('blur', { bubbles: true }));
                                                            }

                                                            var periodsField = document.getElementById('schedule_periods_json_field');

                                                            if (periodsField) {
                                                                periodsField.value = JSON.stringify(selectedPeriods);
                                                                periodsField.dispatchEvent(new Event('input', { bubbles: true }));
                                                                periodsField.dispatchEvent(new Event('change', { bubbles: true }));
                                                                periodsField.dispatchEvent(new Event('blur', { bubbles: true }));
                                                            }
                                                        \"
                                                    >
                                                ";
                                            }

                                            $rowClick = ! $isPaid
                                                ? "onclick=\"
                                                    if (event.target.tagName.toLowerCase() === 'input') return;
                                                    var cb = this.querySelector('input[type=checkbox]');
                                                    if (!cb) return;
                                                    cb.checked = !cb.checked;
                                                    cb.dispatchEvent(new Event('change', { bubbles: true }));
                                                \""
                                                : '';

                                            $rows .= "
                                                <tr style='border-bottom:1px solid #f3f4f6;cursor:pointer;background:#fff;' data-period='{$period}' {$rowClick}>
                                                    <td style='padding:.7rem .9rem;'>{$checkboxHtml}</td>
                                                    <td style='padding:.7rem .9rem;font-size:.78rem;font-weight:700;color:#1e3a5f;'>{$period}</td>
                                                    <td style='padding:.7rem .9rem;font-size:.78rem;color:#374151;'>{$dueDate}</td>
                                                    <td style='padding:.7rem .9rem;font-size:.78rem;text-align:right;'>₱".number_format($scheduledAmortization, 2)."</td>
                                                    <td style='padding:.7rem .9rem;font-size:.78rem;text-align:right;'>₱".number_format($penalty, 2)."</td>
                                                    <td style='padding:.7rem .9rem;font-size:.78rem;text-align:right;'>₱".number_format($totalPaid, 2)."</td>
                                                    <td style='padding:.7rem .9rem;font-size:.78rem;text-align:right;font-weight:700;color:#111827;'>₱".number_format($unpaidAmount, 2)."</td>
                                                    <td style='padding:.7rem .9rem;'>
                                                        <span style='font-size:.65rem;font-weight:700;padding:3px 8px;border-radius:999px;{$statusStyle}'>
                                                            {$status}
                                                        </span>
                                                    </td>
                                                </tr>
                                            ";
                                        }

                                        return new HtmlString("
                                            <div style='border:1px solid #e5e7eb;border-radius:.75rem;overflow:hidden;'>
                                                <table style='width:100%;border-collapse:collapse;' id='schedule_table'>
                                                    <thead>
                                                        <tr style='background:#f9fafb;'>
                                                            <th style='padding:.5rem .9rem;width:36px;'></th>
                                                            <th style='padding:.5rem .9rem;text-align:left;font-size:.62rem;color:#6b7280;font-weight:700;text-transform:uppercase;'>#</th>
                                                            <th style='padding:.5rem .9rem;text-align:left;font-size:.62rem;color:#6b7280;font-weight:700;text-transform:uppercase;'>Due Date</th>
                                                            <th style='padding:.5rem .9rem;text-align:right;font-size:.62rem;color:#6b7280;font-weight:700;text-transform:uppercase;'>Amortization</th>
                                                            <th style='padding:.5rem .9rem;text-align:right;font-size:.62rem;color:#6b7280;font-weight:700;text-transform:uppercase;'>Penalty</th>
                                                            <th style='padding:.5rem .9rem;text-align:right;font-size:.62rem;color:#6b7280;font-weight:700;text-transform:uppercase;'>Paid</th>
                                                            <th style='padding:.5rem .9rem;text-align:right;font-size:.62rem;color:#6b7280;font-weight:700;text-transform:uppercase;'>Unpaid</th>
                                                            <th style='padding:.5rem .9rem;text-align:left;font-size:.62rem;color:#6b7280;font-weight:700;text-transform:uppercase;'>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>{$rows}</tbody>
                                                </table>
                                            </div>
                                        ");
                                    })
                                    ->columnSpanFull(),

                                TextInput::make('schedule_periods_json')
                                    ->hidden()
                                    ->dehydrated()
                                    ->default('[]')
                                    ->extraInputAttributes([
                                        'id' => 'schedule_periods_json_field',
                                    ]),

                                TextInput::make('scheduled_due_date')
                                    ->hidden()
                                    ->dehydrated(),

                                TextInput::make('amount_paid')
                                    ->label('Amount (₱)')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->prefix('₱')
                                    ->readOnly()
                                    ->default('0.00')
                                    ->placeholder('0.00')
                                    ->extraInputAttributes([
                                        'id' => 'amount_paid_field',
                                    ]),

                                DatePicker::make('payment_date')
                                    ->label('Payment Date')
                                    ->required()
                                    ->default(now()),

                                Textarea::make('notes')
                                    ->label('Notes')
                                    ->placeholder('Optional notes...')
                                    ->rows(2)
                                    ->columnSpanFull(),

                                FileUpload::make('file_path')
                                    ->label('Upload Proof of Payment')
                                    ->helperText('Photo of receipt, deposit slip, etc. Max 10MB.')
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'])
                                    ->maxSize(10240)
                                    ->disk('local')
                                    ->directory('collection-proofs')
                                    ->visibility('private')
                                    ->imagePreviewHeight('120')
                                    ->fetchFileInformation(false)
                                    ->nullable()
                                    ->columnSpanFull(),

                                TextInput::make('document_type')
                                    ->default('Official Receipt')
                                    ->hidden(),

                            ])
                            ->columns(2),

                    ])
                        ->skippable(false)
                        ->columnSpanFull(),
                ])
                ->modalCancelAction(
                    Action::make('cancel')
                        ->label('Cancel')
                        ->color('gray')
                        ->close()
                )
                ->action(function (array $data) {

                    $loan = LoanAccount::find($data['loan_account_id']);

                    if ($loan && $loan->status === 'Restructured') {
                        Notification::make()
                            ->title('Invalid payment')
                            ->body('This loan has been restructured and is no longer payable. Please select the Active loan.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $isMember = $this->isMemberUser();

                    if ($isMember) {
                        $memberId = $this->getLoggedInMemberId();

                        if ((int) $data['member_id'] !== (int) $memberId) {
                            Notification::make()
                                ->title('Unauthorized payment submission')
                                ->body('You can only submit payments for your own account.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $allowedLoan = LoanAccount::where('loan_account_id', $data['loan_account_id'])
                            ->whereHas('loanApplication', fn ($q) => $q->where('member_id', $memberId))
                            ->first();

                        if (! $allowedLoan) {
                            Notification::make()
                                ->title('Unauthorized loan selection')
                                ->body('You can only submit payments for your own loan account.')
                                ->danger()
                                ->send();

                            return;
                        }
                    }

                    $member = MemberDetail::with('profile')->find($data['member_id']);
                    $memberName = $member
                        ? trim(($member->profile?->first_name ?? '').' '.($member->profile?->last_name ?? ''))
                        : 'Unknown';

                    $loanNumber = $loan ? "LA-{$loan->loan_account_id}" : 'N/A';

                    $selectedPeriods = json_decode($data['schedule_periods_json'] ?? '[]', true);
                    $selectedPeriods = is_array($selectedPeriods)
                        ? array_values(array_unique(array_map('intval', $selectedPeriods)))
                        : [];

                    $computedAmount = isset($data['amount_paid']) ? (float) $data['amount_paid'] : 0;
                    $computedAmount = round($computedAmount, 2);

                    if ($computedAmount <= 0) {
                        Notification::make()
                            ->title('No payment amount selected')
                            ->body('Please check at least one valid amortization row.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $filePath = $data['file_path'] ?? null;
                    $originalName = null;
                    $mimeType = null;
                    $fileSize = null;

                    if ($filePath && is_string($filePath)) {
                        $disk = Storage::disk('local');
                        $originalName = basename($filePath);

                        if ($disk->exists($filePath)) {
                            $mimeType = $disk->mimeType($filePath);
                            $fileSize = $disk->size($filePath);
                        }
                    }

                    $record = CollectionAndPosting::create([
                        'loan_account_id' => $data['loan_account_id'],
                        'schedule_period' => ! empty($selectedPeriods) ? json_encode($selectedPeriods) : null,
                        'scheduled_due_date' => $data['scheduled_due_date'] ?? null,
                        'loan_number' => $loanNumber,
                        'member_name' => $memberName,
                        'amount_paid' => number_format($computedAmount, 2, '.', ''),
                        'payment_date' => $data['payment_date'],
                        'payment_method' => 'Cash',
                        'notes' => $data['notes'] ?? null,
                        'file_path' => $filePath,
                        'original_file_name' => $originalName,
                        'mime_type' => $mimeType,
                        'file_size' => $fileSize,
                        'document_type' => $data['document_type'] ?? 'Official Receipt',
                        'status' => $isMember ? 'Draft' : 'Posted',
                        'posted_by_user_id' => Auth::id(),
                        'audit_trail' => [
                            'action' => $isMember ? 'Submitted' : 'Posted',
                            'user_id' => Auth::id(),
                            'timestamp' => now()->toISOString(),
                            'loan_id' => $data['loan_account_id'],
                            'member_id' => $data['member_id'],
                        ],
                    ]);

                    if ($isMember) {
                        Notification::make()
                            ->title('Payment submitted')
                            ->body('Your payment was submitted and is awaiting approval.')
                            ->success()
                            ->send();

                        return;
                    }

                    if ($loan) {
                        app(LoanAccountBalanceService::class)->update($loan);
                    }

                    $freshLoan = $loan ? $loan->fresh() : null;
                    $remainingBalance = $freshLoan
                        ? '₱'.number_format($freshLoan->balance, 2)
                        : '—';

                    $nextDueDateLabel = '—';
                    if ($freshLoan) {
                        $freshSchedule = app(LoanScheduleService::class)->build($freshLoan);
                        $freshNextDue = collect($freshSchedule)->first(
                            fn ($row) => round((float) ($row['unpaid_amount'] ?? 0), 2) > 0
                        );
                        if ($freshNextDue && ! empty($freshNextDue['due_date'])) {
                            $nextDueDateLabel = Carbon::parse($freshNextDue['due_date'])->format('M d, Y');
                        }
                    }

                    $this->dispatch('show-receipt', [
                        'member' => $memberName,
                        'loan' => $loanNumber,
                        'date' => Carbon::parse($record->payment_date)->format('M d, Y'),
                        'method' => 'Cash',
                        'posted_by' => auth()->user()?->name ?? 'System',
                        'amount' => '₱'.number_format($record->amount_paid, 2),
                        'balance' => $remainingBalance,
                        'next_due_date' => $nextDueDateLabel,
                        'generated_at' => now()->format('M d, Y h:i A'),
                    ]);
                }),
        ];
    }

    #[On('open-record-payment')]
    public function openRecordPayment(): void
    {
        $this->mountAction('recordPayment');
    }

    public function getHeader(): ?View
    {
        $baseQuery = CollectionAndPosting::query();

        if ($this->isMemberUser()) {
            $loanIds = $this->getLoggedInMemberLoanIds();
            $baseQuery->whereIn('loan_account_id', $loanIds);
        }

        $todayTotal = (clone $baseQuery)->whereDate('payment_date', today())->where('status', 'Posted')->sum('amount_paid');
        $todayCount = (clone $baseQuery)->whereDate('payment_date', today())->where('status', 'Posted')->count();
        $pendingCount = (clone $baseQuery)->where('status', 'Draft')->count();
        $pendingPayments = (clone $baseQuery)
            ->where('status', 'Draft')
            ->latest()
            ->limit(10)
            ->get();

        $dailyByAo = collect();

        if (! $this->isMemberUser()) {
            $dailyByAo = CollectionAndPosting::with('postedBy')
                ->whereDate('payment_date', today())
                ->where('status', 'Posted')
                ->get()
                ->groupBy('posted_by_user_id')
                ->map(function ($payments) {
                    $user = $payments->first()->postedBy;

                    return [
                        'name' => $user?->name ?? 'Unknown',
                        'total' => $payments->sum('amount_paid'),
                        'count' => $payments->count(),
                    ];
                })
                ->values();
        }

        return view('filament.pages.payment.collection-and-posting', compact(
            'todayTotal',
            'todayCount',
            'pendingCount',
            'pendingPayments',
            'dailyByAo',
        ));
    }

    public function getFooter(): ?View
    {
        $auditQuery = CollectionAndPosting::with('postedBy')
            ->whereNotNull('audit_trail')
            ->latest();

        if ($this->isMemberUser()) {
            $loanIds = $this->getLoggedInMemberLoanIds();
            $auditQuery->whereIn('loan_account_id', $loanIds);
        }

        $auditLogs = $auditQuery
            ->limit(50)
            ->get()
            ->map(function ($record) {
                $trail = is_array($record->audit_trail)
                    ? $record->audit_trail
                    : json_decode($record->audit_trail, true);

                return [
                    'action' => $trail['action'] ?? 'Posted',
                    'reference' => $record->loan_number,
                    'member' => $record->member_name,
                    'amount' => $record->amount_paid,
                    'user' => $record->postedBy?->name ?? 'System',
                    'timestamp' => $record->created_at,
                ];
            });

        return view('filament.pages.payment.collection-and-posting-footer', compact('auditLogs'));
    }
}
