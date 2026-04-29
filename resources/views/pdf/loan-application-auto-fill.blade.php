<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Loan Application</title>
    <style>

        /* ══════════════════════════════════════════════════════════════
           @PAGE — THE ONLY CORRECT WAY TO SET PRINT/PDF MARGINS
           Never use body margin/padding for this purpose.
        ══════════════════════════════════════════════════════════════ */
        @page {
            size: 8.5in 12.5in;
            margin: 0.5in;
        }

        /* Named pages for first / rest if needed (optional, DOMPDF 2.x+) */
        @page :first { margin: 0.5in; }

        /* ══════════════════════════════════════════════════════════════
           RESET — body margin/padding MUST be 0; @page handles margins
        ══════════════════════════════════════════════════════════════ */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            margin: 0 !important;
            padding: 0 !important;
            width: 100%;
            font-family: DejaVu Sans, sans-serif; /* DOMPDF needs DejaVu for UTF-8/₱ */
            font-size: 8px;
            color: #000;
            line-height: 1.3;
        }

        /* ══════════════════════════════════════════════════════════════
           PAGE BREAK HELPERS
           Always pair CSS2 (page-break-*) + CSS3 (break-*) for
           maximum compatibility across DOMPDF, wkhtmltopdf, Puppeteer,
           and browser Print-to-PDF.
        ══════════════════════════════════════════════════════════════ */
        .page-break {
            page-break-after : always; /* CSS2 — DOMPDF / wkhtmltopdf */
            break-after       : page;  /* CSS3 — Chrome / Firefox      */
            height: 0;
            display: block;
            visibility: hidden;
        }

        /* Prevent any block from splitting across pages */
        .no-break {
            page-break-inside : avoid;
            break-inside       : avoid;
        }

        /* Force a new page BEFORE this element */
        .page-start {
            page-break-before : always;
            break-before       : page;
        }

        /* Prevent table rows from splitting mid-row */
        tr {
            page-break-inside : avoid;
            break-inside       : avoid;
        }

        /* ══════════════════════════════════════════════════════════════
           GLOBAL ELEMENT STYLES
        ══════════════════════════════════════════════════════════════ */
        table { width: 100%; border-collapse: collapse; }
        td, th { vertical-align: top; }

        .b  { border: 0.5px solid #000; }
        .br { border-right: 0.5px solid #000; }
        .bb { border-bottom: 0.5px solid #000; }
        .bt { border-top: 0.5px solid #000; }
        .bl { border-left: 0.5px solid #000; }

        .c { padding: 0.6mm 1mm; }

        .lbl {
            font-size: 6px;
            color: #333;
        }
        .val {
            font-size: 7.5px;
            font-weight: bold;
            border-bottom: 0.4px solid #000;
            min-height: 3mm;
            padding-bottom: 0.1mm;
        }

        /* ── Global green section header ── */
        .sh {
            background: #2f7d32;
            color: #fff;
            font-size: 7px;
            font-weight: bold;
            padding: 0.7mm 1mm;
        }

        .sig-line {
            border-top: 0.5px solid #000;
            padding-top: 0.5mm;
            font-size: 6px;
            text-align: center;
            margin-top: 4mm;
        }

        /* Compress spacing between form tables */
        .form-outer > table { margin-bottom: 0; margin-top: 0; }
        .form-outer table.no-break { margin-bottom: 0; margin-top: 0; }
        .form-outer table.bt { margin-top: 0; }
        
        /* Reduce padding in inline div with application statement */
        .form-outer > div[style] { padding: 1.2mm 1.5mm !important; }
        
        /* Compress AO Actions section */
        .form-outer > table:last-of-type div[style*="font-size:7.5px"] {
            font-size: 7px !important;
            margin-bottom: 0.3mm !important;
        }
        .form-outer > table:last-of-type div[style*="margin-bottom:1mm"] {
            margin-bottom: 0.5mm !important;
        }

        .footer-pg {
            text-align: center;
            font-size: 6.5px;
            color: #666;
            margin-top: 1.5mm;
            border-top: 0.3px solid #ccc;
            padding-top: 0.8mm;
            /* Stick to bottom of every page */
            page-break-before : avoid;
            break-before       : avoid;
        }

        /* ── Badges ── */
        .badge { display: inline-block; padding: 0.3mm 1.5mm; border-radius: 1mm; font-size: 7px; font-weight: bold; }
        .bs { background: #d1fae5; color: #065f46; }
        .bw { background: #fef3c7; color: #92400e; }
        .bd { background: #fee2e2; color: #991b1b; }
        .bi { background: #dbeafe; color: #1e40af; }
        .bg { background: #f3f4f6; color: #374151; }

        /* ══════════════════════════════════════════════════════════════
           PAGE 1 — LOAN APPLICATION FORM
        ══════════════════════════════════════════════════════════════ */
        .loan-application-page { line-height: 1.1; }

        .form-outer {
            border: 1.5px solid #000;
            width: 100%;
            page-break-inside: auto; /* Allow content to flow across pages */
        }

        .form-header {
            padding: 1mm 1.5mm 0.7mm 1.5mm;
            border-bottom: 3px solid #2f7d32;
        }

        .form-header-table { width: 100%; border-collapse: collapse; }

        .form-logo-cell {
            width: 28mm;
            padding-right: 1mm;
            vertical-align: middle;
        }

        .form-logo  { display: block; width: 26mm; height: auto; }
        .form-branding { vertical-align: middle; }

        .form-coop-name {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            color: #1b5e20;
        }

        .form-branch { font-size: 7px; margin-top: 0.2mm; }

        .form-title {
            text-align: center;
            font-size: 9.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0.7mm 0;
            background: #2f7d32;
            color: #fff;
        }

        /* ══════════════════════════════════════════════════════════════
           PAGES 2, 3, 4 — SHARED LOGO HEADER
        ══════════════════════════════════════════════════════════════ */
        .page-logo-header {
            border-bottom: 3px solid #2f7d32;
            padding: 1.5mm 2mm 1mm 2mm;
            margin-bottom: 3mm;
            width: 100%;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .page-logo-header table { table-layout: fixed; }

        .plh-logo-cell { width: 34mm; padding-right: 2mm; vertical-align: middle; }
        .plh-logo      { display: block; width: 32mm; height: auto; }
        .plh-branding  { vertical-align: middle; }

        .plh-name {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #1b5e20;
        }

        .plh-sub { font-size: 7.5px; margin-top: 0.3mm; color: #000; }

        /* ══════════════════════════════════════════════════════════════
           PAGE 2 — PROMISSORY NOTE
        ══════════════════════════════════════════════════════════════ */
        .pn-wrapper {
            width: 90%;
            padding: 2mm 3mm;
            box-sizing: border-box;
        }

        .pn-title {
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 2mm;
            color: #1b5e20;
            border-bottom: 2px solid #2f7d32;
            padding-bottom: 1mm;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .pn-body {
            font-size: 7.5px;
            text-align: justify;
            margin-bottom: 2mm;
            line-height: 1.5;
        }

        .pn-body p {
            margin-bottom: 2mm;
            text-align: justify;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .pn-underline {
            border-bottom: 0.5px solid #000;
            display: inline-block;
            min-width: 12mm;
            font-weight: bold;
        }

        .pn-sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2mm;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .pn-sig-table td { padding: 0 4mm; vertical-align: bottom; }

        .pn-sig-line {
            border-top: 0.5px solid #2f7d32;
            padding-top: 0.8mm;
            font-size: 6.5px;
            text-align: center;
            margin-top: 10mm;
        }

        .pn-sig-sub { font-size: 6.5px; margin-top: 1mm; }

        .pn-comaker-title {
            font-size: 8px;
            font-weight: bold;
            margin: 2mm 0 1mm 0;
            color: #1b5e20;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .pn-presence {
            text-align: center;
            font-size: 8px;
            font-weight: bold;
            margin-top: 4mm;
            margin-bottom: 2mm;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* ══════════════════════════════════════════════════════════════
           PAGE 3 — INTERNAL EVALUATION SHEET
        ══════════════════════════════════════════════════════════════ */
        .eval-header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding: 2mm 2mm;
            margin-bottom: 3mm;
            background: #2f7d32;
            color: #fff;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .notes-box {
            border: 0.5px solid #999;
            padding: 1.5mm 2mm;
            min-height: 14mm;
            font-size: 8px;
            background: #fafafa;
        }

        /* ══════════════════════════════════════════════════════════════
           PAGE 4 — AMORTIZATION SCHEDULE
        ══════════════════════════════════════════════════════════════ */
        table.amort {
            page-break-inside: auto; /* allow rows to break across pages if schedule is very long */
        }

        table.amort th {
            background: #2f7d32;
            color: #fff;
            padding: 1.5mm 2mm;
            text-align: center;
            font-weight: bold;
            border: 0.5px solid #1b5e20;
            font-size: 7.5px;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        table.amort thead {
            /* Repeat header on every new page if browser/renderer supports it */
            display: table-header-group;
        }

        table.amort td {
            padding: 1.2mm 2mm;
            text-align: center;
            border: 0.3px solid #ccc;
            font-size: 7px;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        table.amort tr:nth-child(even) td { background: #f1f8f1; }

        table.amort tfoot {
            display: table-footer-group;
        }

        table.amort tfoot td {
            font-weight: bold;
            background: #e8f5e9;
            border-top: 1px solid #2f7d32;
            color: #1b5e20;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .amort-placeholder {
            border: 0.5px dashed #a5d6a7;
            padding: 8mm 4mm;
            text-align: center;
            color: #388e3c;
            font-size: 8px;
            font-style: italic;
            background: #f9fdf9;
            margin-top: 2mm;
        }

        .sig-line-green { border-top: 0.5px solid #2f7d32; }

    </style>
</head>
<body>

@php
    $fmt   = fn($v) => $v ?? '—';
    $money = fn($v) => '₱' . number_format((float)$v, 2);
    $date  = fn($v, $f='F j, Y') => $v ? \Carbon\Carbon::parse($v)->format($f) : '—';
    $dated = fn($v) => $v ? \Carbon\Carbon::parse($v)->format('m/d/Y') : '—';

    // Cash Flow
    $totalIncome    = $loanApplication->cashflows()->where('row_type', 'income')->sum('amount');
    $totalExpenses  = $loanApplication->cashflows()->whereIn('row_type', ['expense', 'debt'])->sum('amount');
    $netCashFlow    = $totalIncome - $totalExpenses;
    $allowedPayment = $netCashFlow * 0.40;

    // Capacity
    $principal       = (float) $loanApplication->amount_requested;
    $term            = max((int) $loanApplication->term_months, 1);
    $interestRate    = (float) ($loanApplication->loanAccount?->interest_rate ?? $loanApplication->type?->max_interest_rate ?? 0);
    $proposedPayment = ($principal / $term) + ($principal * ($interestRate / 100) / 12);

    $capacityStatus = match(true) {
        $allowedPayment >= $proposedPayment         => 'Safe',
        $allowedPayment >= ($proposedPayment * 0.8) => 'Slightly Risky',
        default                                     => 'High Risk',
    };
    $capacityClass = match($capacityStatus) { 'Safe' => 'bs', 'Slightly Risky' => 'bw', default => 'bd' };

    $loanStatusClass = match($loanApplication->status) {
        'Pending' => 'bw', 'Under Review' => 'bi', 'Approved' => 'bs', 'Rejected' => 'bd', default => 'bg'
    };
    $acctStatusClass = match($loanApplication->loanAccount?->status) {
        'Active' => 'bs', 'Closed' => 'bg', 'Defaulted' => 'bd', default => 'bg'
    };
    $collateralStatus = (float)$loanApplication->amount_requested <= 15000
        ? 'Not Required'
        : ($loanApplication->collateral_status ?? '—');
    $collateralClass = (float)$loanApplication->amount_requested <= 15000
        ? 'bg'
        : match($loanApplication->collateral_status) {
            'Approved' => 'bs', 'Rejected' => 'bd', 'Pending Verification' => 'bw', default => 'bg'
        };

    $coMaker1 = $coMakers->get(0);
    $coMaker2 = $coMakers->get(1);

    // Amortization
    $schedule = null;
    if ($loanApplication->loanAccount?->release_date) {
        try {
            $schedule = app(\App\Services\LoanAmortizationService::class)->generate(
                loanAmount: $principal,
                monthlyInterestRatePercent: $interestRate,
                termMonths: $term,
                releaseDate: $loanApplication->loanAccount->release_date,
            );
        } catch (\Throwable $e) { $schedule = null; }
    }

    $totalPrincipal = 0; $totalInterest = 0; $totalPayment = 0;
    if ($schedule) {
        foreach ($schedule as $r) {
            $totalPrincipal += (float)($r['principal'] ?? 0);
            $totalInterest  += (float)($r['interest'] ?? 0);
            $totalPayment   += (float)($r['payment'] ?? $r['monthly_payment'] ?? 0);
        }
    }

    $totalPages = 4;

    $releaseDate  = $loanApplication->loanAccount?->release_date;
    $maturityDate = $loanApplication->loanAccount?->maturity_date;
    $monthlyAmort = $loanApplication->loanAccount?->monthly_amortization;
    $firstPayDate = $releaseDate ? \Carbon\Carbon::parse($releaseDate)->addMonth()->format('F j, Y') : '___________';
    $releaseYear  = $releaseDate ? \Carbon\Carbon::parse($releaseDate)->format('Y') : '20__';

    $logoPath = public_path('logo.png');
    $logoData = file_exists($logoPath)
        ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath))
        : null;
@endphp


{{-- ═══════════════════════════════════════════════════════
     PAGE 1 — LOAN APPLICATION FORM
     ═══════════════════════════════════════════════════════ --}}
<div class="loan-application-page">
<div class="form-outer">

    {{-- Cooperative Header --}}
    <div class="form-header no-break">
        <table class="form-header-table">
            <tr>
                <td class="form-logo-cell">
                    @if($logoData)
                        <img src="{{ $logoData }}" alt="SLEMCOOP Logo" class="form-logo">
                    @endif
                </td>
                <td class="form-branding">
                    <div class="form-coop-name">Southern Leyte Employees Multi-Purpose Cooperative</div>
                    <div class="form-branch">{{ $fmt($member?->branch?->name ?? '') }} Branch</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="form-title">Loan Application</div>

    {{-- Loan Details --}}
    <table class="no-break">
        <tr>
            <td class="b c" style="width:25%">
                <div class="lbl">Loan Amount</div>
                <div class="val">{{ $money($loanApplication->amount_requested) }}</div>
            </td>
            <td class="b c" style="width:25%">
                <div class="lbl">Loan Type</div>
                <div class="val">{{ $fmt($loanApplication->type?->name) }}</div>
            </td>
            <td class="b c" style="width:25%">
                <div class="lbl">Term (Months)</div>
                <div class="val">{{ $fmt($loanApplication->term_months) }}</div>
            </td>
            <td class="b c" style="width:25%">
                <div class="lbl">Mode of Payment</div>
                <div class="val">Monthly</div>
            </td>
        </tr>
        <tr>
            <td class="b c" colspan="3">
                <div class="lbl">Purpose</div>
                <div class="val">{{ $fmt($loanApplication->purpose) }}</div>
            </td>
            <td class="b c">
                <div class="lbl">Interest Rate</div>
                <div class="val">{{ $interestRate }}% / month</div>
            </td>
        </tr>
    </table>

    {{-- Applicant's Name --}}
    <table class="bt no-break">
        <tr><td class="b c sh" colspan="6">APPLICANT'S NAME</td></tr>
        <tr>
            <td class="b c" style="width:22%">
                <div class="lbl">Family Name</div>
                <div class="val">{{ $fmt($profile?->last_name) }}</div>
            </td>
            <td class="b c" style="width:22%">
                <div class="lbl">Given Name</div>
                <div class="val">{{ $fmt($profile?->first_name) }}</div>
            </td>
            <td class="b c" style="width:20%">
                <div class="lbl">Middle Name</div>
                <div class="val">{{ $fmt($profile?->middle_name) }}</div>
            </td>
            <td class="b c" style="width:12%">
                <div class="lbl">Civil Status</div>
                <div class="val">{{ $fmt($profile?->civil_status) }}</div>
            </td>
            <td class="b c" style="width:12%">
                <div class="lbl">T.I.N.</div>
                <div class="val">{{ $fmt($profile?->tin) }}</div>
            </td>
            <td class="b c" style="width:12%">
                <div class="lbl">Mobile/Tel.</div>
                <div class="val">{{ $fmt($profile?->mobile_number) }}</div>
            </td>
        </tr>
        <tr>
            <td class="b c" style="width:10%">
                <div class="lbl">Age</div>
                <div class="val">{{ $profile?->birthdate ? \Carbon\Carbon::parse($profile->birthdate)->age : '—' }}</div>
            </td>
            <td class="b c" style="width:14%">
                <div class="lbl">Birthday</div>
                <div class="val">{{ $dated($profile?->birthdate) }}</div>
            </td>
            <td class="b c" style="width:14%">
                <div class="lbl">Years in the Coop</div>
                <div class="val">{{ $fmt($member?->years_in_coop) }}</div>
            </td>
            <td class="b c" style="width:15%">
                <div class="lbl">No. of Dependents</div>
                <div class="val">{{ $fmt($member?->dependents_count) }}</div>
            </td>
            <td class="b c" style="width:22%">
                <div class="lbl">No. of Children in School</div>
                <div class="val">{{ $fmt($member?->children_in_school_count) }}</div>
            </td>
            <td class="b c" style="width:25%">
                <div class="lbl">Source of Income</div>
                <div class="val">{{ $fmt($member?->occupation) }}</div>
            </td>
        </tr>
    </table>

    {{-- Present Address --}}
    <table class="bt no-break">
        <tr>
            <td class="b c" style="width:8%">
                <div class="lbl" style="font-weight:bold;">Present Address</div>
            </td>
            <td class="b c" style="width:32%">
                <div class="lbl">Street/Purok/Barangay</div>
                <div class="val">{{ $fmt($profile?->address) }}</div>
            </td>
            <td class="b c" style="width:22%">
                <div class="lbl">City/Municipality</div>
                <div class="val">&nbsp;</div>
            </td>
            <td class="b c" style="width:18%">
                <div class="lbl">Province</div>
                <div class="val">&nbsp;</div>
            </td>
            <td class="b c" style="width:20%">
                <div class="lbl">Telephone/Contact Nos.</div>
                <div class="val">{{ $fmt($profile?->mobile_number) }}</div>
            </td>
        </tr>
    </table>

    {{-- Employer --}}
    <table class="bt no-break">
        <tr>
            <td class="b c" style="width:30%">
                <div class="lbl">Name of Employer/Business</div>
                <div class="val">{{ $fmt($member?->employer_name) }}</div>
            </td>
            <td class="b c" style="width:25%">
                <div class="lbl">Address</div>
                <div class="val">&nbsp;</div>
            </td>
            <td class="b c" style="width:20%">
                <div class="lbl">Years in Business or Employment</div>
                <div class="val">{{ $fmt($member?->years_in_coop) }}</div>
            </td>
            <td class="b c" style="width:15%">
                <div class="lbl">Employment Type</div>
                <div class="val">{{ $fmt($member?->employment_info) }}</div>
            </td>
            <td class="b c" style="width:10%">
                <div class="lbl">Occupation</div>
                <div class="val">{{ $fmt($member?->occupation) }}</div>
            </td>
        </tr>
    </table>

    {{-- Spouse --}}
    <table class="bt no-break">
        <tr><td class="b c sh" colspan="6">NAME OF SPOUSE</td></tr>
        <tr>
            <td class="b c" style="width:20%">
                <div class="lbl">Family Name</div>
                <div class="val">{{ $spouse ? (array_reverse(explode(' ', $spouse->full_name ?? '—'))[0] ?? '—') : '—' }}</div>
            </td>
            <td class="b c" style="width:20%">
                <div class="lbl">Given Name</div>
                <div class="val">{{ $spouse ? explode(' ', $spouse->full_name ?? '—')[0] : '—' }}</div>
            </td>
            <td class="b c" style="width:8%">
                <div class="lbl">M.I.</div>
                <div class="val">&nbsp;</div>
            </td>
            <td class="b c" style="width:13%">
                <div class="lbl">T.I.N.</div>
                <div class="val">{{ $fmt($spouse?->tin) }}</div>
            </td>
            <td class="b c" style="width:19%">
                <div class="lbl">Source of Income</div>
                <div class="val">{{ $fmt($spouse?->source_of_income) }}</div>
            </td>
            <td class="b c" style="width:20%">
                <div class="lbl">Occupation</div>
                <div class="val">{{ $fmt($spouse?->occupation) }}</div>
            </td>
        </tr>
        <tr>
            <td class="b c" style="width:10%">
                <div class="lbl">Age</div>
                <div class="val">{{ $spouse?->birthdate ? \Carbon\Carbon::parse($spouse->birthdate)->age : '—' }}</div>
            </td>
            <td class="b c" style="width:14%">
                <div class="lbl">Birthday</div>
                <div class="val">{{ $dated($spouse?->birthdate) }}</div>
            </td>
            <td class="b c" style="width:30%" colspan="2">
                <div class="lbl">Name of Employer/Business</div>
                <div class="val">{{ $fmt($spouse?->employer_name) }}</div>
            </td>
            <td class="b c" colspan="2">
                <div class="lbl">Address</div>
                <div class="val">{{ $fmt($spouse?->employer_address) }}</div>
            </td>
        </tr>
    </table>

    {{-- Application Statement --}}
    <div class="no-break" style="padding:1.2mm 1.5mm; border-top:0.5px solid #000; font-size:7.5px; font-style:italic;">
        I/we hereby apply for a loan in the amount of
        <span style="font-weight:bold; border-bottom:0.5px solid #000;">{{ $money($loanApplication->amount_requested) }}</span>
        <strong>payable in accordance with the terms and conditions as may be approved.</strong>
    </div>

    {{-- Applicant Signatures --}}
    <table class="bt no-break">
        <tr>
            <td class="b c" style="width:50%; height:8mm; vertical-align:bottom;">
                <div class="sig-line">Applicant's Printed Name &amp; Signature</div>
            </td>
            <td class="b c" style="width:50%; height:8mm; vertical-align:bottom;">
                <div class="lbl">With my marital consent: Spouse' Printed Name &amp; Signature</div>
                <div class="sig-line">&nbsp;</div>
            </td>
        </tr>
    </table>

    {{-- Co-Makers --}}
    <table class="bt no-break">
        <tr><td class="b c sh" colspan="4">CO-MAKERS</td></tr>
        <tr>
            <td class="b c br" colspan="2" style="width:50%">
                <div class="lbl" style="font-weight:bold; color:#000;">Co-Maker 1</div>
            </td>
            <td class="b c" colspan="2" style="width:50%">
                <div class="lbl" style="font-weight:bold; color:#000;">Co-Maker 2</div>
            </td>
        </tr>
        <tr>
            <td class="b c" colspan="2" style="width:50%; border-right:1px solid #000;">
                <div class="lbl">Printed Name &amp; Signature</div>
                <div class="val" style="min-height:5mm;">{{ $fmt($coMaker1?->full_name) }}</div>
            </td>
            <td class="b c" colspan="2">
                <div class="lbl">Printed Name &amp; Signature</div>
                <div class="val" style="min-height:5mm;">{{ $fmt($coMaker2?->full_name) }}</div>
            </td>
        </tr>
        <tr>
            <td class="b c" style="width:30%; border-right:0.5px solid #ccc;">
                <div class="lbl">Residential Address</div>
                <div class="val">{{ $fmt($coMaker1?->address) }}</div>
            </td>
            <td class="b c" style="width:20%; border-right:1px solid #000;">
                <div class="lbl">Contact Nos.</div>
                <div class="val">{{ $fmt($coMaker1?->contact_number) }}</div>
            </td>
            <td class="b c" style="width:30%; border-right:0.5px solid #ccc;">
                <div class="lbl">Residential Address</div>
                <div class="val">{{ $fmt($coMaker2?->address) }}</div>
            </td>
            <td class="b c" style="width:20%">
                <div class="lbl">Contact Nos.</div>
                <div class="val">{{ $fmt($coMaker2?->contact_number) }}</div>
            </td>
        </tr>
        <tr>
            <td class="b c" style="border-right:0.5px solid #ccc;">
                <div class="lbl">Occupation</div>
                <div class="val">{{ $fmt($coMaker1?->occupation) }}</div>
            </td>
            <td class="b c" style="border-right:1px solid #000;">
                <div class="lbl">Relationship to Borrower</div>
                <div class="val">{{ $fmt($coMaker1?->relationship) }}</div>
            </td>
            <td class="b c" style="border-right:0.5px solid #ccc;">
                <div class="lbl">Occupation</div>
                <div class="val">{{ $fmt($coMaker2?->occupation) }}</div>
            </td>
            <td class="b c">
                <div class="lbl">Relationship to Borrower</div>
                <div class="val">{{ $fmt($coMaker2?->relationship) }}</div>
            </td>
        </tr>
    </table>

    {{-- Loan History --}}
    <table class="bt no-break">
        <tr>
            <td class="b c sh" style="width:20%">LOAN HISTORY</td>
            <td class="b c sh" style="width:18%">Amount</td>
            <td class="b c sh" style="width:10%">Cycle</td>
            <td class="b c sh" style="width:22%">Type of Loan</td>
            <td class="b c sh" style="width:20%">Loan Balance (if any)</td>
            <td class="b c sh" style="width:10%">Repayment</td>
        </tr>
        <tr>
            <td class="b c"><div class="lbl">Previous Loan</div></td>
            <td class="b c"><div class="val">&nbsp;</div></td>
            <td class="b c"><div class="val">&nbsp;</div></td>
            <td class="b c"><div class="val">&nbsp;</div></td>
            <td class="b c"><div class="val">&nbsp;</div></td>
            <td class="b c" rowspan="2"><div class="val" style="min-height:6mm;">&nbsp;</div></td>
        </tr>
        <tr>
            <td class="b c"><div class="lbl">This Loan</div></td>
            <td class="b c"><div class="val">{{ $money($loanApplication->amount_requested) }}</div></td>
            <td class="b c"><div class="val">&nbsp;</div></td>
            <td class="b c"><div class="val">{{ $fmt($loanApplication->type?->name) }}</div></td>
            <td class="b c"><div class="val">&nbsp;</div></td>
        </tr>
    </table>

    {{-- AO Actions & GM/CRECOM/BOD --}}
    <table class="bt no-break">
        <tr>
            <td class="b c" style="width:50%; vertical-align:top; border-right:1px solid #000;">
                <div style="font-size:7.5px; font-weight:bold; margin-bottom:1mm; color:#1b5e20;">AO's/BDA's, Loan Officer's, and Br. Manager's Action</div>
                @foreach([
                    "Borrower's Personal Circumstances Verified",
                    "Co-maker's Identity & Personal Circumstances Verified",
                    "Credit Dealings with Suppliers Verified",
                    "Bank & Other FI Dealings Verified",
                    "Collaterals Inspected & Appraised",
                    "Absolute Ownership of Collaterals Verified",
                    "Statement of Income & Expenditures Verified",
                    "Status of Employment Verified",
                    "Attached Cash Flow Analysis",
                    "Sufficiency of Required Capital Build-Up Verified",
                    "Sufficiency of Mutual Benefit Savings",
                    "Inter-COOP/Branch Dealings Verified",
                    "Farm Plan & Budget Verified",
                    "Project Feasibility Verified",
                ] as $item)
                <div style="font-size:7px; margin-bottom:0.3mm;">&#9634; {{ $item }}</div>
                @endforeach
                <div style="font-size:7px; font-style:italic; margin-top:0.5mm;">The Loan Unit hereby recommends this loan for &#9634; disapproval / &#9634; approval, as follows:</div>
                <table style="margin-top:0.5mm; width:100%">
                    <tr>
                        <td style="width:40%"><div class="lbl">Loan Amount</div><div class="val">&nbsp;</div></td>
                        <td style="width:30%"><div class="lbl">Term (months)</div><div class="val">&nbsp;</div></td>
                        <td style="width:30%"><div class="lbl">Int. Rate</div><div class="val">&nbsp;</div></td>
                    </tr>
                </table>
                <table style="width:100%; margin-top:3mm; border-collapse:collapse;">
                    <tr>
                        <td style="width:33%; text-align:center; border-top:0.5px solid #2f7d32; padding-top:0.3mm; font-size:6.5px;">BDA/AO/DO</td>
                        <td style="width:34%; text-align:center; border-top:0.5px solid #2f7d32; padding-top:0.3mm; font-size:6.5px;">Loan Officer</td>
                        <td style="width:33%; text-align:center; border-top:0.5px solid #2f7d32; padding-top:0.3mm; font-size:6.5px;">Branch Manager</td>
                    </tr>
                </table>
            </td>
            <td class="b c" style="width:50%; vertical-align:top;">
                <div style="border:0.5px solid #2f7d32; padding:1.5mm; margin-bottom:1.5mm;">
                    <div style="font-size:7px; font-weight:bold; margin-bottom:0.3mm; color:#1b5e20;">General Manager's Action</div>
                    <div style="font-size:7px;">I have evaluated the credit-worthiness of the applicant with his/her loan. This loan is hereby:</div>
                    <div style="font-size:7px; margin-top:0.5mm;">&#9634; Disapproved &nbsp;&nbsp; &#9634; Approved, as follows:</div>
                    <table style="width:100%; margin-top:0.5mm;"><tr>
                        <td style="width:40%"><div class="lbl">Loan Amount</div><div class="val">&nbsp;</div></td>
                        <td style="width:30%"><div class="lbl">Term (months)</div><div class="val">&nbsp;</div></td>
                        <td style="width:30%"><div class="lbl">@ /mo.</div><div class="val">&nbsp;</div></td>
                    </tr></table>
                    <div style="font-size:7px; margin-top:0.5mm;">&#9634; Endorsed for CRECOM / BOD Action.</div>
                    <div style="border-top:0.5px solid #2f7d32; margin-top:5mm; text-align:center; font-size:6.5px; padding-top:0.3mm;">GM's Signature or Authorized</div>
                </div>
                <div style="border:0.5px solid #2f7d32; padding:1.5mm; margin-bottom:1.5mm;">
                    <div style="font-size:7px; font-weight:bold; margin-bottom:0.3mm; color:#1b5e20;">CRECOM's Action</div>
                    <div style="font-size:7px;">&#9634; Disapproved &nbsp;&nbsp; &#9634; Approved, as follows:</div>
                    <table style="width:100%; margin-top:0.5mm;"><tr>
                        <td style="width:40%"><div class="lbl">Loan Amount</div><div class="val">&nbsp;</div></td>
                        <td style="width:30%"><div class="lbl">Term (months)</div><div class="val">&nbsp;</div></td>
                        <td style="width:30%"><div class="lbl">@ /mo.</div><div class="val">&nbsp;</div></td>
                    </tr></table>
                    <div style="font-size:7px; margin-top:0.5mm;">&#9634; Endorsed for BOD Action.</div>
                    <table style="width:100%; margin-top:3mm; border-collapse:collapse;"><tr>
                        <td style="width:50%; text-align:center; border-top:0.5px solid #2f7d32; font-size:6.5px; padding-top:0.3mm;">Member</td>
                        <td style="width:50%; text-align:center; border-top:0.5px solid #2f7d32; font-size:6.5px; padding-top:0.3mm;">Secretary</td>
                    </tr></table>
                    <div style="text-align:center; border-top:0.5px solid #2f7d32; margin-top:3mm; font-size:6.5px; padding-top:0.3mm;">CRECOM Chairperson</div>
                </div>
                    <div style="border:0.5px solid #2f7d32; padding:1mm 1.2mm;">
                    <div style="font-size:7px; font-weight:bold; margin-bottom:0.3mm; color:#1b5e20;">BOD's Action</div>
                    <div style="font-size:7px;">&#9634; Disapproved</div>
                    <div style="font-size:7px; margin-top:0.5mm;">&#9634; Approved as per recommendation...</div>
                    <div style="font-size:7px; margin-top:0.5mm;">At BOD Meeting on _____________, {{ $releaseYear }}</div>
                    <div style="font-size:7px; margin-top:0.5mm;">Per Resolution No. _____________, {{ $releaseYear }}</div>
                    <div style="border-top:0.5px solid #2f7d32; margin-top:5mm; text-align:center; font-size:6.5px; padding-top:0.3mm;">BOD Chairman's Signature / Secretary</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Loan Release Record --}}
    <table class="bt no-break">
        <tr><td class="b c sh" colspan="3">LOAN RELEASE RECORD</td></tr>
        <tr>
            <td class="b c" style="width:30%"><div class="lbl">Check Number</div><div class="val">&nbsp;</div></td>
            <td class="b c" style="width:30%"><div class="lbl">Date</div><div class="val">{{ $date($releaseDate) }}</div></td>
            <td class="b c" style="width:40%"><div class="lbl">Amount</div><div class="val">{{ $money($loanApplication->amount_requested) }}</div></td>
        </tr>
        <tr>
            <td class="b c" colspan="2" style="height:7mm; vertical-align:bottom;">
                <div style="border-top:0.5px solid #2f7d32; text-align:center; font-size:6px; padding-top:0.3mm;">Teller/Cashier</div>
            </td>
            <td class="b c" style="height:7mm; vertical-align:bottom;">
                <div style="border-top:0.5px solid #2f7d32; text-align:center; font-size:6px; padding-top:0.3mm;">Branch Manager</div>
            </td>
        </tr>
    </table>

</div>
</div>

<div class="footer-pg">
    {{ $fmt($profile?->full_name) }} &nbsp;|&nbsp; Application #{{ $loanApplication->loan_application_id }} &nbsp;|&nbsp; {{ now()->format('F j, Y') }} &nbsp;|&nbsp; Page 1 of 4
</div>


{{-- ═══════════════════════════════════════════════════════
     PAGE BREAK → PAGE 2
     ═══════════════════════════════════════════════════════ --}}
<div class="page-break"></div>


{{-- ═══════════════════════════════════════════════════════
     PAGE 2 — PROMISSORY NOTE
     ═══════════════════════════════════════════════════════ --}}
<div class="page-logo-header no-break">
    <table>
        <tr>
            <td class="plh-logo-cell">
                @if($logoData)
                    <img src="{{ $logoData }}" alt="SLEMCOOP Logo" class="plh-logo">
                @endif
            </td>
            <td class="plh-branding">
                <div class="plh-name">Southern Leyte Employees Multi-Purpose Cooperative</div>
                <div class="plh-sub">{{ $fmt($member?->branch?->name ?? '') }} Branch &nbsp;|&nbsp; Promissory Note</div>
            </td>
        </tr>
    </table>
</div>

<div class="pn-wrapper">

    <div class="pn-title">Promissory Note</div>

    {{-- Header Row --}}
    <table class="no-break" style="width:100%; border-collapse:collapse; margin-bottom:2mm;">
        <tr>
            <td style="width:60%; vertical-align:bottom; padding-bottom:1mm;">
                <div style="font-size:8px; margin-bottom:0.5mm;">PhP</div>
                <div style="border-bottom:0.5px solid #000; font-size:8px; font-weight:bold; display:inline-block; min-width:50mm; padding-bottom:0.3mm;">{{ $money($loanApplication->amount_requested) }}</div>
                <div style="font-size:7px; color:#444; margin-top:0.3mm;">Amount of Loan</div>
            </td>
            <td style="width:40%; vertical-align:bottom; padding-bottom:1mm; text-align:right;">
                <div style="font-size:7px; color:#444; margin-bottom:0.3mm;">PN No.</div>
                <div style="border-bottom:0.5px solid #000; font-size:9px; font-weight:bold; display:inline-block; min-width:30mm; padding-bottom:0.3mm;">{{ $loanApplication->loan_application_id }}</div>
            </td>
        </tr>
        <tr>
            <td style="vertical-align:bottom; padding-top:3mm;">
                <div style="border-bottom:0.5px solid #000; font-size:9px; font-weight:bold; display:inline-block; min-width:50mm; padding-bottom:0.3mm;">{{ $date($releaseDate) }}</div>
                <div style="font-size:7px; color:#444; margin-top:0.3mm;">Date Released</div>
            </td>
            <td style="vertical-align:bottom; padding-top:3mm; text-align:right;">
                <div style="font-size:7px; color:#444; margin-bottom:0.3mm;">Maturity Date</div>
                <div style="border-bottom:0.5px solid #000; font-size:9px; font-weight:bold; display:inline-block; min-width:30mm; padding-bottom:0.3mm;">{{ $date($maturityDate) }}</div>
            </td>
        </tr>
    </table>

    <div class="pn-body">
        <p>
            FOR VALUE RECEIVED, I/we jointly and severally, promise to pay to <strong>SOUTHERN LEYTE EMPLOYEES MULTI-PURPOSE COOPERATIVE (SLEMCOOP)</strong> or its order, at its office, the sum of <strong>PESOS</strong>
            (<span class="pn-underline">{{ $money($loanApplication->amount_requested) }}</span>), Philippine Currency within
            <span class="pn-underline">{{ $loanApplication->term_months }}</span> days/months/year(s) from date hereof at the rate of
            <span class="pn-underline">{{ $interestRate }}</span> percent (<span class="pn-underline">{{ $interestRate }}%</span>) per month/annum,
            computed in diminishing balance/annuity/straight method payable in equal daily/weekly/quincena/monthly installment commencing from
            <span class="pn-underline">{{ $firstPayDate }}</span>, {{ $releaseYear }} in the amount of PESOS:
            (<span class="pn-underline">{{ $monthlyAmort ? $money($monthlyAmort) : '___________' }}</span>) inclusive of interest until fully paid.
        </p>

        <p>
            In case of any default in the agreed payment schedules and/or any deviation of the loan proceeds, the payee or its assignee endorsee is unconditionally entitled to declare all unpaid balance of the note immediately due and payable. Notice of demand is expressly waived. A penalty charge of <span class="pn-underline">3</span> percent (<span class="pn-underline">3%</span>) per month upon due date(s), with 5 days grace period, in addition to the accruing interest, shall be charged on all delayed or unpaid installments. The cooperative is authorized to offset the share capital, term savings and any other deposits in the cooperative, in my/our names, for the payment of my/our loan without the need for prior notice or approval from me/us.
        </p>

        <p>
            In connection with the preparation and processing of this loan and other expenses, a service fee equivalent to <span class="pn-underline">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> of the loan is hereon imposed and the amount of which shall be deducted from the loan proceeds.
        </p>

        <p>
            I/we assign and authorize my employer to withhold the corresponding amount due to the Coop from all money, bonuses, retirement and other benefits accruing to me in case of termination or separation from my employment. The employer shall remit the amount collected to the Coop within Thirty (30) days from withholding or collection, or at least make the amount ready for pick up by the Coop or its authorized personnel.
        </p>

        <p>
            In the event that the cooperative eventually assigns this promissory note and all its securities attendant thereto in favor of the Land Bank of the Philippines to secure their loan obligation, consent is hereby given to the said assignment and shall bind myself/ourselves to the effectivity and legality of the assignment of credit and shall pay directly all amount due from me by virtue of this note to the Land Bank of the Philippines by reason and pursuant to the Deed of Assignment or a PN provision of the same substance.
        </p>

        <p>
            In case this note is referred to an attorney for collection or for legal action, I/we bind ourselves to pay attorney's fees equivalent to Twenty Five Percent (25%) of my outstanding obligation to any holder hereof, exclusive of costs and expenses of the litigation, but in no case shall be less than Five Thousand Pesos (P5,000.00). Any action arising out on this instrument shall be brought before the proper court in Maasin City, Southern Leyte, Philippines.
        </p>

        <p>In joint-several capacity:</p>
    </div>

    {{-- Borrower & Spouse Signatures --}}
    <table class="pn-sig-table no-break">
        <tr>
            <td style="width:50%">
                <div class="pn-sig-line">Borrower's Printed Name and Signature</div>
                <div style="text-align:center; font-size:8px; font-weight:bold; margin-top:1mm;">{{ $fmt($profile?->full_name) }}</div>
                <div class="pn-sig-sub">Valid ID/CTC No. ________________________</div>
                <div class="pn-sig-sub">Address: {{ $fmt($profile?->address) }}</div>
            </td>
            <td style="width:50%">
                <div class="pn-sig-line">Spouse's Printed Name and Signature</div>
                <div style="text-align:center; font-size:8px; font-weight:bold; margin-top:1mm;">{{ $fmt($spouse?->full_name) }}</div>
                <div class="pn-sig-sub">Valid ID/CTC No. ________________________</div>
                <div class="pn-sig-sub">Address: ________________________________</div>
            </td>
        </tr>
    </table>

    {{-- Co-Makers' Statement --}}
    <div class="pn-comaker-title">Co-makers' Statement:</div>

    <div class="pn-body">
        <p>
            We hereby agree to be the co-makers of
            <span class="pn-underline" style="min-width:50mm;">{{ $fmt($profile?->full_name) }}</span>
            for the aforementioned Loan granted by the <strong>SOUTHERN LEYTE EMPLOYEES MULTI-PURPOSE COOPERATIVE (SLEMCOOP)</strong>, Maasin City, Southern Leyte. We further agree to voluntarily and willingly bind ourselves to pay jointly and severally all his/her unpaid obligations to SLEMCOOP according to the terms and conditions of the Promissory Note that we signed in case the borrower fails to pay his/her obligations on time for whatever reason/s.
        </p>
    </div>

    <table class="pn-sig-table no-break">
        <tr>
            <td style="width:50%">
                <div style="font-size:8px; font-weight:bold; margin-bottom:1mm; color:#1b5e20;">Co-maker 1</div>
                <div class="pn-sig-line">Printed Name and Signature</div>
                <div style="text-align:center; font-size:8px; font-weight:bold; margin-top:1mm;">{{ $fmt($coMaker1?->full_name) }}</div>
                <div class="pn-sig-sub">Valid ID/CTC No. ________________________</div>
                <div class="pn-sig-sub">Address: {{ $fmt($coMaker1?->address) }}</div>
            </td>
            <td style="width:50%">
                <div style="font-size:8px; font-weight:bold; margin-bottom:1mm; color:#1b5e20;">Co-maker 2</div>
                <div class="pn-sig-line">Printed Name and Signature</div>
                <div style="text-align:center; font-size:8px; font-weight:bold; margin-top:1mm;">{{ $fmt($coMaker2?->full_name) }}</div>
                <div class="pn-sig-sub">Valid ID/CTC No. ________________________</div>
                <div class="pn-sig-sub">Address: {{ $fmt($coMaker2?->address) }}</div>
            </td>
        </tr>
    </table>

    <div class="pn-presence">Signed in the Presence of:</div>

    <table class="pn-sig-table no-break">
        <tr>
            <td style="width:50%"><div class="pn-sig-line">&nbsp;</div></td>
            <td style="width:50%"><div class="pn-sig-line">&nbsp;</div></td>
        </tr>
    </table>

</div>

<div class="footer-pg">
    {{ $fmt($profile?->full_name) }} &nbsp;|&nbsp; Application #{{ $loanApplication->loan_application_id }} &nbsp;|&nbsp; {{ now()->format('F j, Y') }} &nbsp;|&nbsp; Page 2 of 4
</div>


{{-- ═══════════════════════════════════════════════════════
     PAGE BREAK → PAGE 3
     ═══════════════════════════════════════════════════════ --}}
<div class="page-break"></div>


{{-- ═══════════════════════════════════════════════════════
     PAGE 3 — INTERNAL EVALUATION SHEET
     ═══════════════════════════════════════════════════════ --}}
<div class="page-logo-header no-break">
    <table>
        <tr>
            <td class="plh-logo-cell">
                @if($logoData)
                    <img src="{{ $logoData }}" alt="SLEMCOOP Logo" class="plh-logo">
                @endif
            </td>
            <td class="plh-branding">
                <div class="plh-name">Southern Leyte Employees Multi-Purpose Cooperative</div>
                <div class="plh-sub">{{ $fmt($member?->branch?->name ?? '') }} Branch &nbsp;|&nbsp; Internal Evaluation Sheet</div>
            </td>
        </tr>
    </table>
</div>

<div class="eval-header no-break">
    <div style="font-size:12px; font-weight:bold; text-transform:uppercase;">Internal Evaluation Sheet</div>
    <div style="font-size:8px; color:#fff; margin-top:0.5mm;">
        {{ $fmt($profile?->full_name) }} &nbsp;|&nbsp; Application #{{ $loanApplication->loan_application_id }} &nbsp;|&nbsp; {{ $fmt($loanApplication->type?->name) }} &nbsp;|&nbsp; {{ $money($loanApplication->amount_requested) }}
    </div>
</div>

{{-- Application Status --}}
<table class="bt no-break" style="margin-bottom:2mm;">
    <tr><td class="b c sh" colspan="4">APPLICATION STATUS</td></tr>
    <tr>
        <td class="b c" style="width:25%"><div class="lbl">Loan Status</div><div class="val"><span class="badge {{ $loanStatusClass }}">{{ $loanApplication->status }}</span></div></td>
        <td class="b c" style="width:25%"><div class="lbl">Date Applied</div><div class="val">{{ $date($loanApplication->created_at) }}</div></td>
        <td class="b c" style="width:25%"><div class="lbl">Approved At</div><div class="val">{{ $date($loanApplication->approved_at) }}</div></td>
        <td class="b c" style="width:25%"><div class="lbl">Collateral Status</div><div class="val"><span class="badge {{ $collateralClass }}">{{ $collateralStatus }}</span></div></td>
    </tr>
</table>

{{-- Cash Flow --}}
<table class="bt no-break" style="margin-bottom:2mm;">
    <tr><td class="b c sh" colspan="4">CASH FLOW ANALYSIS</td></tr>
    <tr>
        <td class="b c" style="width:25%"><div class="lbl">Total Income</div><div class="val">{{ $money($totalIncome) }}</div></td>
        <td class="b c" style="width:25%"><div class="lbl">Total Expenses</div><div class="val">{{ $money($totalExpenses) }}</div></td>
        <td class="b c" style="width:25%"><div class="lbl">Net Cash Flow</div><div class="val">{{ $money($netCashFlow) }}</div></td>
        <td class="b c" style="width:25%"><div class="lbl">Allowed Payment (40%)</div><div class="val">{{ $money($allowedPayment) }}</div></td>
    </tr>
    @if($loanApplication->salary || $loanApplication->business_income || $loanApplication->remittances || $loanApplication->other_income)
    <tr>
        <td class="b c"><div class="lbl">Salary</div><div class="val">{{ $money($loanApplication->salary ?? 0) }}</div></td>
        <td class="b c"><div class="lbl">Business Income</div><div class="val">{{ $money($loanApplication->business_income ?? 0) }}</div></td>
        <td class="b c"><div class="lbl">Remittances</div><div class="val">{{ $money($loanApplication->remittances ?? 0) }}</div></td>
        <td class="b c"><div class="lbl">Other Income</div><div class="val">{{ $money($loanApplication->other_income ?? 0) }}</div></td>
    </tr>
    <tr>
        <td class="b c"><div class="lbl">Living Expenses</div><div class="val">{{ $money($loanApplication->living_expenses ?? 0) }}</div></td>
        <td class="b c"><div class="lbl">Business Expenses</div><div class="val">{{ $money($loanApplication->business_expenses ?? 0) }}</div></td>
        <td class="b c"><div class="lbl">Existing Loan Payments</div><div class="val">{{ $money($loanApplication->existing_loan_payments ?? 0) }}</div></td>
        <td class="b c"><div class="lbl">Other Expenses</div><div class="val">{{ $money($loanApplication->other_expenses ?? 0) }}</div></td>
    </tr>
    @endif
</table>

{{-- Loan Capacity --}}
<table class="bt no-break" style="margin-bottom:2mm;">
    <tr><td class="b c sh" colspan="4">LOAN CAPACITY EVALUATION</td></tr>
    <tr>
        <td class="b c" style="width:25%"><div class="lbl">Proposed Monthly Payment</div><div class="val">{{ $money($proposedPayment) }}</div></td>
        <td class="b c" style="width:25%"><div class="lbl">Allowed Payment (40%)</div><div class="val">{{ $money($allowedPayment) }}</div></td>
        <td class="b c" style="width:25%"><div class="lbl">Capacity Status</div><div class="val"><span class="badge {{ $capacityClass }}">{{ $capacityStatus }}</span></div></td>
        <td class="b c" style="width:25%"><div class="lbl">Interest Rate / Month</div><div class="val">{{ $interestRate }}%</div></td>
    </tr>
</table>

{{-- Internal Notes --}}
<table class="bt no-break" style="margin-bottom:2mm;">
    <tr><td class="b c sh" colspan="2">INTERNAL NOTES</td></tr>
    <tr>
        <td class="b c" style="width:50%; border-right:0.5px solid #000;">
            <div class="lbl" style="margin-bottom:1mm;">Evaluation Notes</div>
            <div class="notes-box">{{ $loanApplication->evaluation_notes ?? '—' }}</div>
        </td>
        <td class="b c" style="width:50%;">
            <div class="lbl" style="margin-bottom:1mm;">BI/CI Notes (Background / Credit Investigation)</div>
            <div class="notes-box">{{ $loanApplication->bici_notes ?? '—' }}</div>
        </td>
    </tr>
</table>

{{-- Loan Account (conditional) --}}
@if($loanApplication->loanAccount)
<table class="bt no-break" style="margin-bottom:2mm;">
    <tr><td class="b c sh" colspan="4">LOAN ACCOUNT</td></tr>
    <tr>
        <td class="b c" style="width:25%"><div class="lbl">Release Date</div><div class="val">{{ $date($releaseDate) }}</div></td>
        <td class="b c" style="width:25%"><div class="lbl">Maturity Date</div><div class="val">{{ $date($maturityDate) }}</div></td>
        <td class="b c" style="width:25%"><div class="lbl">Monthly Amortization</div><div class="val">{{ $money($monthlyAmort) }}</div></td>
        <td class="b c" style="width:25%"><div class="lbl">Account Status</div><div class="val"><span class="badge {{ $acctStatusClass }}">{{ $loanApplication->loanAccount->status }}</span></div></td>
    </tr>
    <tr>
        <td class="b c"><div class="lbl">Principal Amount</div><div class="val">{{ $money($loanApplication->loanAccount->principal_amount) }}</div></td>
        <td class="b c"><div class="lbl">Outstanding Balance</div><div class="val">{{ $money($loanApplication->loanAccount->balance) }}</div></td>
        <td class="b c"><div class="lbl">Interest Rate / Month</div><div class="val">{{ $interestRate }}%</div></td>
        <td class="b c"><div class="lbl">Term</div><div class="val">{{ $loanApplication->term_months }} months</div></td>
    </tr>
</table>
@endif

<table class="no-break" style="width:100%; border-collapse:collapse; margin-top:4mm;">
    <tr>
        <td style="width:33%; text-align:center; padding:0 3mm;"><div style="border-top:0.5px solid #2f7d32; padding-top:0.5mm; font-size:7px;">Prepared by (Loan Officer)</div></td>
        <td style="width:34%; text-align:center; padding:0 3mm;"><div style="border-top:0.5px solid #2f7d32; padding-top:0.5mm; font-size:7px;">Evaluated by (Branch Manager)</div></td>
        <td style="width:33%; text-align:center; padding:0 3mm;"><div style="border-top:0.5px solid #2f7d32; padding-top:0.5mm; font-size:7px;">Approved by (General Manager)</div></td>
    </tr>
</table>

<div class="footer-pg">
    {{ $fmt($profile?->full_name) }} &nbsp;|&nbsp; Application #{{ $loanApplication->loan_application_id }} &nbsp;|&nbsp; {{ now()->format('F j, Y') }} &nbsp;|&nbsp; Page 3 of 4
</div>


{{-- ═══════════════════════════════════════════════════════
     PAGE BREAK → PAGE 4
     ═══════════════════════════════════════════════════════ --}}
<div class="page-break"></div>


{{-- ═══════════════════════════════════════════════════════
     PAGE 4 — AMORTIZATION SCHEDULE
     ═══════════════════════════════════════════════════════ --}}
<div class="page-logo-header no-break">
    <table>
        <tr>
            <td class="plh-logo-cell">
                @if($logoData)
                    <img src="{{ $logoData }}" alt="SLEMCOOP Logo" class="plh-logo">
                @endif
            </td>
            <td class="plh-branding">
                <div class="plh-name">Southern Leyte Employees Multi-Purpose Cooperative</div>
                <div class="plh-sub">{{ $fmt($member?->branch?->name ?? '') }} Branch &nbsp;|&nbsp; Amortization Schedule</div>
            </td>
        </tr>
    </table>
</div>

<div class="eval-header no-break">
    <div style="font-size:12px; font-weight:bold; text-transform:uppercase;">Amortization Schedule</div>
    <div style="font-size:8px; color:#fff; margin-top:0.5mm;">
        {{ $fmt($profile?->full_name) }} &nbsp;|&nbsp; {{ $money($principal) }} &nbsp;|&nbsp; {{ $term }} months &nbsp;|&nbsp; {{ $interestRate }}% / month &nbsp;|&nbsp; Release: {{ $date($releaseDate) }}
    </div>
</div>

@if($schedule && count($schedule) > 0)

    <table class="amort">
        <thead>
            <tr>
                <th>#</th>
                <th>Due Date</th>
                <th>Beginning Balance</th>
                <th>Principal</th>
                <th>Interest</th>
                <th>Monthly Payment</th>
                <th>Ending Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($schedule as $row)
            <tr>
                <td>{{ $row['period'] ?? $loop->iteration }}</td>
                <td>{{ isset($row['due_date']) ? \Carbon\Carbon::parse($row['due_date'])->format('M j, Y') : '—' }}</td>
                <td>&#8369;{{ number_format((float)($row['beginning_balance'] ?? $row['balance_start'] ?? 0), 2) }}</td>
                <td>&#8369;{{ number_format((float)($row['principal'] ?? 0), 2) }}</td>
                <td>&#8369;{{ number_format((float)($row['interest'] ?? 0), 2) }}</td>
                <td>&#8369;{{ number_format((float)($row['payment'] ?? $row['monthly_payment'] ?? 0), 2) }}</td>
                <td>&#8369;{{ number_format((float)($row['balance'] ?? $row['ending_balance'] ?? 0), 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align:right; padding:1.5mm 2mm;">TOTALS</td>
                <td>&#8369;{{ number_format($totalPrincipal, 2) }}</td>
                <td>&#8369;{{ number_format($totalInterest, 2) }}</td>
                <td>&#8369;{{ number_format($totalPayment, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    {{-- Acknowledgment signatures (with schedule) --}}
    <table class="no-break" style="width:100%; border-collapse:collapse; margin-top:10mm;">
        <tr>
            <td style="width:33%; text-align:center; padding:0 3mm;">
                <div style="border-top:0.5px solid #2f7d32; padding-top:0.5mm; font-size:7px;">Borrower's Signature over Printed Name</div>
            </td>
            <td style="width:34%; text-align:center; padding:0 3mm;">
                <div style="border-top:0.5px solid #2f7d32; padding-top:0.5mm; font-size:7px;">Loan Officer</div>
            </td>
            <td style="width:33%; text-align:center; padding:0 3mm;">
                <div style="border-top:0.5px solid #2f7d32; padding-top:0.5mm; font-size:7px;">Branch Manager</div>
            </td>
        </tr>
    </table>

@else

    {{-- Placeholder when no schedule yet --}}
    <table class="amort">
        <thead>
            <tr>
                <th>#</th>
                <th>Due Date</th>
                <th>Beginning Balance</th>
                <th>Principal</th>
                <th>Interest</th>
                <th>Monthly Payment</th>
                <th>Ending Balance</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="7">
                    <div class="amort-placeholder">
                        Amortization schedule will be generated upon loan approval and release date assignment.
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    {{-- Summary block --}}
    <table class="no-break" style="width:100%; border-collapse:collapse; margin-top:3mm;">
        <tr>
            <td class="b c" style="width:25%">
                <div class="lbl">Loan Amount</div>
                <div class="val">{{ $money($principal) }}</div>
            </td>
            <td class="b c" style="width:25%">
                <div class="lbl">Term</div>
                <div class="val">{{ $term }} months</div>
            </td>
            <td class="b c" style="width:25%">
                <div class="lbl">Interest Rate</div>
                <div class="val">{{ $interestRate }}% / month</div>
            </td>
            <td class="b c" style="width:25%">
                <div class="lbl">Est. Monthly Payment</div>
                <div class="val">{{ $monthlyAmort ? $money($monthlyAmort) : $money($proposedPayment) }}</div>
            </td>
        </tr>
        <tr>
            <td class="b c" style="width:25%">
                <div class="lbl">Release Date</div>
                <div class="val">{{ $date($releaseDate) }}</div>
            </td>
            <td class="b c" style="width:25%">
                <div class="lbl">Maturity Date</div>
                <div class="val">{{ $date($maturityDate) }}</div>
            </td>
            <td class="b c" style="width:25%">
                <div class="lbl">First Payment Due</div>
                <div class="val">{{ $firstPayDate }}</div>
            </td>
            <td class="b c" style="width:25%">
                <div class="lbl">Account Status</div>
                <div class="val">{{ $loanApplication->loanAccount?->status ?? 'Pending Release' }}</div>
            </td>
        </tr>
    </table>

    {{-- Acknowledgment signatures (no schedule) --}}
    <table class="no-break" style="width:100%; border-collapse:collapse; margin-top:10mm;">
        <tr>
            <td style="width:33%; text-align:center; padding:0 3mm;">
                <div style="border-top:0.5px solid #2f7d32; padding-top:0.5mm; font-size:7px;">Borrower's Signature over Printed Name</div>
            </td>
            <td style="width:34%; text-align:center; padding:0 3mm;">
                <div style="border-top:0.5px solid #2f7d32; padding-top:0.5mm; font-size:7px;">Loan Officer</div>
            </td>
            <td style="width:33%; text-align:center; padding:0 3mm;">
                <div style="border-top:0.5px solid #2f7d32; padding-top:0.5mm; font-size:7px;">Branch Manager</div>
            </td>
        </tr>
    </table>

@endif

<div class="footer-pg">
    {{ $fmt($profile?->full_name) }} &nbsp;|&nbsp; Application #{{ $loanApplication->loan_application_id }} &nbsp;|&nbsp; {{ now()->format('F j, Y') }} &nbsp;|&nbsp; Page 4 of 4
</div>

</body>
</html>