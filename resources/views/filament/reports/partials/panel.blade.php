@php
    $summaryCards = $report['summary_cards'] ?? [];
    $sections = $report['sections'] ?? [];
@endphp

<div class="report-panel">
    <div class="report-panel__body space-y-5">
        @if (!empty($summaryCards))
            <div class="report-summary-grid">
                @foreach ($summaryCards as $card)
                    <div class="report-summary-card">
                        <div class="report-summary-label">{{ $card['label'] }}</div>
                        <div class="report-summary-value">{{ $card['value'] }}</div>
                    </div>
                @endforeach
            </div>
        @endif

        @include('filament.reports.partials.table', ['table' => $report['main_table']])

        @foreach ($sections as $section)
            @include('filament.reports.partials.table', ['table' => $section])
        @endforeach

        <div class="report-signature-grid">
            <div class="report-signature-card">
                <div class="report-signature-label">Prepared By</div>
                <div class="report-signature-line">{{ $report['footer']['prepared_by'] }}</div>
            </div>

            <div class="report-signature-card">
                <div class="report-signature-label">Verified By</div>
                <div class="report-signature-line">{{ $report['footer']['verified_by'] }}</div>
            </div>
        </div>
    </div>
</div>