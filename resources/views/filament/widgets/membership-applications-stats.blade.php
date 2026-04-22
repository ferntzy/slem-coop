<x-filament-widgets::widget>
    @php
        $stats = $this->getStats();
        $thisMonth = \App\Models\MembershipApplication::whereMonth('created_at', now()->month)->count();
        $approvalRate = $stats['total'] > 0
            ? round(($stats['approved'] / $stats['total']) * 100, 1)
            : 0;
    @endphp
    @once
        <style>
            .loan-dashboard {
                --page-text: #163020;
                --muted-text: #5f6f65;
                --panel-bg: #ffffff;
                --panel-border: #d7e5db;

                --hero-bg: linear-gradient(135deg, #f4fbf6 0%, #edf7f0 45%, #e3f1e7 100%);
                --hero-border: #cfe3d5;
                --hero-title: #123524;
                --hero-text: #4d6354;

                --hero-chip-bg: #e4f3e8;
                --hero-chip-border: #c7dfcf;
                --hero-chip-text: #1f6a3a;

                --hero-side-bg: rgba(255, 255, 255, 0.92);
                --hero-side-border: #d6e7db;

                --soft-box: #f7fbf8;

                --shadow-main: 0 14px 30px rgba(25, 74, 44, 0.08);
            }

            .dark .loan-dashboard {
                --page-text: #f3f7f4;
                --muted-text: #a7b8ad;
                --panel-bg: rgba(2, 6, 23, 0.40);
                --panel-border: rgba(148, 163, 184, 0.14);

                --hero-bg: linear-gradient(135deg, #0f1f17 0%, #153325 45%, #1c4d33 100%);
                --hero-border: rgba(140, 190, 155, 0.18);
                --hero-title: #f3f7f4;
                --hero-text: #c8d4cc;

                --hero-chip-bg: rgba(74, 222, 128, 0.10);
                --hero-chip-border: rgba(74, 222, 128, 0.18);
                --hero-chip-text: #86efac;

                --hero-side-bg: rgba(2, 6, 23, 0.45);
                --hero-side-border: rgba(74, 222, 128, 0.16);

                --soft-box: rgba(2, 6, 23, 0.25);

                --shadow-main: 0 18px 40px rgba(0, 0, 0, 0.28);
            }

            .loan-dashboard {
                display: flex;
                flex-direction: column;
                gap: 22px;
                color: var(--page-text);
            }

            .loan-hero {
                background: var(--hero-bg);
                border: 1px solid var(--hero-border);
                border-radius: 24px;
                padding: 28px 30px;
                box-shadow: var(--shadow-main);
                position: relative;
                overflow: hidden;
            }

            .loan-hero::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 5px;
                background: linear-gradient(90deg, #166534, #22c55e, #86efac);
            }

            .loan-hero__row {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: 20px;
                flex-wrap: wrap;
            }

            .loan-hero__content {
                max-width: 760px;
            }

            .loan-chip {
                display: inline-block;
                padding: 6px 12px;
                border-radius: 999px;
                background: var(--hero-chip-bg);
                border: 1px solid var(--hero-chip-border);
                color: var(--hero-chip-text);
                font-size: 12px;
                font-weight: 700;
                letter-spacing: 1.2px;
                text-transform: uppercase;
            }

            .loan-hero h1 {
                margin: 14px 0 8px;
                font-size: 38px;
                line-height: 1.1;
                font-weight: 800;
                color: var(--hero-title);
            }

            .loan-hero p {
                margin: 0;
                font-size: 15px;
                line-height: 1.7;
                color: var(--hero-text);
            }

            .loan-hero__summary {
                min-width: 240px;
                background: var(--hero-side-bg);
                border: 1px solid var(--hero-side-border);
                border-radius: 18px;
                padding: 18px 20px;
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.14);
            }

            .dark .loan-hero__summary {
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06);
            }

            .loan-hero__summary-label {
                margin: 0 0 6px 0;
                color: var(--muted-text);
                font-size: 12px;
                text-transform: uppercase;
                letter-spacing: 1.4px;
                font-weight: 700;
            }

            .loan-hero__summary-value {
                font-size: 30px;
                font-weight: 800;
                color: var(--hero-title);
                line-height: 1.2;
            }

            .loan-hero__summary-sub {
                margin: 8px 0 0;
                color: var(--hero-text);
                font-size: 13px;
            }

            .loan-grid-4 {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 16px;
            }

            .membership-grid-insights {
                display: grid;
                grid-template-columns: minmax(0, 2fr) repeat(2, minmax(0, 1fr));
                gap: 16px;
            }

            .loan-card {
                border-radius: 20px;
                padding: 20px;
                border: 1px solid var(--panel-border);
                box-shadow: var(--shadow-main);
                background: var(--panel-bg);
                transition: all 0.2s ease;
            }

            .loan-card:hover,
            .loan-panel:hover,
            .loan-mini-card:hover {
                transform: translateY(-2px);
            }

            .loan-card p {
                margin: 0;
            }

            .loan-card h2,
            .loan-card h3 {
                margin: 10px 0 6px;
                font-weight: 800;
                color: var(--page-text);
            }

            .loan-card--pending {
                background: linear-gradient(135deg, #fffdf7 0%, #f8f5ea 100%);
                border-left: 5px solid #b59a3b;
            }

            .loan-card--review {
                background: linear-gradient(135deg, #f6fbf7 0%, #eef7f0 100%);
                border-left: 5px solid #4f8a63;
            }

            .loan-card--approved {
                background: linear-gradient(135deg, #f2fbf4 0%, #e7f5ea 100%);
                border-left: 5px solid #1f7a3d;
            }

            .loan-card--rejected {
                background: linear-gradient(135deg, #fff8f8 0%, #f9efef 100%);
                border-left: 5px solid #b04b4b;
            }

            .dark .loan-card--pending {
                background: linear-gradient(135deg, rgba(245, 158, 11, 0.10) 0%, rgba(2, 6, 23, 0.35) 100%);
                border-left-color: rgba(245, 158, 11, 0.55);
            }

            .dark .loan-card--review {
                background: linear-gradient(135deg, rgba(74, 222, 128, 0.10) 0%, rgba(2, 6, 23, 0.35) 100%);
                border-left-color: rgba(74, 222, 128, 0.50);
            }

            .dark .loan-card--approved {
                background: linear-gradient(135deg, rgba(34, 197, 94, 0.12) 0%, rgba(2, 6, 23, 0.35) 100%);
                border-left-color: rgba(34, 197, 94, 0.55);
            }

            .dark .loan-card--rejected {
                background: linear-gradient(135deg, rgba(244, 63, 94, 0.10) 0%, rgba(2, 6, 23, 0.35) 100%);
                border-left-color: rgba(244, 63, 94, 0.55);
            }

            .loan-label {
                font-size: 13px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 1px;
            }

            .loan-label--pending { color: #8a7330; }
            .loan-label--review { color: #3d6d4d; }
            .loan-label--approved { color: #166534; }
            .loan-label--rejected { color: #a33d3d; }

            .dark .loan-label--pending { color: #f3d98b; }
            .dark .loan-label--review { color: #9bd3aa; }
            .dark .loan-label--approved { color: #86efac; }
            .dark .loan-label--rejected { color: #f0a3a3; }

            .loan-subtext {
                font-size: 13px;
                color: var(--muted-text);
            }

            .loan-panel {
                background: var(--panel-bg);
                border: 1px solid var(--panel-border);
                border-radius: 20px;
                padding: 22px;
                min-width: 0;
                box-shadow: var(--shadow-main);
            }

            .loan-panel h3 {
                margin: 0 0 14px;
                color: var(--page-text);
                font-size: 18px;
                font-weight: 700;
            }

            .loan-soft-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 14px;
            }

            .loan-soft-box {
                padding: 14px;
                border-radius: 14px;
                background: var(--soft-box);
                border: 1px solid var(--panel-border);
            }

            .loan-soft-box p:first-child {
                margin: 0;
                font-size: 12px;
                color: var(--muted-text);
                text-transform: uppercase;
                letter-spacing: 1px;
            }

            .loan-soft-box p:last-child {
                margin: 8px 0 0;
                font-size: 26px;
                color: var(--page-text);
                font-weight: 800;
            }

            .loan-mini-card {
                border-radius: 20px;
                padding: 20px;
                box-shadow: var(--shadow-main);
            }

            .loan-mini-card p {
                margin: 0;
            }

            .loan-mini-card h3 {
                margin: 12px 0 0;
                color: var(--page-text);
                font-size: 28px;
                font-weight: 800;
                line-height: 1.2;
            }

            .loan-mini-card small {
                display: block;
                margin-top: 10px;
                font-size: 13px;
                color: var(--muted-text);
            }

            .loan-mini-card--green {
                background: linear-gradient(135deg, #f1faf3 0%, #e7f6ea 100%);
                border: 1px solid #cfe5d5;
            }

            .loan-mini-card--yellow {
                background: linear-gradient(135deg, #fcfbf4 0%, #f5f1e4 100%);
                border: 1px solid #e3ddc1;
            }

            .dark .loan-mini-card--green {
                background: linear-gradient(135deg, rgba(34, 197, 94, 0.12) 0%, rgba(2, 6, 23, 0.35) 100%);
                border-color: rgba(34, 197, 94, 0.18);
            }

            .dark .loan-mini-card--yellow {
                background: linear-gradient(135deg, rgba(245, 158, 11, 0.10) 0%, rgba(2, 6, 23, 0.35) 100%);
                border-color: rgba(245, 158, 11, 0.18);
            }

            .loan-mini-label {
                font-size: 12px;
                text-transform: uppercase;
                letter-spacing: 1px;
                font-weight: 700;
            }

            .loan-mini-label--green { color: #166534; }
            .loan-mini-label--yellow { color: #8b7a2f; }

            .dark .loan-mini-label--green { color: #86efac; }
            .dark .loan-mini-label--yellow { color: #f3d98b; }

            @media (max-width: 1200px) {
                .membership-grid-insights {
                    grid-template-columns: 1fr 1fr;
                }
            }

            @media (max-width: 768px) {
                .loan-hero {
                    padding: 22px 20px;
                }

                .loan-hero h1 {
                    font-size: 30px;
                }

                .membership-grid-insights {
                    grid-template-columns: 1fr;
                }

                .loan-soft-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    @endonce

    <div class="loan-dashboard">
        <div class="loan-hero">
            <div class="loan-hero__row">
                <div class="loan-hero__content">
                    <div class="loan-chip">
                        Membership Management Dashboard
                    </div>

                    <h1>Membership Applications Overview</h1>

                    <p>
                        Track submitted applications, review approval progress, and monitor membership decisions
                        from one centralized page.
                    </p>
                </div>

                <div class="loan-hero__summary">
                    <p class="loan-hero__summary-label">Total Applications</p>
                    <div class="loan-hero__summary-value">{{ $stats['total'] }}</div>
                    <p class="loan-hero__summary-sub">All time</p>
                </div>
            </div>
        </div>

        <div class="loan-grid-4">
            <div class="loan-card loan-card--pending">
                <p class="loan-label loan-label--pending">Pending</p>
                <h2>{{ $stats['pending'] }}</h2>
                <p class="loan-subtext">Applications awaiting initial review.</p>
            </div>

            <div class="loan-card loan-card--review">
                <p class="loan-label loan-label--review">Needs Review</p>
                <h2>{{ $stats['under_review'] }}</h2>
                <p class="loan-subtext">Applications currently being evaluated.</p>
            </div>

            <div class="loan-card loan-card--approved">
                <p class="loan-label loan-label--approved">Approved</p>
                <h2>{{ $stats['approved'] }}</h2>
                <p class="loan-subtext">Applications approved for membership.</p>
            </div>

            <div class="loan-card loan-card--rejected">
                <p class="loan-label loan-label--rejected">Rejected</p>
                <h2>{{ $stats['rejected'] }}</h2>
                <p class="loan-subtext">Applications that did not pass evaluation.</p>
            </div>
        </div>

        <div class="membership-grid-insights">
            <div class="loan-panel">
                <h3>Quick Insights</h3>

                <div class="loan-soft-grid">
                    <div class="loan-soft-box">
                        <p>This Month</p>
                        <p>{{ $thisMonth }}</p>
                    </div>

                    <div class="loan-soft-box">
                        <p>Approval Rate</p>
                        <p>{{ $approvalRate }}%</p>
                    </div>
                </div>
            </div>

            <div class="loan-mini-card loan-mini-card--green">
                <p class="loan-mini-label loan-mini-label--green">Open Pipeline</p>
                <h3>{{ $stats['pending'] + $stats['under_review'] }}</h3>
                <small>Applications still in active processing stages.</small>
            </div>

            <div class="loan-mini-card loan-mini-card--yellow">
                <p class="loan-mini-label loan-mini-label--yellow">Decisioned</p>
                <h3>{{ $stats['approved'] + $stats['rejected'] }}</h3>
                <small>Applications with final outcomes recorded.</small>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
