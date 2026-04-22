<?php

namespace App\Filament\Pages;

use App\Models\Branch;
use App\Models\MemberDetail;
use App\Models\User;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ReportsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Reports';

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Reports';

    protected static ?string $slug = 'reports';

    protected string $view = 'filament.pages.reports';

    public string $startDate = '';

    public string $endDate = '';

    public ?int $branchId = null;

    public ?int $memberId = null;

    public string $activeReportTab = 'daily-collection';

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if ($user->isAdminOrSuperAdmin()) {
            return true;
        }

        return static::userHasAnyRoleName($user, [
            'super_admin',
            'Super Admin',
            'super admin',
            'admin',
            'Admin',
            'manager',
            'Manager',
            'hq_manager',
            'HQ Manager',
            'hqmanager',
            'branch_manager',
            'Branch Manager',
            'cashier',
            'Cashier',
            'teller',
            'Teller',
            'cash_handler',
            'Cash Handler',
            'hq_cashier',
            'HQ Cashier',
            'hq_teller',
            'HQ Teller',
            'loan_officer',
            'Loan Officer',
            'hq_loan_officer',
            'HQ Loan Officer',
            'loan_manager',
            'Loan Manager',
            'credit_committee',
            'Credit Committee',
            'account_officer',
            'Account Officer',
            'hq_account_officer',
            'HQ Account Officer',
            'member',
            'Member',
        ]);
    }

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->toDateString();

        if ($this->isBranchScopedUser() && $this->currentUser()->branchId()) {
            $this->branchId = $this->currentUser()->branchId();
        }

        if ($this->isMemberUser()) {
            $this->memberId = $this->currentUser()->profile?->memberDetail?->getKey();
            $this->branchId = $this->currentUser()->profile?->memberDetail?->branch_id;
            $this->activeReportTab = 'loan-statement';
        }

        $this->activeReportTab = $this->normalizeReportTab($this->activeReportTab);
    }

    public function form(Schema $form): Schema
    {
        return $form->schema([
            Section::make('Filters')
                ->description('Change the period, branch, or member scope. The selected filters apply to the visible tab and PDF export.')
                ->schema([
                    DatePicker::make('startDate')
                        ->label('Start Date')
                        ->required()
                        ->native(false)
                        ->live(),
                    DatePicker::make('endDate')
                        ->label('End Date')
                        ->required()
                        ->native(false)
                        ->live(),
                    Select::make('branchId')
                        ->label('Branch')
                        ->options(fn (): array => $this->branchOptions())
                        ->placeholder('All branches')
                        ->searchable()
                        ->live()
                        ->disabled(fn (): bool => $this->isBranchFilterLocked()),
                    Select::make('memberId')
                        ->label('Member')
                        ->searchable()
                        ->getSearchResultsUsing(fn (string $search): array => $this->memberSearchResults($search))
                        ->getOptionLabelUsing(fn ($value): ?string => $this->memberOptionLabel($value))
                        ->placeholder('All members')
                        ->live()
                        ->disabled(fn (): bool => $this->isMemberFilterLocked()),
                ])
                ->columns(4),

            Tabs::make('ReportsTabs')
                ->label('Reports')
                ->persistTabInQueryString('report')
                ->livewireProperty('activeReportTab')
                ->tabs($this->reportTabs()),
        ]);
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action('downloadPdf'),

            Action::make('refreshReport')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action('refreshReport'),
        ];
    }

    public function downloadPdf(): mixed
    {
        $report = $this->reportData($this->activeReportTab);
        $filename = Str::slug($report['title']).'.pdf';
        $pdf = Pdf::loadView('filament.reports.pdf', [
            'report' => $report,
        ])
            ->setPaper('a4', $report['orientation'] ?? 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'dpi' => 150,
            ]);

        return response()->streamDownload(function () use ($pdf): void {
            echo $pdf->output();
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function refreshReport(): void {}

    public function reportData(?string $reportKey = null): array
    {
        return app(ReportService::class)->build(
            $this->normalizeReportTab($reportKey ?? $this->activeReportTab),
            $this->filterState(),
            $this->currentUser(),
        );
    }

    public function activeReportLabel(): string
    {
        return $this->reportDefinitions()[$this->normalizeReportTab($this->activeReportTab)]['label'] ?? 'Reports';
    }

    public function activeReportDescription(): string
    {
        return $this->reportDefinitions()[$this->normalizeReportTab($this->activeReportTab)]['description'] ?? 'View operational reports using the selected filters.';
    }

    public function filterState(): array
    {
        return [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'branchId' => $this->branchId,
            'memberId' => $this->memberId,
        ];
    }

    public function branchOptions(): array
    {
        return Branch::query()
            ->orderBy('name')
            ->pluck('name', 'branch_id')
            ->all();
    }

    public function memberSearchResults(string $search): array
    {
        return MemberDetail::query()
            ->with('profile')
            ->when($this->branchId, fn ($query, int $branchId) => $query->where('branch_id', $branchId))
            ->whereHas('profile', function ($query) use ($search): void {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            })
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->mapWithKeys(fn (MemberDetail $member) => [
                $member->getKey() => $member->profile?->full_name ?? 'Unknown Member',
            ])
            ->all();
    }

    public function memberOptionLabel(mixed $value): ?string
    {
        if (! $value) {
            return null;
        }

        return MemberDetail::query()
            ->with('profile')
            ->find($value)
            ?->profile?->full_name;
    }

    protected function currentUser(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }

    protected function isBranchScopedUser(): bool
    {
        return $this->currentUser()->isBranchScoped() && $this->currentUser()->branchId() !== null;
    }

    protected function isMemberUser(): bool
    {
        return $this->currentUser()->isMember();
    }

    protected function isBranchFilterLocked(): bool
    {
        return $this->isBranchScopedUser() || $this->isMemberUser();
    }

    protected function isMemberFilterLocked(): bool
    {
        return $this->isMemberUser();
    }

    protected function reportTabs(): array
    {
        $tabs = [];

        foreach ($this->reportDefinitions() as $reportKey => $definition) {
            $tabs[$reportKey] = Tab::make($definition['label'])
                ->id($reportKey)
                ->icon($definition['icon'])
                ->visible(fn (): bool => $this->reportVisible($definition['roles']))
                ->schema([
                    View::make($definition['view'])
                        ->viewData(fn (): array => [
                            'report' => $this->reportData($reportKey),
                        ])
                        ->columnSpanFull(),
                ]);
        }

        return $tabs;
    }

    protected function reportDefinitions(): array
    {
        return [
            'system-summary' => [
                'label' => 'System Summary',
                'icon' => 'heroicon-o-chart-bar-square',
                'description' => 'High-level operational and financial summary for the selected period.',
                'view' => 'filament.reports.tabs.report',
                'roles' => $this->adminRoles(),
            ],
            'financial-summary' => [
                'label' => 'Financial Summary',
                'icon' => 'heroicon-o-currency-dollar',
                'description' => 'Consolidated inflow, outflow, and net movement across core financial modules.',
                'view' => 'filament.reports.tabs.report',
                'roles' => $this->adminRoles(),
            ],
            'audit-trail' => [
                'label' => 'Audit Trail',
                'icon' => 'heroicon-o-clipboard-document-list',
                'description' => 'Loan workflow and payment change logs for accountability and compliance.',
                'view' => 'filament.reports.tabs.report',
                'roles' => $this->adminRoles(),
            ],
            'branch-performance' => [
                'label' => 'Branch Performance',
                'icon' => 'heroicon-o-building-office-2',
                'description' => 'Branch-level KPIs for membership, collections, portfolio, and delinquency.',
                'view' => 'filament.reports.tabs.report',
                'roles' => $this->adminRoles(),
            ],
            'loan-portfolio' => [
                'label' => 'Loan Portfolio',
                'icon' => 'heroicon-o-rectangle-stack',
                'description' => 'Portfolio composition by loan type, status, and outstanding balances.',
                'view' => 'filament.reports.tabs.report',
                'roles' => $this->managerRoles(),
            ],
            'delinquency' => [
                'label' => 'Delinquency',
                'icon' => 'heroicon-o-exclamation-triangle',
                'description' => 'Loan accounts with overdue schedules and delinquent balances.',
                'view' => 'filament.reports.tabs.report',
                'roles' => $this->managerRoles(),
            ],
            'cash-flow' => [
                'label' => 'Cash Flow',
                'icon' => 'heroicon-o-arrows-right-left',
                'description' => 'Monthly movement of collections, deposits, withdrawals, and releases.',
                'view' => 'filament.reports.tabs.report',
                'roles' => $this->managerRoles(),
            ],
            'loan-approval' => [
                'label' => 'Loan Approval',
                'icon' => 'heroicon-o-check-badge',
                'description' => 'Approval pipeline from submission to manager/admin decision.',
                'view' => 'filament.reports.tabs.report',
                'roles' => $this->managerRoles(),
            ],
            'daily-collection' => [
                'label' => 'Daily Collection',
                'icon' => 'heroicon-o-banknotes',
                'description' => 'Posted loan payments, cashier breakdowns, and daily collection entries.',
                'view' => 'filament.reports.tabs.report',
                'roles' => $this->cashierRoles(),
            ],
            'transaction-report' => [
                'label' => 'Transaction Report',
                'icon' => 'heroicon-o-receipt-percent',
                'description' => 'Unified ledger of posted loan, savings, and collection transactions.',
                'view' => 'filament.reports.tabs.report',
                'roles' => $this->cashierRoles(),
            ],
            'cashier-summary' => [
                'label' => 'Cashier Summary',
                'icon' => 'heroicon-o-calculator',
                'description' => 'Daily cashier handling totals with net cash movement.',
                'view' => 'filament.reports.tabs.report',
                'roles' => $this->cashierRoles(),
            ],
            'member-account' => [
                'label' => 'Member Account',
                'icon' => 'heroicon-o-user-group',
                'description' => 'Member-level balances, status, share capital, and outstanding loan view.',
                'view' => 'filament.reports.tabs.report',
                'roles' => $this->accountOfficerRoles(),
            ],
            'loan-tracking' => [
                'label' => 'Loan Tracking',
                'icon' => 'heroicon-o-chart-pie',
                'description' => 'Outstanding balances, due dates, and repayment progress by account.',
                'view' => 'filament.reports.tabs.report',
                'roles' => $this->accountOfficerRoles(),
            ],
            'collection-monitoring' => [
                'label' => 'Collection Monitoring',
                'icon' => 'heroicon-o-eye',
                'description' => 'Due-now, due-soon, and overdue monitoring for collection follow-up.',
                'view' => 'filament.reports.tabs.report',
                'roles' => $this->accountOfficerRoles(),
            ],
            'delinquent-accounts' => [
                'label' => 'Delinquent Accounts',
                'icon' => 'heroicon-o-shield-exclamation',
                'description' => 'Overdue accounts and delinquent balances within account officer scope.',
                'view' => 'filament.reports.tabs.report',
                'roles' => $this->accountOfficerRoles(),
            ],
            'loan-application' => [
                'label' => 'Loan Application',
                'icon' => 'heroicon-o-document-text',
                'description' => 'Loan application listing with status and approval stage.',
                'view' => 'filament.reports.tabs.report',
                'roles' => $this->loanOfficerRoles(),
            ],
            'loan-evaluation' => [
                'label' => 'Loan Evaluation',
                'icon' => 'heroicon-o-document-magnifying-glass',
                'description' => 'Cashflow-based evaluation metrics and recommendations.',
                'view' => 'filament.reports.tabs.report',
                'roles' => $this->loanOfficerRoles(),
            ],
            'approved-loans' => [
                'label' => 'Approved Loans',
                'icon' => 'heroicon-o-check-circle',
                'description' => 'Released loan accounts with approval trail and terms.',
                'view' => 'filament.reports.tabs.report',
                'roles' => $this->loanOfficerRoles(),
            ],
            'restructured-loans' => [
                'label' => 'Restructured Loans',
                'icon' => 'heroicon-o-arrow-path-rounded-square',
                'description' => 'Restructure applications, terms, and decision logs.',
                'view' => 'filament.reports.tabs.report',
                'roles' => $this->loanOfficerRoles(),
            ],
            'loan-statement' => [
                'label' => 'Loan Statement',
                'icon' => 'heroicon-o-document-chart-bar',
                'description' => 'Member-facing loan account balances, due dates, and payment section.',
                'view' => 'filament.reports.tabs.report',
                'roles' => $this->memberRoles(),
            ],
            'payment-history' => [
                'label' => 'Payment History',
                'icon' => 'heroicon-o-clock',
                'description' => 'Chronological posted payment record for selected member scope.',
                'view' => 'filament.reports.tabs.report',
                'roles' => $this->memberRoles(),
            ],
            'savings-statement' => [
                'label' => 'Savings Statement',
                'icon' => 'heroicon-o-wallet',
                'description' => 'Savings deposits, withdrawals, and net movement.',
                'view' => 'filament.reports.tabs.report',
                'roles' => $this->memberRoles(),
            ],
            'member-statement' => [
                'label' => 'Member Statement',
                'icon' => 'heroicon-o-user-circle',
                'description' => 'Savings transactions, loan balances, and payment activity for members.',
                'view' => 'filament.reports.tabs.report',
                'roles' => $this->adminRoles(),
            ],
        ];
    }

    protected function adminRoles(): array
    {
        return [
            'super_admin',
            'Super Admin',
            'super admin',
            'admin',
            'Admin',
        ];
    }

    protected function managerRoles(): array
    {
        return [
            'manager',
            'Manager',
            'hq_manager',
            'HQ Manager',
            'hqmanager',
            'branch_manager',
            'Branch Manager',
            'loan_manager',
            'Loan Manager',
            'credit_committee',
            'Credit Committee',
        ];
    }

    protected function cashierRoles(): array
    {
        return [
            'cashier',
            'Cashier',
            'teller',
            'Teller',
            'cash_handler',
            'Cash Handler',
            'hq_cashier',
            'HQ Cashier',
            'hq_teller',
            'HQ Teller',
        ];
    }

    protected function accountOfficerRoles(): array
    {
        return [
            'account_officer',
            'Account Officer',
            'hq_account_officer',
            'HQ Account Officer',
        ];
    }

    protected function loanOfficerRoles(): array
    {
        return [
            'loan_officer',
            'Loan Officer',
            'hq_loan_officer',
            'HQ Loan Officer',
        ];
    }

    protected function memberRoles(): array
    {
        return [
            'member',
            'Member',
        ];
    }

    protected function reportVisible(array $roles): bool
    {
        if ($this->currentUser()->isAdminOrSuperAdmin()) {
            return true;
        }

        return static::userHasAnyRoleName($this->currentUser(), $roles);
    }

    protected static function userHasAnyRoleName(User $user, array $roles): bool
    {
        if ($user->hasAnyRole($roles)) {
            return true;
        }

        $userRoleNames = $user->roles
            ->pluck('name')
            ->push($user->profile?->role?->name)
            ->filter()
            ->map(fn (mixed $roleName): string => static::normalizeRoleName((string) $roleName))
            ->unique();

        if ($userRoleNames->isEmpty()) {
            return false;
        }

        $allowedRoleNames = collect($roles)
            ->map(fn (mixed $roleName): string => static::normalizeRoleName((string) $roleName))
            ->unique();

        return $userRoleNames->intersect($allowedRoleNames)->isNotEmpty();
    }

    protected static function normalizeRoleName(string $roleName): string
    {
        return (string) Str::of($roleName)
            ->lower()
            ->replace(['-', ' '], '_')
            ->trim();
    }

    protected function normalizeReportTab(?string $reportKey): string
    {
        $reportKey = $reportKey ?: 'daily-collection';
        $definitions = $this->reportDefinitions();

        if (! isset($definitions[$reportKey])) {
            return $this->firstVisibleReportKey();
        }

        if (! $this->reportVisible($definitions[$reportKey]['roles'])) {
            return $this->firstVisibleReportKey();
        }

        return $reportKey;
    }

    protected function firstVisibleReportKey(): string
    {
        foreach ($this->reportDefinitions() as $reportKey => $definition) {
            if ($this->reportVisible($definition['roles'])) {
                return $reportKey;
            }
        }

        return 'daily-collection';
    }
}
