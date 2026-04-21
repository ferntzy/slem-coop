<x-filament-panels::page>
    @include('filament.reports.partials.styles')

    <div class="report-dashboard space-y-6">
        <section class="report-hero report-surface">
            <div class="report-hero__intro">
                <div class="report-badge">
                    Reports
                </div>

                <div class="report-hero__copy">
                    <h1 class="report-hero__title">
                        Reports Dashboard
                    </h1>
                    <p class="report-hero__description">
                        Switch tabs to view each report. Filters stay in sync with the selected tab and the PDF export.
                    </p>
                </div>
            </div>

            <div class="report-meta">
                <div class="report-meta__row">
                    <span class="report-meta__label">Active Report</span>
                    <span class="report-meta__value">{{ $this->activeReportLabel() }}</span>
                </div>

                <div class="report-meta__row">
                    <span class="report-meta__label">Period</span>
                    <span class="report-meta__value">
                        {{ \Illuminate\Support\Carbon::parse($this->startDate)->format('M d, Y') }} - {{ \Illuminate\Support\Carbon::parse($this->endDate)->format('M d, Y') }}
                    </span>
                </div>

                @if ($this->branchId)
                    <div class="report-meta__row">
                        <span class="report-meta__label">Branch</span>
                        <span class="report-meta__value">{{ $this->branchOptions()[$this->branchId] ?? 'Selected branch' }}</span>
                    </div>
                @endif

                @if ($this->memberId)
                    <div class="report-meta__row">
                        <span class="report-meta__label">Member</span>
                        <span class="report-meta__value">{{ $this->memberOptionLabel($this->memberId) ?? 'Selected member' }}</span>
                    </div>
                @endif
            </div>

            <p class="report-hero__note">
                {{ $this->activeReportDescription() }}
            </p>
        </section>

        <section class="report-filters report-surface">
            {{ $this->form }}
        </section>
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>