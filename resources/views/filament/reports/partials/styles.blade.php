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
            --report-border: rgba(22, 101, 52, 0.12);
            --report-border-strong: rgba(22, 101, 52, 0.20);
            --report-text: rgb(15 23 42);
            --report-muted: rgb(100 116 139);
            --report-accent: rgb(22 163 74);
            --report-accent-soft: rgba(22, 163, 74, 0.10);
            --report-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
            --report-table-head-start: rgb(22 101 52);
            --report-table-head-end: rgb(21 128 61);
            --report-table-row-hover: rgb(240 253 244);
            --report-table-footer-bg: rgba(22, 163, 74, 0.08);
            isolation: isolate;
            position: relative;
        }

        .dark .report-dashboard {
            --report-surface: rgb(15 23 42);
            --report-surface-alt: rgb(2 6 23);
            --report-border: rgba(52, 211, 153, 0.18);
            --report-border-strong: rgba(52, 211, 153, 0.25);
            --report-text: rgb(226 232 240);
            --report-muted: rgb(148 163 184);
            --report-accent: rgb(52 211 153);
            --report-accent-soft: rgba(16, 185, 129, 0.18);
            --report-shadow: 0 16px 40px rgba(2, 6, 23, 0.35);
            --report-table-head-start: rgb(22 101 52);
            --report-table-head-end: rgb(21 128 61);
            --report-table-row-hover: rgba(16, 185, 129, 0.08);
            --report-table-footer-bg: rgba(16, 185, 129, 0.14);
        }

        .report-dashboard::before,
        .report-dashboard::after {
            content: '';
            pointer-events: none;
            position: absolute;
            border-radius: 9999px;
            filter: blur(28px);
            opacity: 0.55;
            z-index: -1;
        }

        .report-dashboard::before {
            inset: -2rem auto auto -4rem;
            width: 16rem;
            height: 16rem;
            background: radial-gradient(circle, rgba(22, 163, 74, 0.12), transparent 68%);
        }

        .report-dashboard::after {
            inset: 8rem -5rem auto auto;
            width: 18rem;
            height: 18rem;
            background: radial-gradient(circle, rgba(34, 197, 94, 0.10), transparent 68%);
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
            background:
                radial-gradient(circle at top right, rgba(255,255,255,.10), transparent 18%),
                radial-gradient(circle at 30% 100%, rgba(255,255,255,.06), transparent 14%),
                linear-gradient(135deg, rgb(22 101 52), rgb(21 128 61), rgb(20 83 45));
            color: white;
        }

        .report-hero::after {
            content: '';
            pointer-events: none;
            position: absolute;
            inset: -4rem -4rem auto auto;
            width: 12rem;
            height: 12rem;
            border-radius: 9999px;
            background: radial-gradient(circle, rgba(255,255,255,.10), transparent 70%);
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
            border: 1px solid rgba(255,255,255,.20);
            border-radius: 9999px;
            background: rgba(255,255,255,.10);
            color: rgb(220 252 231);
            display: inline-flex;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            line-height: 1;
            padding: 0.65rem 1rem;
            text-transform: uppercase;
        }

        .report-hero__title {
            color: white;
            font-size: clamp(1.6rem, 2vw, 2.3rem);
            font-weight: 800;
            letter-spacing: -0.045em;
            line-height: 1.05;
            margin: 0;
        }

        .report-hero__description,
        .report-hero__note {
            color: rgba(220, 252, 231, 0.85);
            font-size: 0.98rem;
            line-height: 1.7;
            margin: 0;
        }

        .report-meta {
            align-self: start;
            border: 1px solid rgba(255,255,255,.14);
            border-radius: 1.35rem;
            background: rgba(0,0,0,.10);
            box-shadow: 0 10px 30px rgba(0,0,0,.12);
            display: grid;
            gap: 0.85rem;
            min-width: 280px;
            padding: 1rem;
            position: relative;
            z-index: 1;
            backdrop-filter: blur(8px);
        }

        .report-meta__row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .report-meta__label {
            color: rgba(220,252,231,.75);
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        .report-meta__value {
            color: white;
            font-size: 0.95rem;
            font-weight: 700;
            min-width: 0;
            overflow-wrap: anywhere;
            text-align: right;
        }

        .report-filters {
            padding: 1.25rem;
        }

        .report-panel {
            border: 1px solid var(--report-border);
            border-radius: 1.5rem;
            background: var(--report-surface);
            box-shadow: var(--report-shadow);
            overflow: hidden;
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
            border: 1px solid rgba(22, 163, 74, 0.18);
            border-radius: 1rem;
            background: linear-gradient(135deg, rgb(6 78 59), rgb(20 83 45));
            padding: 1rem;
            box-shadow: 0 14px 30px rgba(21, 128, 61, 0.12);
        }

        .report-summary-label {
            color: rgb(187 247 208);
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        .report-summary-value {
            color: white;
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

        @media (max-width: 1024px) {
            .report-hero {
                grid-template-columns: 1fr;
            }

            .report-meta {
                min-width: 0;
            }
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

        .report-table__footer--label,
        .report-table__footer--muted {
            color: var(--report-text);
        }
    </style>
@endonce