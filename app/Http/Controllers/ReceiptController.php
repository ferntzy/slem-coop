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

        return response()->streamDownload(function () use ($data) {
            echo $this->renderReceiptImage($data);
        }, $data['downloadName'], [
            'Content-Type'  => 'image/png',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'        => 'no-cache',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Data
    // ─────────────────────────────────────────────────────────────────────────

    private function receiptData(CollectionAndPosting $record, bool $autoprint): array
    {
        $postedBy         = User::find($record->posted_by_user_id);
        $amountPaid       = (float) ($record->amount_paid ?? 0);
        $remainingBalance = (float) ($record->remaining_balance ?? 0);
        $nextDueDate      = 'N/A';

        $loanAccount = $record->loanAccount?->fresh();

        if ($loanAccount) {
            $remainingBalance = (float) ($loanAccount->balance ?? $remainingBalance);
            $freshSchedule    = app(LoanScheduleService::class)->build($loanAccount);
            $freshNextDue     = collect($freshSchedule)->first(
                fn (array $row): bool => round((float) ($row['unpaid_amount'] ?? 0), 2) > 0
            );
            if ($freshNextDue && ! empty($freshNextDue['due_date'])) {
                $nextDueDate = Carbon::parse($freshNextDue['due_date'])->format('M d, Y');
            }
        } elseif (! empty($record->next_due_date)) {
            $nextDueDate = Carbon::parse($record->next_due_date)->format('M d, Y');
        }

        return [
            'record'           => $record,
            'orNumber'         => e($record->reference_number ?? 'N/A'),
            'member'           => e($record->member_name ?? 'Regular Member'),
            'loan'             => e($record->loan_number ?? 'N/A'),
            'amount'           => number_format($amountPaid, 2),
            'rawAmount'        => $amountPaid,
            'remainingBalance' => number_format($remainingBalance, 2),
            'paymentDate'      => $record->payment_date
                                    ? Carbon::parse($record->payment_date)->format('M d, Y')
                                    : 'N/A',
            'generatedAt'      => now()->format('M d, Y h:i A'),
            'method'           => e($record->payment_method ?? 'Cash'),
            'status'           => strtoupper((string) ($record->status ?? 'POSTED')),
            'postedByName'     => e($postedBy?->name ?? $postedBy?->username ?? 'Admin User'),
            'notes'            => e($record->notes ?? ''),
            'nextDueDate'      => $nextDueDate,
            'autoprint'        => $autoprint,
            'downloadName'     => sprintf(
                'receipt-%s.png',
                preg_replace('/[^A-Za-z0-9\-_]/', '-', (string) ($record->reference_number ?: $record->getKey()))
            ),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HTML receipt
    // ─────────────────────────────────────────────────────────────────────────

    private function renderReceipt(CollectionAndPosting $record, bool $autoprint): Response
    {
        $d = $this->receiptData($record, $autoprint);

        $autoPrintJs = $d['autoprint']
            ? '<script>window.addEventListener("load",function(){window.print();});</script>'
            : '';

        $notesBlock = $d['notes'] !== ''
            ? '<div class="notes-box"><div class="notes-lbl">Notes</div><div class="notes-body">' . $d['notes'] . '</div></div>'
            : '';

        $statusColor = match (strtolower($d['status'])) {
            'posted' => '#16a34a',
            'void'   => '#dc2626',
            'draft'  => '#d97706',
            default  => '#6b7280',
        };

        $downloadUrl = route('receipt.download', $record);

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Receipt — {$d['orNumber']}</title>
{$autoPrintJs}
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

*{box-sizing:border-box;margin:0;padding:0;}

body{
    font-family:'Inter',system-ui,sans-serif;
    background:#f0fdf4;
    min-height:100vh;
    display:flex;
    flex-direction:column;
    align-items:center;
    padding:28px 16px;
}

/* ── Toolbar ── */
.toolbar{
    width:100%;max-width:480px;
    display:flex;justify-content:flex-end;
    gap:8px;margin-bottom:16px;flex-wrap:wrap;
}
.btn{
    display:inline-flex;align-items:center;gap:6px;
    padding:9px 16px;border-radius:10px;
    font-size:.8rem;font-weight:700;border:none;cursor:pointer;
    text-decoration:none;transition:all .18s;
}
.btn:hover{transform:translateY(-1px);opacity:.9;}
.btn-print{background:#fff;color:#374151;border:1px solid #d1d5db;box-shadow:0 1px 3px rgba(0,0,0,.08);}
.btn-download{
    background:linear-gradient(135deg,#16a34a,#22c55e);
    color:#fff;
    box-shadow:0 4px 14px rgba(22,163,74,.35);
}
.btn-close{background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;}

/* ── Card ── */
.card{
    width:100%;max-width:480px;
    background:#fff;
    border-radius:24px;
    overflow:hidden;
    box-shadow:0 8px 40px rgba(22,163,74,.12), 0 2px 8px rgba(0,0,0,.06);
    border:1px solid #bbf7d0;
}

/* ── Header strip ── */
.card-header{
    background:linear-gradient(135deg,#14532d 0%,#16a34a 60%,#22c55e 100%);
    padding:28px 28px 22px;
    text-align:center;
    position:relative;
    overflow:hidden;
}
.card-header::before{
    content:'';position:absolute;inset:0;
    background:radial-gradient(circle at 70% 30%,rgba(255,255,255,.12),transparent 55%);
    pointer-events:none;
}
.header-icon{
    width:52px;height:52px;
    background:rgba(255,255,255,.15);
    border:2px solid rgba(255,255,255,.25);
    border-radius:16px;
    display:flex;align-items:center;justify-content:center;
    margin:0 auto 14px;
    color:#fff;
    backdrop-filter:blur(4px);
}
.header-title{
    font-size:1.25rem;font-weight:900;
    color:#fff;letter-spacing:.04em;
    text-transform:uppercase;
}
.header-sub{
    margin-top:4px;font-size:.8rem;font-weight:600;
    color:rgba(255,255,255,.75);letter-spacing:.02em;
}
.or-badge{
    display:inline-block;margin-top:12px;
    background:rgba(255,255,255,.15);
    border:1px solid rgba(255,255,255,.3);
    border-radius:999px;
    padding:4px 14px;
    font-size:.72rem;font-weight:700;
    font-family:monospace;letter-spacing:.1em;
    color:#fff;
    backdrop-filter:blur(4px);
}

/* ── Amount hero ── */
.amount-hero{
    background:linear-gradient(135deg,#f0fdf4,#dcfce7);
    border-bottom:1px solid #bbf7d0;
    padding:20px 28px;
    text-align:center;
}
.amount-lbl{
    font-size:.65rem;font-weight:800;
    text-transform:uppercase;letter-spacing:.12em;
    color:#15803d;margin-bottom:4px;
}
.amount-val{
    font-size:2.6rem;font-weight:900;
    color:#15803d;letter-spacing:-.02em;line-height:1;
}

/* ── Details ── */
.sep-dashed{border-top:2px dashed #bbf7d0;margin:0 24px;}

.details{padding:6px 28px 0;}
.drow{
    display:flex;justify-content:space-between;align-items:center;
    padding:13px 0;border-bottom:1px solid #f0fdf4;gap:12px;
}
.drow:last-child{border-bottom:none;}
.dlbl{font-size:.75rem;font-weight:600;color:#6b7280;flex-shrink:0;}
.dval{font-size:.85rem;font-weight:700;color:#111827;text-align:right;}
.dval.mono{font-family:monospace;font-size:.82rem;}

/* ── Summary boxes ── */
.summary{display:flex;flex-direction:column;gap:8px;padding:0 28px 20px;}
.sbox{
    background:linear-gradient(135deg,#f0fdf4,#dcfce7);
    border:1px solid #bbf7d0;border-radius:12px;
    padding:12px 16px;
    display:flex;justify-content:space-between;align-items:center;gap:10px;
}
.sbox-lbl{font-size:.72rem;font-weight:600;color:#15803d;}
.sbox-val{font-size:.9rem;font-weight:800;color:#14532d;}

/* ── Notes ── */
.notes-box{
    margin:0 28px 20px;
    padding:12px 14px;
    background:#f8fafc;
    border:1px solid #e2e8f0;
    border-radius:10px;
}
.notes-lbl{
    font-size:.62rem;font-weight:800;
    text-transform:uppercase;letter-spacing:.09em;
    color:#94a3b8;margin-bottom:6px;
}
.notes-body{font-size:.8rem;color:#374151;line-height:1.6;white-space:pre-wrap;}

/* ── Footer ── */
.card-footer{
    background:#f8fafc;
    border-top:1px solid #e8f5e9;
    padding:14px 28px;
    text-align:center;
    font-size:.68rem;color:#9ca3af;line-height:1.7;
}
.card-footer strong{color:{$statusColor};font-weight:800;}
.card-footer span{color:#6b7280;}

/* ── Print ── */
@media print{
    body{background:#fff;padding:0;}
    .toolbar{display:none!important;}
    .card{box-shadow:none;border-radius:0;max-width:100%;border:none;}
    @page{margin:8mm;}
}
</style>
</head>
<body>

<div class="toolbar">
  <button class="btn btn-close" onclick="window.close()">✕ Close</button>
  <button class="btn btn-print" onclick="window.print()">
    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
    </svg>
    Print
  </button>
  <a class="btn btn-download" href="{$downloadUrl}">
    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
    </svg>
    Download PNG
  </a>
</div>

<div class="card">

  <!-- Header -->
  <div class="card-header">
    <div class="header-icon">
      <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
    </div>
    <div class="header-title">Payment Receipt</div>
    <div class="header-sub">Payment Posted Successfully &nbsp;·&nbsp; Cooperative Lending System</div>
    <div class="or-badge">{$d['orNumber']}</div>
  </div>

  <!-- Amount hero -->
  <div class="amount-hero">
    <div class="amount-lbl">Amount Paid</div>
    <div class="amount-val">&#x20B1;{$d['amount']}</div>
  </div>

  <div class="sep-dashed" style="margin-top:.5rem;"></div>

  <!-- Details -->
  <div class="details">
    <div class="drow">
      <span class="dlbl">Member</span>
      <span class="dval">{$d['member']}</span>
    </div>
    <div class="drow">
      <span class="dlbl">Loan #</span>
      <span class="dval mono">{$d['loan']}</span>
    </div>
    <div class="drow">
      <span class="dlbl">Payment Date</span>
      <span class="dval">{$d['paymentDate']}</span>
    </div>
    <div class="drow">
      <span class="dlbl">Payment Method</span>
      <span class="dval">{$d['method']}</span>
    </div>
    <div class="drow">
      <span class="dlbl">Posted By</span>
      <span class="dval">{$d['postedByName']}</span>
    </div>
  </div>

  <div class="sep-dashed" style="margin:8px 0 16px;"></div>

  <!-- Summary boxes -->
  <div class="summary">
    <div class="sbox">
      <span class="sbox-lbl">Remaining Balance</span>
      <span class="sbox-val">&#x20B1;{$d['remainingBalance']}</span>
    </div>
    <div class="sbox">
      <span class="sbox-lbl">Next Due Date</span>
      <span class="sbox-val">{$d['nextDueDate']}</span>
    </div>
  </div>

  {$notesBlock}

  <!-- Footer -->
  <div class="card-footer">
    <div>Generated: <span>{$d['generatedAt']}</span></div>
    <div>Status: <strong>{$d['status']}</strong> &nbsp;·&nbsp; This is an official digital receipt.</div>
  </div>

</div>
</body>
</html>
HTML;

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PNG download (GD)
    // ─────────────────────────────────────────────────────────────────────────

    private function renderReceiptImage(array $data): string
    {
        $w = 900;
        $h = 1300;
        $img = imagecreatetruecolor($w, $h);
        imagealphablending($img, true);
        imagesavealpha($img, true);

        // Colours
        $bgCol      = imagecolorallocate($img, 240, 253, 244);   // #f0fdf4
        $headerTop  = imagecolorallocate($img, 20,  83,  45);    // #14532d
        $headerBot  = imagecolorallocate($img, 34, 197,  94);    // #22c55e
        $white      = imagecolorallocate($img, 255, 255, 255);
        $cardBg     = imagecolorallocate($img, 255, 255, 255);
        $greenDark  = imagecolorallocate($img, 21, 128,  61);    // #15803d
        $greenLight = imagecolorallocate($img, 187, 247, 208);   // #bbf7d0
        $greenBg    = imagecolorallocate($img, 220, 252, 231);   // #dcfce7
        $label      = imagecolorallocate($img, 107, 114, 128);   // #6b7280
        $valueFg    = imagecolorallocate($img, 17,  24,  39);    // #111827
        $footerFg   = imagecolorallocate($img, 156, 163, 175);   // #9ca3af
        $sepCol     = imagecolorallocate($img, 187, 247, 208);   // #bbf7d0

        // Background
        imagefilledrectangle($img, 0, 0, $w, $h, $bgCol);

        // Card background
        $pad = 40;
        imagefilledrectangle($img, $pad, $pad, $w - $pad, $h - $pad, $cardBg);

        // Header gradient (simulate with two rectangles)
        $hh = 220;
        imagefilledrectangle($img, $pad, $pad, $w - $pad, $pad + $hh, $headerTop);

        // Header text
        $title = 'PAYMENT RECEIPT';
        $tlen  = imagefontwidth(5) * strlen($title);
        imagestring($img, 5, (int)(($w - $tlen) / 2), $pad + 60, $title, $white);

        $sub = 'Payment Posted Successfully';
        $slen = imagefontwidth(3) * strlen($sub);
        imagestring($img, 3, (int)(($w - $slen) / 2), $pad + 92, $sub, $white);

        $sys = 'Cooperative Lending System';
        $ylen = imagefontwidth(2) * strlen($sys);
        imagestring($img, 2, (int)(($w - $ylen) / 2), $pad + 116, $sys, $white);

        $or  = 'OR: ' . html_entity_decode($data['orNumber']);
        $olen = imagefontwidth(3) * strlen($or);
        imagestring($img, 3, (int)(($w - $olen) / 2), $pad + 148, $or, $white);

        // Amount hero band
        $ay = $pad + $hh;
        imagefilledrectangle($img, $pad, $ay, $w - $pad, $ay + 100, $greenBg);
        $albl = 'AMOUNT PAID';
        $alen = imagefontwidth(2) * strlen($albl);
        imagestring($img, 2, (int)(($w - $alen) / 2), $ay + 14, $albl, $greenDark);
        $aval = 'PHP ' . $data['amount'];
        $avlen = imagefontwidth(5) * strlen($aval);
        imagestring($img, 5, (int)(($w - $avlen) / 2), $ay + 38, $aval, $greenDark);

        // Separator
        imageline($img, $pad + 20, $ay + 100, $w - $pad - 20, $ay + 100, $sepCol);

        // Detail rows
        $rows = [
            ['Member',         html_entity_decode($data['member'])],
            ['Loan #',         html_entity_decode($data['loan'])],
            ['Payment Date',   $data['paymentDate']],
            ['Method',         html_entity_decode($data['method'])],
            ['Posted By',      html_entity_decode($data['postedByName'])],
        ];

        $ry = $ay + 120;
        $lx = $pad + 30;
        $vx = $w - $pad - 30;

        foreach ($rows as [$lbl, $val]) {
            imagestring($img, 4, $lx, $ry, $lbl, $label);
            $vlen2 = imagefontwidth(4) * strlen($val);
            imagestring($img, 4, $vx - $vlen2, $ry, $val, $valueFg);
            $ry += 58;
            imageline($img, $lx, $ry - 16, $w - $pad - $lx + $pad, $ry - 16, $greenLight);
        }

        $ry += 10;
        imageline($img, $pad + 20, $ry, $w - $pad - 20, $ry, $sepCol);
        $ry += 20;

        // Summary boxes
        foreach ([
            ['Remaining Balance', 'PHP ' . $data['remainingBalance']],
            ['Next Due Date',     $data['nextDueDate']],
        ] as [$lbl, $val]) {
            imagefilledrectangle($img, $lx - 10, $ry, $w - $pad - 20, $ry + 60, $greenBg);
            imagerectangle($img, $lx - 10, $ry, $w - $pad - 20, $ry + 60, $greenLight);
            imagestring($img, 3, $lx, $ry + 14, $lbl, $greenDark);
            $vlen3 = imagefontwidth(4) * strlen($val);
            imagestring($img, 4, $w - $pad - 30 - $vlen3, $ry + 18, $val, $greenDark);
            $ry += 78;
        }

        $ry += 10;

        // Footer
        $gen = 'Generated: ' . $data['generatedAt'] . '  |  Status: ' . $data['status'];
        $glen = imagefontwidth(2) * strlen($gen);
        imagestring($img, 2, (int)(($w - $glen) / 2), $ry + 20, $gen, $footerFg);

        // Capture PNG
        ob_start();
        imagepng($img);
        $png = (string) ob_get_clean();
        imagedestroy($img);

        return $png;
    }
}