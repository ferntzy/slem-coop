<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 18px 22px; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #0f172a;
            font-size: 11px;
            line-height: 1.45;
            margin: 0;
        }
        .header {
            border-radius: 16px;
            background: linear-gradient(135deg, #052e16 0%, #166534 100%);
            color: white;
            padding: 18px 20px;
            margin-bottom: 16px;
        }
        .title { font-size: 20px; font-weight: 700; margin: 0; }
        .subtitle { margin: 6px 0 0; color: rgba(255,255,255,0.78); font-size: 11px; }
        .meta-grid { display: table; width: 100%; margin-top: 14px; }
        .meta-cell { display: table-cell; width: 50%; vertical-align: top; }
        .meta-box { border: 1px solid rgba(255,255,255,0.15); background: rgba(255,255,255,0.06); border-radius: 12px; padding: 12px 14px; }
        .meta-row { display: table; width: 100%; table-layout: fixed; margin-top: 4px; }
        .meta-row:first-child { margin-top: 0; }
        .label, .value { display: table-cell; vertical-align: top; }
        .label { width: 42%; color: rgba(255,255,255,0.68); }
        .value { width: 58%; font-weight: 600; color: white; text-align: right; }
        .report-panel {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
            background: white;
            margin-bottom: 16px;
        }
        .report-panel__body { padding: 14px; }
        .report-summary-grid {
            display: block;
            margin: 0 0 16px;
        }
        .report-summary-card { display: inline-block; width: 24%; min-width: 150px; vertical-align: top; margin-right: 1%; margin-bottom: 10px; }
        .report-summary-label { font-size: 9px; letter-spacing: 0.16em; text-transform: uppercase; color: #64748b; }
        .report-summary-value { font-size: 18px; font-weight: 700; margin-top: 6px; color: #0f172a; }
        .report-table {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            overflow: hidden;
            margin-bottom: 16px;
            background: white;
        }
        .report-table__heading {
            margin: 0;
            padding: 12px 14px;
            background: #f8fafc;
            font-size: 12px;
            font-weight: 700;
        }
        .report-table__scroll { overflow-x: auto; }
        .report-table__table { width: 100%; border-collapse: collapse; }
        .report-table__head {
            background: #0f172a;
            color: white;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 8px 10px;
            text-align: left;
        }
        .report-table__head--right,
        .report-table__cell--right,
        .report-table__footer--right { text-align: right; }
        .report-table__cell, .report-table__footer {
            border-top: 1px solid #e2e8f0;
            padding: 8px 10px;
            vertical-align: top;
        }
        .report-table__row:nth-child(even) .report-table__cell { background: #f8fafc; }
        .report-table__empty {
            color: #64748b;
            padding: 14px 10px;
            text-align: center;
        }
        .report-table__footer {
            font-weight: 700;
            background: #ecfdf5;
        }
        .report-table__footer--label { font-weight: 700; }
        .report-signature-grid {
            display: table;
            width: 100%;
            table-layout: fixed;
            margin-top: 16px;
        }
        .report-signature-card {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 14px;
        }
        .report-signature-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.16em;
            color: #64748b;
        }
        .report-signature-line {
            margin-top: 28px;
            border-top: 1px solid #94a3b8;
            padding-top: 8px;
            font-weight: 600;
        }
        .footer { margin-top: 18px; display: table; width: 100%; table-layout: fixed; }
        .footer-cell { display: table-cell; width: 50%; vertical-align: top; padding-right: 14px; }
        .footer-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.16em;
            color: #64748b;
        }
        .footer-line {
            margin-top: 28px;
            border-top: 1px solid #94a3b8;
            padding-top: 8px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    @include('filament.reports.partials.header', ['report' => $report])

    @include('filament.reports.partials.panel', ['report' => $report])
</body>
</html>