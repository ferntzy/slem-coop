@once
    <style>
        .report-shell,
        .report-dashboard {
            color: rgb(15 23 42);
        }

        .dark .report-shell,
        .dark .report-dashboard {
            color: rgb(226 232 240);
        }

        .report-dashboard {
            --report-surface: rgb(255 255 255);
            --report-surface-alt: rgb(248 250 252);
            --report-border: rgb(226 232 240);
            --report-border-strong: rgb(203 213 225);
            --report-text: rgb(15 23 42);
            --report-muted: rgb(100 116 139);
            --report-accent: rgb(13 148 136);
            --report-accent-soft: rgb(236 253 245);
            --report-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
            --report-table-head-start: rgb(15 23 42);
            --report-table-head-end: rgb(30 41 59);
            --report-table-row-hover: rgb(248 250 252);
            --report-table-footer-bg: rgba(16, 185, 129, 0.06);
            isolation: isolate;
            position: relative;
        }

        .dark .report-dashboard {
            --report-surface: rgb(15 23 42);
            --report-surface-alt: rgb(2 6 23);
            --report-border: rgb(30 41 59);
            --report-border-strong: rgb(51 65 85);
            --report-text: rgb(226 232 240);
            --report-muted: rgb(148 163 184);
            --report-accent: rgb(45 212 191);
            --report-accent-soft: rgb(15 118 110 / 0.18);
            --report-shadow: 0 16px 40px rgba(2, 6, 23, 0.38);
            --report-table-head-start: rgb(15 23 42);
            --report-table-head-end: rgb(30 41 59);
            --report-table-row-hover: rgb(15 23 42);
            --report-table-footer-bg: rgb(6 78 59 / 0.22);
        }

        .report-dashboard::before,
        .report-dashboard::after {
            content: '';
            pointer-events: none;
            position: absolute;
            border-radius: 9999px;
            filter: blur(28px);
            opacity: 0.7;
            z-index: -1;
        }

        .report-dashboard::before {
            inset: -2rem auto auto -4rem;
            width: 16rem;
            height: 16rem;
            background: radial-gradient(circle, rgba(13, 148, 136, 0.16), transparent 68%);
        }

        .report-dashboard::after {
            inset: 8rem -5rem auto auto;
            width: 18rem;
            height: 18rem;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.08), transparent 68%);
        }

        .dark .report-dashboard::before {
            background: radial-gradient(circle, rgba(45, 212, 191, 0.18), transparent 68%);
        }

        .dark .report-dashboard::after {
            background: radial-gradient(circle, rgba(148, 163, 184, 0.12), transparent 68%);
        }

        .report-surface {
            border: 1px solid var(--report-border);
            border-radius: 1.75rem;
            background: linear-gradient(180deg, var(--report-surface), var(--report-surface-alt));
            box-shadow: var(--report-shadow);
            color: var(--report-text);
        }

        .report-hero {
            display: grid;
            gap: 1.5rem;
            grid-template-columns: minmax(0, 1.25fr) minmax(280px, 0.75fr);
            overflow: hidden;
            padding: 1.5rem;
            position: relative;
        }

        .report-hero::after {
            content: '';
            pointer-events: none;
            position: absolute;
            inset: -4rem -4rem auto auto;
            width: 12rem;
            height: 12rem;
            border-radius: 9999px;
            background: radial-gradient(circle, var(--report-accent-soft), transparent 70%);
            opacity: 0.85;
        }

        .report-hero__intro {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            position: relative;
            z-index: 1;
        }

        .report-hero__copy {
            display: grid;
            gap: 0.5rem;
        }

        .report-badge {
            align-self: flex-start;
            border: 1px solid var(--report-accent);
            border-radius: 9999px;
            background: var(--report-accent-soft);
            color: var(--report-accent);
            display: inline-flex;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            line-height: 1;
            padding: 0.65rem 1rem;
            text-transform: uppercase;
        }

        .report-hero__title {
            color: var(--report-text);
            font-size: clamp(1.6rem, 2vw, 2.3rem);
            font-weight: 800;
            letter-spacing: -0.045em;
            line-height: 1.05;
            margin: 0;
        }

        .report-hero__description {
            color: var(--report-muted);
            font-size: 0.98rem;
            line-height: 1.7;
            margin: 0;
            max-width: 52rem;
        }

        .report-meta {
            align-self: start;
            border: 1px solid var(--report-border);
            border-radius: 1.35rem;
            background: var(--report-surface);
            box-shadow: var(--report-shadow);
            display: grid;
            gap: 0.85rem;
            min-width: 280px;
            padding: 1rem;
            position: relative;
            z-index: 1;
        }

        .report-meta__row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .report-meta__label {
            color: var(--report-muted);
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        .report-meta__value {
            color: var(--report-text);
            font-size: 0.95rem;
            font-weight: 700;
            min-width: 0;
            overflow-wrap: anywhere;
            text-align: right;
        }

        .report-hero__note {
            color: var(--report-muted);
            font-size: 0.95rem;
            line-height: 1.7;
            margin: 0;
            grid-column: 1 / -1;
            position: relative;
            z-index: 1;
        }

        .report-filters {
            padding: 1.25rem;
        }

        @media (max-width: 1024px) {
            .report-hero {
                grid-template-columns: 1fr;
            }

            .report-meta {
                min-width: 0;
            }
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
            --report-table-head-start: rgb(15 23 42);
            --report-table-head-end: rgb(30 41 59);
            --report-table-row-hover: rgb(248 250 252);
            --report-table-footer-bg: rgba(16, 185, 129, 0.06);
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
            --report-table-head-start: rgb(15 23 42);
            --report-table-head-end: rgb(30 41 59);
            --report-table-row-hover: rgb(15 23 42);
            --report-table-footer-bg: rgb(6 78 59 / 0.22);
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
            background: linear-gradient(135deg, var(--report-table-head-start), var(--report-table-head-end));
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

        .report-table__row:hover .report-table__cell {
            background: var(--report-table-row-hover);
        }

        .report-table__empty {
            color: var(--report-muted);
            padding: 1rem;
            text-align: center;
        }

        .report-table__footer {
            background: var(--report-table-footer-bg);
            font-weight: 700;
        }

        .report-table__footer--label {
            color: var(--report-text);
        }

        .report-table__footer--muted {
            color: var(--report-text);
        }
    </style>
@endonce