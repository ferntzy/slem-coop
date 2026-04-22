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
            line-height: 1.5;
            margin: 0;
        }

        .card {
            border: 1px solid #dbeafe;
            border-radius: 16px;
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 100%);
            color: #fff;
            padding: 18px 20px;
            text-align: center;
        }

        .header .coop {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.16em;
            opacity: 0.8;
            margin-bottom: 6px;
        }

        .header h1 {
            font-size: 20px;
            margin: 0;
        }

        .receipt-no {
            display: inline-block;
            margin-top: 10px;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.22);
            font-family: monospace;
            font-size: 11px;
            letter-spacing: 0.08em;
        }

        .amount {
            padding: 20px;
            text-align: center;
            background: #f0fdf4;
            border-bottom: 1px solid #dcfce7;
        }

        .amount-label {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #15803d;
            margin-bottom: 6px;
        }

        .amount-value {
            font-size: 30px;
            font-weight: 800;
            color: #059669;
        }

        .section {
            padding: 18px 20px;
        }

        .row {
            display: table;
            width: 100%;
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .row:last-child {
            border-bottom: 0;
        }

        .label,
        .value {
            display: table-cell;
            vertical-align: top;
        }

        .label {
            width: 34%;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #94a3b8;
        }

        .value {
            width: 66%;
            text-align: right;
            font-size: 11px;
            font-weight: 600;
            color: #111827;
            word-break: break-word;
        }

        .badge {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 999px;
            font-size: 9px;
            font-weight: 700;
        }

        .badge-green { background: #dcfce7; color: #15803d; }
        .badge-yellow { background: #fef3c7; color: #92400e; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-gray { background: #e2e8f0; color: #334155; }

        .notes {
            margin: 0 20px 18px;
            padding: 12px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #f8fafc;
            font-size: 10px;
            color: #475569;
            line-height: 1.6;
        }

        .notes-label {
            display: block;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 6px;
        }

        .footer {
            padding: 14px 20px 18px;
            text-align: center;
            font-size: 9px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }

        .footer strong {
            color: #0f172a;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <div class="coop">Cooperative Management System</div>
            <h1>Official Receipt</h1>
            <div class="receipt-no">{!! $orNumber !!}</div>
        </div>

        <div class="amount">
            <div class="amount-label">Amount Paid</div>
            <div class="amount-value">₱{!! $amount !!}</div>
        </div>

        <div class="section">
            <div class="row">
                <div class="label">Member</div>
                <div class="value">{!! $member !!}</div>
            </div>
            <div class="row">
                <div class="label">Loan Account</div>
                <div class="value" style="font-family: monospace;">{!! $loan !!}</div>
            </div>
            <div class="row">
                <div class="label">Payment Date</div>
                <div class="value">{!! $date !!}</div>
            </div>
            <div class="row">
                <div class="label">Payment Method</div>
                <div class="value">{!! $method !!}</div>
            </div>
            <div class="row">
                <div class="label">Status</div>
                <div class="value"><span class="badge badge-gray">{!! $status !!}</span></div>
            </div>
            <div class="row">
                <div class="label">Posted By</div>
                <div class="value">{!! $postedByName !!}</div>
            </div>
        </div>

        @if(! empty($record->notes))
            <div class="notes">
                <span class="notes-label">Notes</span>
                {!! $notes !!}
            </div>
        @endif

        <div class="footer">
            Generated: <strong>{!! $generatedAt !!}</strong><br>
            This is an official digital receipt. Please keep this for your records.
        </div>
    </div>
</body>
</html>