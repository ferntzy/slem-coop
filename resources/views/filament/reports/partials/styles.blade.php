@once
    <style>
        .report-shell {
            color: rgb(15 23 42);
        }

        .dark .report-shell {
            color: rgb(226 232 240);
        }

        .report-panel {
            --report-surface: rgb(255 255 255);
            --report-surface-alt: rgb(248 250 252);
            --report-border: rgb(226 232 240);
            --report-border-strong: rgb(203 213 225);
            --report-text: rgb(15 23 42);
            --report-muted: rgb(100 116 139);
            --report-accent: rgb(13 148 136);
            --report-accent-soft: rgb(236 253 245);
            --report-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
            border: 1px solid var(--report-border);
            border-radius: 1.5rem;
            background: var(--report-surface);
            box-shadow: var(--report-shadow);
            overflow: hidden;
        }

        .dark .report-panel {
            --report-surface: rgb(15 23 42);
            --report-surface-alt: rgb(2 6 23);
            --report-border: rgb(30 41 59);
            --report-border-strong: rgb(51 65 85);
            --report-text: rgb(226 232 240);
            --report-muted: rgb(148 163 184);
            --report-accent: rgb(45 212 191);
            --report-accent-soft: rgb(15 118 110 / 0.18);
            --report-shadow: 0 12px 32px rgba(2, 6, 23, 0.35);
        }

        .report-panel__body {
            padding: 1.25rem;
        }

        .report-summary-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        }

        .report-summary-card {
            border: 1px solid var(--report-border);
            border-radius: 1rem;
            background: linear-gradient(180deg, var(--report-surface), var(--report-surface-alt));
            padding: 1rem;
            box-shadow: var(--report-shadow);
        }

        .report-summary-label {
            color: var(--report-muted);
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        .report-summary-value {
            color: var(--report-text);
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            margin-top: 0.5rem;
        }

        .report-signature-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        @media (max-width: 767px) {
            .report-signature-grid {
                grid-template-columns: 1fr;
            }
        }

        .report-signature-card {
            border: 1px dashed var(--report-border-strong);
            border-radius: 1rem;
            background: var(--report-surface-alt);
            padding: 1rem;
        }

        .report-signature-label {
            color: var(--report-muted);
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        .report-signature-line {
            border-top: 1px solid var(--report-border-strong);
            color: var(--report-text);
            font-weight: 600;
            margin-top: 2rem;
            padding-top: 0.75rem;
        }

        .report-table {
            border: 1px solid var(--report-border);
            border-radius: 1rem;
            background: var(--report-surface);
            box-shadow: var(--report-shadow);
            overflow: hidden;
        }

        .report-table__heading {
            border-bottom: 1px solid var(--report-border);
            background: var(--report-surface-alt);
            color: var(--report-text);
            font-size: 0.95rem;
            font-weight: 700;
            padding: 0.85rem 1rem;
        }

        .report-table__scroll {
            overflow-x: auto;
        }

        .report-table__table {
            width: 100%;
            border-collapse: collapse;
        }

        .report-table__head {
            background: rgb(15 23 42);
            color: rgb(255 255 255);
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            padding: 0.85rem 1rem;
            text-align: left;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .dark .report-table__head {
            background: rgb(30 41 59);
        }

        .report-table__head--right,
        .report-table__cell--right,
        .report-table__footer--right {
            text-align: right;
        }

        .report-table__cell,
        .report-table__footer {
            border-top: 1px solid var(--report-border);
            color: var(--report-text);
            padding: 0.85rem 1rem;
            vertical-align: top;
        }

        .report-table__row:nth-child(even) .report-table__cell {
            background: var(--report-surface-alt);
        }

        .report-table__empty {
            color: var(--report-muted);
            padding: 1rem;
            text-align: center;
        }

        .report-table__footer {
            background: rgba(16, 185, 129, 0.06);
            font-weight: 700;
        }

        .dark .report-table__footer {
            background: rgb(6 78 59 / 0.22);
        }

        .report-table__footer--label {
            color: var(--report-text);
        }

        .report-table__footer--muted {
            color: var(--report-text);
        }
    </style>
@endonce