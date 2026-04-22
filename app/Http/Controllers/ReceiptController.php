<?php

namespace App\Http\Controllers;

use App\Models\CollectionAndPosting;
use App\Models\User;
use App\Services\LoanScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReceiptController extends Controller
{
    public function show(CollectionAndPosting $record): Response
    {
        return $this->renderReceipt($record, autoprint: false);
    }

    public function print(CollectionAndPosting $record): Response
    {
        return $this->renderReceipt($record, autoprint: true);
    }

    public function download(CollectionAndPosting $record): StreamedResponse
    {
        $data = $this->receiptData($record, false);

        if (! function_exists('imagecreatetruecolor')) {
            abort(500, 'GD extension is required for receipt image download.');
        }

        $filename = $data['downloadName'];

        return response()->streamDownload(function () use ($data) {
            echo $this->renderReceiptImage($data);
        }, $filename, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function receiptData(CollectionAndPosting $record, bool $autoprint): array
    {
        $postedBy = User::find($record->posted_by_user_id);

        $amountPaid = (float) ($record->amount_paid ?? 0);
        $remainingBalance = (float) ($record->remaining_balance ?? 0);

        $paymentDate = $record->payment_date
            ? Carbon::parse($record->payment_date)
            : null;

        $nextDueDate = ! empty($record->next_due_date)
            ? Carbon::parse($record->next_due_date)->format('M d, Y')
            : 'N/A';

        $loanAccount = $record->loanAccount?->fresh();

        if ($loanAccount) {
            $remainingBalance = (float) ($loanAccount->balance ?? $remainingBalance);

            $freshSchedule = app(LoanScheduleService::class)->build($loanAccount);
            $freshNextDue = collect($freshSchedule)->first(
                fn (array $row): bool => round((float) ($row['unpaid_amount'] ?? 0), 2) > 0
            );

            if ($freshNextDue && ! empty($freshNextDue['due_date'])) {
                $nextDueDate = Carbon::parse($freshNextDue['due_date'])->format('M d, Y');
            }
        }

        return [
            'record' => $record,
            'orNumber' => e($record->reference_number ?? 'N/A'),
            'member' => e($record->member_name ?? 'Regular Member'),
            'loan' => e($record->loan_number ?? 'N/A'),
            'amount' => number_format($amountPaid, 2),
            'rawAmount' => $amountPaid,
            'remainingBalance' => number_format($remainingBalance, 2),
            'paymentDate' => $paymentDate?->format('M d, Y') ?? 'N/A',
            'generatedAt' => now()->format('M d, Y h:i A'),
            'method' => e($record->payment_method ?? 'Cash'),
            'status' => strtoupper((string) ($record->status ?? 'POSTED')),
            'postedByName' => e($postedBy?->name ?? $postedBy?->username ?? 'Admin User'),
            'notes' => e($record->notes ?? ''),
            'nextDueDate' => $nextDueDate,
            'autoprint' => $autoprint,
            'downloadName' => sprintf(
                'payment-receipt-%s.png',
                preg_replace('/[^A-Za-z0-9\-_]/', '-', (string) ($record->reference_number ?: $record->getKey()))
            ),
        ];
    }

    private function renderReceipt(CollectionAndPosting $record, bool $autoprint): Response
    {
        $data = $this->receiptData($record, $autoprint);

        $autoPrintJs = $data['autoprint']
            ? '<script>window.addEventListener("load", function () { window.print(); });</script>'
            : '';

        $notesBlock = $data['notes'] !== ''
            ? <<<HTML
                <div class="receipt-notes">
                    <div class="receipt-note-title">Notes</div>
                    <div class="receipt-note-body">{$data['notes']}</div>
                </div>
            HTML
            : '';

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - {$data['orNumber']}</title>
    {$autoPrintJs}
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background:
                radial-gradient(circle at top, rgba(16, 185, 129, 0.12), transparent 30%),
                #020617;
            color: #e2e8f0;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 24px 16px;
        }

        .toolbar {
            width: 100%;
            max-width: 720px;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-bottom: 18px;
            flex-wrap: wrap;
        }

        .btn {
            appearance: none;
            border: none;
            border-radius: 14px;
            padding: 12px 18px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: .2s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
            opacity: .94;
        }

        .btn-close {
            background: #0f172a;
            color: #e2e8f0;
            border: 1px solid #1e293b;
        }

        .btn-download {
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
            color: #ffffff;
            box-shadow: 0 10px 24px rgba(16, 185, 129, 0.22);
        }

        .btn-print {
            background: #111827;
            color: #f8fafc;
            border: 1px solid #1f2937;
        }

        .receipt-shell {
            width: 100%;
            max-width: 720px;
            background: rgba(2, 6, 23, 0.96);
            border: 1px solid #0f2a4a;
            border-radius: 28px;
            padding: 28px;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.45);
        }

        .receipt-card {
            border: 1px dashed rgba(59, 130, 246, 0.22);
            border-radius: 24px;
            padding: 34px 28px 26px;
            background: linear-gradient(180deg, rgba(2,6,23,0.95), rgba(2,6,23,0.88));
        }

        .receipt-head {
            text-align: center;
            margin-bottom: 24px;
        }

        .receipt-icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #00d09c;
        }

        .receipt-title {
            margin: 0;
            font-size: 22px;
            line-height: 1.1;
            font-weight: 900;
            letter-spacing: .02em;
            color: #ffffff;
            text-transform: uppercase;
        }

        .receipt-subtitle {
            margin-top: 8px;
            color: #00d09c;
            font-size: 14px;
            font-weight: 700;
        }

        .receipt-system {
            margin-top: 6px;
            color: #7aa2d6;
            font-size: 14px;
        }

        .divider {
            border-top: 1px dashed rgba(255,255,255,.50);
            margin: 22px 0;
        }

        .detail-list {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 18px;
            padding: 14px 0;
            border-bottom: 1px solid rgba(30, 58, 138, 0.25);
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-size: 16px;
            color: #93c5fd;
            font-weight: 500;
        }

        .detail-value {
            font-size: 16px;
            color: #ffffff;
            font-weight: 800;
            text-align: right;
            word-break: break-word;
        }

        .amount-row {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid rgba(30, 58, 138, 0.35);
        }

        .amount-row .detail-label {
            font-size: 17px;
            font-weight: 800;
            color: #0f172a;
            background: linear-gradient(135deg, #c7d2fe 0%, #93c5fd 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .amount-row .detail-value {
            font-size: 18px;
            color: #00d09c;
        }

        .summary-boxes {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
            margin-top: 14px;
        }

        .summary-box {
            background: linear-gradient(90deg, rgba(0, 94, 55, 0.92), rgba(0, 74, 52, 0.92));
            border: 1px solid rgba(16, 185, 129, 0.24);
            border-radius: 14px;
            padding: 14px 16px;
            display: flex;
            justify-content: space-between;
            gap: 14px;
            align-items: center;
        }

        .summary-label {
            color: #a7f3d0;
            font-size: 14px;
            font-weight: 500;
        }

        .summary-value {
            color: #ffffff;
            font-size: 16px;
            font-weight: 900;
            text-align: right;
        }

        .receipt-footer {
            margin-top: 18px;
            padding-top: 14px;
            border-top: 1px dashed rgba(255,255,255,.45);
            text-align: center;
            color: #7aa2d6;
            font-size: 13px;
        }

        .receipt-footer strong {
            color: #00d09c;
        }

        .receipt-notes {
            margin-top: 14px;
            background: rgba(15, 23, 42, 0.85);
            border: 1px solid #1e293b;
            border-radius: 14px;
            padding: 14px 16px;
        }

        .receipt-note-title {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #94a3b8;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .receipt-note-body {
            font-size: 14px;
            color: #e2e8f0;
            line-height: 1.6;
            white-space: pre-wrap;
        }

        @media (max-width: 640px) {
            .receipt-shell {
                padding: 18px;
                border-radius: 20px;
            }

            .receipt-card {
                padding: 24px 18px 20px;
            }

            .detail-row,
            .summary-box {
                flex-direction: column;
                align-items: flex-start;
            }

            .detail-value,
            .summary-value {
                text-align: left;
            }
        }

        @media print {
            body {
                background: #020617;
                padding: 0;
            }

            .toolbar {
                display: none !important;
            }

            .receipt-shell {
                max-width: 100%;
                box-shadow: none;
                border: none;
                border-radius: 0;
            }

            @page {
                margin: 10mm;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button class="btn btn-close" onclick="window.close()">Close</button>
        <button class="btn btn-print" onclick="window.print()">Print</button>
        <a class="btn btn-download" href="{$this->downloadUrl($record)}">Download PNG</a>
    </div>

    <div class="receipt-shell">
        <div class="receipt-card">
            <div class="receipt-head">
                <div class="receipt-icon">
                    <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 2H8a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V8z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 2v6h6"/>
                    </svg>
                </div>
                <h1 class="receipt-title">Payment Receipt</h1>
                <div class="receipt-subtitle">Payment Posted Successfully</div>
                <div class="receipt-system">Cooperative Lending System</div>
            </div>

            <div class="divider"></div>

            <div class="detail-list">
                <div class="detail-row">
                    <div class="detail-label">Member</div>
                    <div class="detail-value">{$data['member']}</div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Loan #</div>
                    <div class="detail-value">{$data['loan']}</div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Payment Date</div>
                    <div class="detail-value">{$data['paymentDate']}</div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Method</div>
                    <div class="detail-value">{$data['method']}</div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Posted By</div>
                    <div class="detail-value">{$data['postedByName']}</div>
                </div>

                <div class="detail-row amount-row">
                    <div class="detail-label">Amount Paid</div>
                    <div class="detail-value">₱{$data['amount']}</div>
                </div>
            </div>

            <div class="summary-boxes">
                <div class="summary-box">
                    <div class="summary-label">Remaining Balance</div>
                    <div class="summary-value">₱{$data['remainingBalance']}</div>
                </div>

                <div class="summary-box">
                    <div class="summary-label">Next Due Date</div>
                    <div class="summary-value">{$data['nextDueDate']}</div>
                </div>
            </div>

            {$notesBlock}

            <div class="receipt-footer">
                Generated on {$data['generatedAt']} &nbsp;·&nbsp; Status: <strong>{$data['status']}</strong>
            </div>
        </div>
    </div>
</body>
</html>
HTML;

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    private function downloadUrl(CollectionAndPosting $record): string
    {
        return route('receipt.download', $record);
    }

    private function renderReceiptImage(array $data): string
    {
        $width = 1200;
        $height = 1500;

        $image = imagecreatetruecolor($width, $height);

        imagealphablending($image, true);
        imagesavealpha($image, true);

        $bg = imagecolorallocate($image, 2, 6, 23);
        $card = imagecolorallocate($image, 3, 7, 18);
        $white = imagecolorallocate($image, 255, 255, 255);
        $muted = imagecolorallocate($image, 122, 162, 214);
        $green = imagecolorallocate($image, 0, 208, 156);
        $greenDark = imagecolorallocate($image, 0, 94, 55);
        $line = imagecolorallocate($image, 20, 40, 70);
        $cyan = imagecolorallocate($image, 147, 197, 253);

        imagefilledrectangle($image, 0, 0, $width, $height, $bg);
        imagefilledrectangle($image, 80, 80, $width - 80, $height - 80, $card);

        imagerectangle($image, 110, 110, $width - 110, $height - 110, $line);

        imagestring($image, 5, 505, 150, 'PAYMENT RECEIPT', $white);
        imagestring($image, 4, 470, 190, 'Payment Posted Successfully', $green);
        imagestring($image, 3, 500, 225, 'Cooperative Lending System', $muted);

        imageline($image, 150, 290, $width - 150, 290, $white);

        $rows = [
            ['Member', html_entity_decode($data['member'])],
            ['Loan #', html_entity_decode($data['loan'])],
            ['Payment Date', html_entity_decode($data['paymentDate'])],
            ['Method', html_entity_decode($data['method'])],
            ['Posted By', html_entity_decode($data['postedByName'])],
        ];

        $y = 350;
        foreach ($rows as [$label, $value]) {
            imagestring($image, 5, 150, $y, $label, $cyan);
            $valueX = $width - 150 - (imagefontwidth(5) * strlen($value));
            imagestring($image, 5, max(560, $valueX), $y, $value, $white);
            imageline($image, 150, $y + 42, $width - 150, $y + 42, $line);
            $y += 85;
        }

        imagestring($image, 5, 150, $y + 10, 'Amount Paid', $white);
        $amountText = 'PHP '.$data['amount'];
        $amountX = $width - 150 - (imagefontwidth(5) * strlen($amountText));
        imagestring($image, 5, max(560, $amountX), $y + 10, $amountText, $green);
        imageline($image, 150, $y + 52, $width - 150, $y + 52, $line);

        imagefilledrectangle($image, 150, $y + 95, $width - 150, $y + 170, $greenDark);
        imagestring($image, 5, 180, $y + 120, 'Remaining Balance', $cyan);
        $rb = 'PHP '.$data['remainingBalance'];
        $rbX = $width - 180 - (imagefontwidth(5) * strlen($rb));
        imagestring($image, 5, max(620, $rbX), $y + 120, $rb, $white);

        imagefilledrectangle($image, 150, $y + 195, $width - 150, $y + 270, $greenDark);
        imagestring($image, 5, 180, $y + 220, 'Next Due Date', $cyan);
        $nd = $data['nextDueDate'];
        $ndX = $width - 180 - (imagefontwidth(5) * strlen($nd));
        imagestring($image, 5, max(620, $ndX), $y + 220, $nd, $white);

        $footer = 'Generated on '.$data['generatedAt'].'  |  Status: '.$data['status'];
        $footerX = (int) (($width - (imagefontwidth(4) * strlen($footer))) / 2);
        imagestring($image, 4, max(120, $footerX), $height - 180, $footer, $muted);

        ob_start();
        imagepng($image);
        $png = (string) ob_get_clean();
        imagedestroy($image);

        return $png;
    }
}
