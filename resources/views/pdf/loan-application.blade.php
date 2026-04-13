<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Loan Application</title>
    <style>
        @page {
            margin: 0;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }

        .page {
            position: relative;
            width: 100%;
            height: 100%;
            page-break-after: always;
        }

        .page:last-child {
            page-break-after: auto;
        }

        .bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .field {
            position: absolute;
            z-index: 2;
            color: #000;
            white-space: nowrap;
        }

        .small { font-size: 10px; }
        .normal { font-size: 11px; }
        .bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="page">
        <img class="bg" src="{{ public_path('forms/loan-application-page-1.jpg') }}" alt="Form Background">

        <div class="field normal" style="top: 92px; left: 120px;">
            {{ $form['application_date'] }}
        </div>

        <div class="field normal" style="top: 130px; left: 120px; width: 320px;">
            {{ $form['full_name'] }}
        </div>

        <div class="field normal" style="top: 160px; left: 120px;">
            {{ $form['mobile_number'] }}
        </div>

        <div class="field normal" style="top: 190px; left: 120px;">
            {{ $form['civil_status'] }}
        </div>

        <div class="field normal" style="top: 220px; left: 120px;">
            {{ $form['tin'] }}
        </div>

        <div class="field normal" style="top: 250px; left: 120px;">
            {{ number_format((float) $form['loan_amount'], 2) }}
        </div>

        <div class="field normal" style="top: 280px; left: 120px;">
            {{ $form['loan_term_months'] }} months
        </div>

        <div class="field normal" style="top: 310px; left: 120px;">
            {{ $form['interest_rate'] }}%
        </div>

        <div class="field normal" style="top: 340px; left: 120px; width: 350px;">
            {{ $form['purpose'] }}
        </div>

        <div class="field normal" style="top: 390px; left: 120px; width: 300px;">
            {{ $form['spouse_name'] }}
        </div>

        <div class="field normal" style="top: 420px; left: 120px; width: 300px;">
            {{ $form['spouse_occupation'] }}
        </div>

        {{-- Leave signature areas blank intentionally --}}
    </div>
</body>
</html>