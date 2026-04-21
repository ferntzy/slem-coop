<?php

namespace App\Filament\Pages\Reports;

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
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

abstract class AbstractReportPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.reports.report';

    public string $startDate = '';

    public string $endDate = '';

    public ?int $branchId = null;

    public ?int $memberId = null;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return $user->hasAnyRole(static::allowedRoles());
    }

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->toDateString();

        if ($this->isBranchScopedUser() && $this->currentUser()?->branchId()) {
            $this->branchId = $this->currentUser()->branchId();
        }
    }

    public function form(Schema $form): Schema
    {
        return $form->schema([
            Section::make('Filters')
                ->description('Date range, branch, and member filters apply to the report below and the PDF export.')
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
                        ->disabled(fn (): bool => $this->isBranchScopedUser()),
                    Select::make('memberId')
                        ->label('Member')
                        ->searchable()
                        ->getSearchResultsUsing(fn (string $search): array => $this->memberSearchResults($search))
                        ->getOptionLabelUsing(fn ($value): ?string => $this->memberOptionLabel($value))
                        ->placeholder('All members')
                        ->live()
                        ->disabled(fn (): bool => $this->memberSelectDisabled() || $this->isBranchScopedUser()),
                ])
                ->columns(4),
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
        $report = $this->reportData();

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

    public function reportData(): array
    {
        return app(ReportService::class)->build(
            static::reportKey(),
            $this->filterState(),
            $this->currentUser(),
        );
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

    protected function memberSelectDisabled(): bool
    {
        return false;
    }

    abstract protected static function reportKey(): string;

    abstract protected static function allowedRoles(): array;
}
