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

        return $user->hasAnyRole([
            'super_admin',
            'Super Admin',
            'admin',
            'Admin',
            'manager',
            'Manager',
            'hq_manager',
            'HQ Manager',
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
            $this->activeReportTab = 'member-statement';
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

        return Pdf::loadView('filament.reports.pdf', [
            'report' => $report,
        ])
            ->setPaper('a4', $report['orientation'] ?? 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'dpi' => 150,
            ])
            ->download(Str::slug($report['title']).'.pdf');
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
                        ->viewData([
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
            'daily-collection' => [
                'label' => 'Daily Collection',
                'icon' => 'heroicon-o-banknotes',
                'description' => 'Posted loan payments, cashier breakdowns, and daily collection entries.',
                'view' => 'filament.reports.tabs.report',
                'roles' => [
                    'super_admin',
                    'Super Admin',
                    'admin',
                    'Admin',
                    'manager',
                    'Manager',
                    'hq_manager',
                    'HQ Manager',
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
                    'account_officer',
                    'Account Officer',
                    'hq_account_officer',
                    'HQ Account Officer',
                ],
            ],
            'loan-tracking' => [
                'label' => 'Loan Tracking',
                'icon' => 'heroicon-o-rectangle-stack',
                'description' => 'Outstanding loan balances, due dates, and repayment progress.',
                'view' => 'filament.reports.tabs.report',
                'roles' => [
                    'super_admin',
                    'Super Admin',
                    'admin',
                    'Admin',
                    'manager',
                    'Manager',
                    'hq_manager',
                    'HQ Manager',
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
                ],
            ],
            'system-summary' => [
                'label' => 'System Summary',
                'icon' => 'heroicon-o-chart-bar-square',
                'description' => 'High-level operational and financial summary for the selected period.',
                'view' => 'filament.reports.tabs.report',
                'roles' => [
                    'super_admin',
                    'Super Admin',
                    'admin',
                    'Admin',
                ],
            ],
            'delinquency' => [
                'label' => 'Delinquency',
                'icon' => 'heroicon-o-exclamation-triangle',
                'description' => 'Loan accounts with overdue schedules and delinquent balances.',
                'view' => 'filament.reports.tabs.report',
                'roles' => [
                    'super_admin',
                    'Super Admin',
                    'admin',
                    'Admin',
                    'manager',
                    'Manager',
                    'hq_manager',
                    'HQ Manager',
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
                ],
            ],
            'member-statement' => [
                'label' => 'Member Statement',
                'icon' => 'heroicon-o-user-circle',
                'description' => 'Savings transactions, loan balances, and payment activity for members.',
                'view' => 'filament.reports.tabs.report',
                'roles' => [
                    'super_admin',
                    'Super Admin',
                    'admin',
                    'Admin',
                    'manager',
                    'Manager',
                    'hq_manager',
                    'HQ Manager',
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
                ],
            ],
        ];
    }

    protected function reportVisible(array $roles): bool
    {
        return $this->currentUser()->hasAnyRole($roles);
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
