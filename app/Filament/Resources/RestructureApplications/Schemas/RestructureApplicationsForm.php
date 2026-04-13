<?php

namespace App\Filament\Resources\RestructureApplications\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Hidden;
use App\Models\LoanApplication;
use App\Models\LoanType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class RestructureApplicationsForm
{
    protected static function getInterestRateDisplay(?LoanType $type): ?string
    {
        if (! $type || blank($type->max_interest_rate)) {
            return null;
        }

        return rtrim(rtrim((string) $type->max_interest_rate, '0'), '.') . '%';
    }

    protected static function applyLoanTypeFields(callable $set, ?LoanType $type): void
    {
        $set('interest_rate_display', static::getInterestRateDisplay($type));
        $set('new_interest', (float) ($type?->max_interest_rate ?? 0));
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Hidden::make('status')
                ->default('Pending'),

            Select::make('loan_application_id')
                ->label('Select Existing Loan')
                ->options(function () {
                    return LoanApplication::with(['member.profile', 'type'])
                        ->get()
                        ->mapWithKeys(function ($loan) {
                            $name = $loan->member?->profile?->full_name ?? 'Unknown Member';

                            return [
                                $loan->loan_application_id =>
                                    $name . " — Loan #{$loan->loan_application_id}",
                            ];
                        })
                        ->toArray();
                })
                ->searchable()
                ->required()
                ->reactive()
                ->afterStateHydrated(function ($state, callable $set) {
                    if (! $state) return;

                    $loan = LoanApplication::with('type')->find($state);
                    if (! $loan) return;

                    $set('loan_type_id', $loan->loan_type_id);

                    $loanAccount = DB::table('loan_accounts')
                        ->where('loan_application_id', $state)
                        ->where('status', 'Active')
                        ->first();

                    $set('old_loan_account_id', $loanAccount?->loan_account_id);
                    $set('new_principal', $loanAccount ? $loanAccount->balance : null);

                    $principal = (float) ($loanAccount?->balance ?? 0);

                    $fees = app(\App\Services\CoopFeeCalculatorService::class)
                        ->calculate('restructure', $principal);

                    $set('shared_capital_fee', $fees['shared_capital_fee'] ?? 0);
                    $set('insurance_fee', $fees['insurance_fee'] ?? 0);
                    $set('processing_fee', $fees['processing_fee'] ?? 0);
                    $set('coop_fee_total', $fees['coop_fee_total'] ?? 0);
                    $set('net_release_amount', $fees['net_release_amount'] ?? 0);

                    $type = $loan->type;

                    $set('interest_rate_display', static::getInterestRateDisplay($type));
                    $set('new_interest', (float) ($type?->max_interest_rate ?? 0));
                })
                ->afterStateUpdated(function ($state, callable $set) {
                    if (! $state) {
                        $set('loan_type_id', null);
                        $set('interest_rate_display', null);
                        $set('new_principal', null);
                        $set('old_loan_account_id', null);
                        return;
                    }

                    $loan = LoanApplication::with('type')->find($state);

                    if (! $loan) {
                        $set('loan_type_id', null);
                        $set('interest_rate_display', null);
                        $set('new_principal', null);
                        $set('old_loan_account_id', null);
                        return;
                    }

                    $set('loan_type_id', $loan->loan_type_id);

                    $loanAccount = DB::table('loan_accounts')
                        ->where('loan_application_id', $state)
                        ->where('status', 'Active')
                        ->first();

                    $set('old_loan_account_id', $loanAccount?->loan_account_id);
                    $set('new_principal', $loanAccount ? $loanAccount->balance : null);

                    $principal = (float) ($loanAccount?->balance ?? 0);

                    $fees = app(\App\Services\CoopFeeCalculatorService::class)
                        ->calculate('restructure', $principal);

                    $set('shared_capital_fee', $fees['shared_capital_fee'] ?? 0);
                    $set('insurance_fee', $fees['insurance_fee'] ?? 0);
                    $set('processing_fee', $fees['processing_fee'] ?? 0);
                    $set('coop_fee_total', $fees['coop_fee_total'] ?? 0);
                    $set('net_release_amount', $fees['net_release_amount'] ?? 0);

                    $type = $loan->type;

                    $set('interest_rate_display', static::getInterestRateDisplay($type));
                    $set('new_interest', (float) ($type?->max_interest_rate ?? 0));
                }),

            Hidden::make('old_loan_account_id'),

            Placeholder::make('payment_status')
                ->label('')
                ->columnSpanFull()
                ->content(function (callable $get): HtmlString {
                    $loanId = $get('loan_application_id');

                    if (! $loanId) {
                        return new HtmlString('');
                    }

                    $loan = LoanApplication::find($loanId);

                    if (! $loan) {
                        return new HtmlString('');
                    }

                    $loanAccount = DB::table('loan_accounts')
                        ->where('loan_application_id', $loanId)
                        ->where('status', 'Active')
                        ->first();

                    $totalAmount   = (float) $loan->amount_requested;
                    $principalPaid = $loanAccount
                        ? (float) $loanAccount->principal_amount - (float) $loanAccount->balance
                        : 0;

                    $threshold   = $totalAmount * 0.5;
                    $balance     = $loanAccount ? (float) $loanAccount->balance : $totalAmount;
                    $percentage  = $totalAmount > 0
                        ? min(100, round(($principalPaid / $totalAmount) * 100, 1))
                        : 0;
                    $isEligible  = $principalPaid >= $threshold;
                    $stillNeeded = max(0, $threshold - $principalPaid);

                    $barColor        = $isEligible ? '#1D9E75' : '#E24B4A';
                    $bgStyle         = $isEligible ? 'background:#d1fae5' : 'background:#fee2e2';
                    $statusText      = $isEligible
                        ? 'Eligible for restructuring — 50% threshold reached'
                        : 'Not eligible — must pay at least 50% of the original loan first';
                    $statusColor     = $isEligible ? '#065f46' : '#991b1b';
                    $paidFormatted   = '₱' . number_format($principalPaid, 2);
                    $amountFormatted = '₱' . number_format($totalAmount, 2);
                    $balanceLabel    = $isEligible ? 'Balance remaining' : 'Still needed';
                    $balanceValue    = $isEligible
                        ? '₱' . number_format($balance, 2)
                        : '₱' . number_format($stillNeeded, 2);

                    return new HtmlString(<<<HTML
                        <div style="border:1px solid #e5e7eb;border-radius:10px;padding:14px 16px;{$bgStyle}">
                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
                                <div style="width:8px;height:8px;border-radius:50%;background:{$barColor};flex-shrink:0"></div>
                                <span style="font-size:13px;font-weight:600;color:{$statusColor}">{$statusText}</span>
                            </div>
                            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:12px">
                                <div style="background:#fff;border-radius:8px;padding:10px 12px">
                                    <div style="font-size:11px;color:#6b7280;margin-bottom:2px">Original amount</div>
                                    <div style="font-size:14px;font-weight:600;color:#111827">{$amountFormatted}</div>
                                </div>
                                <div style="background:#fff;border-radius:8px;padding:10px 12px">
                                    <div style="font-size:11px;color:#6b7280;margin-bottom:2px">Principal paid</div>
                                    <div style="font-size:14px;font-weight:600;color:{$barColor}">{$paidFormatted}</div>
                                </div>
                                <div style="background:#fff;border-radius:8px;padding:10px 12px">
                                    <div style="font-size:11px;color:#6b7280;margin-bottom:2px">{$balanceLabel}</div>
                                    <div style="font-size:14px;font-weight:600;color:{$barColor}">{$balanceValue}</div>
                                </div>
                            </div>
                            <div style="font-size:11px;color:#6b7280;margin-bottom:5px">Payment progress ({$percentage}%)</div>
                            <div style="background:#e5e7eb;border-radius:999px;height:8px;overflow:hidden">
                                <div style="width:{$percentage}%;height:100%;background:{$barColor};border-radius:999px;transition:width .4s"></div>
                            </div>
                            <div style="display:flex;justify-content:space-between;margin-top:4px">
                                <span style="font-size:10px;color:#9ca3af">0%</span>
                                <span style="font-size:10px;font-weight:600;color:{$barColor}">50% threshold</span>
                                <span style="font-size:10px;color:#9ca3af">100%</span>
                            </div>
                        </div>
                    HTML);
                }),

            Select::make('loan_type_id')
                ->label('Loan Type')
                ->options(
                    LoanType::pluck('name', 'loan_type_id')
                )
                ->disabled()
                ->dehydrated()
                ->required(),

            TextInput::make('interest_rate_display')
                ->label('Interest Rate')
                ->disabled()
                ->dehydrated(false),

            Hidden::make('new_interest')
                ->default(0),

            Hidden::make('shared_capital_fee')->default(0),
            Hidden::make('insurance_fee')->default(0),
            Hidden::make('processing_fee')->default(0),

            TextInput::make('coop_fee_total')
                ->label('Total Restructure Fee')
                ->numeric()
                ->prefix('₱')
                ->readOnly()
                ->dehydrated(false)
                ->default(0),

            TextInput::make('net_release_amount')
                ->label('Net Restructured Amount')
                ->numeric()
                ->prefix('₱')
                ->readOnly()
                ->dehydrated(false)
                ->default(0),

            TextInput::make('new_principal')
                ->label('New Loan Amount (Remaining Balance)')
                ->numeric()
                ->prefix('₱')
                ->required()
                ->disabled()
                ->dehydrated(true),

            TextInput::make('term_months')
                ->label('Term (months)')
                ->numeric()
                ->required()
                ->minValue(1)
                ->maxValue(function (callable $get) {
                    $loanId = $get('loan_application_id');
                    if (! $loanId) return null;

                    $loan = LoanApplication::with('type')->find($loanId);

                    return $loan?->type?->max_term_months ?? null;
                })
                ->helperText(function (callable $get) {
                    $loanId = $get('loan_application_id');
                    if (! $loanId) return null;

                    $loan = LoanApplication::with('type')->find($loanId);
                    $max  = $loan?->type?->max_term_months;

                    return $max ? "Max term: {$max} months" : null;
                })
                ->rules(function (callable $get) {
                    $loanId = $get('loan_application_id');
                    if (! $loanId) return [];

                    $loan = LoanApplication::with('type')->find($loanId);
                    if (! $loan?->type) return [];

                    return [
                        'min:1',
                        "max:{$loan->type->max_term_months}",
                    ];
                })
                ->disabled(function (callable $get): bool {
                    return ! static::isEligible($get('loan_application_id'));
                }),

        ])->columns(2);
    }

    protected static function isEligible(?int $loanId): bool
    {
        if (! $loanId) {
            return false;
        }

        $loan = LoanApplication::find($loanId);

        if (! $loan) {
            return false;
        }

        $loanAccount = DB::table('loan_accounts')
            ->where('loan_application_id', $loanId)
            ->where('status', 'Active')
            ->first();

        if (! $loanAccount) {
            return false;
        }

        $principalPaid = (float) $loanAccount->principal_amount - (float) $loanAccount->balance;

        return $principalPaid >= ((float) $loan->amount_requested * 0.5);
    }
}