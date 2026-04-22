<?php

namespace App\Http\Controllers;

use App\Models\CollectionAndPosting;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ReceiptController extends Controller
{
    /**
     * Download the receipt as a PDF file.
     */
    public function download(CollectionAndPosting $record): SymfonyResponse
    {
        $data = $this->receiptData($record, autoprint: false);

        return Pdf::loadView('receipts.pdf', $data)
            ->download($data['downloadName']);
    }

    public function print(CollectionAndPosting $record): Response
    {
        return $this->renderReceipt($record, autoprint: true);
    }

    /**
     * @return array<string, mixed>
     */
    private function receiptData(CollectionAndPosting $record, bool $autoprint): array
    {
        $postedBy = User::find($record->posted_by_user_id);

        return [
            'record' => $record,
            'postedBy' => $postedBy,
            'orNumber' => e($record->reference_number ?? 'N/A'),
            'member' => e($record->member_name ?? 'N/A'),
            'loan' => e($record->loan_number ?? 'N/A'),
            'amount' => number_format((float) $record->amount_paid, 2),
            'date' => $record->payment_date
              ? Carbon::parse($record->payment_date)->format('F j, Y')
              : 'N/A',
            'method' => e($record->payment_method ?? 'Cash'),
            'status' => e($record->status ?? 'Posted'),
            'postedByName' => e($postedBy?->name ?? 'System'),
            'generatedAt' => now()->format('F j, Y h:i A'),
            'notes' => e($record->notes ?? ''),
            'statusClass' => match (strtolower((string) $record->status)) {
                'posted' => 'badge-green',
                'draft' => 'badge-yellow',
                'void' => 'badge-red',
                default => 'badge-gray',
            },
            'autoprint' => $autoprint,
            'downloadName' => sprintf(
                'receipt-%s.pdf',
                $record->reference_number ?: $record->getKey()
            ),
        ];
    }

    private function renderReceipt(CollectionAndPosting $record, bool $autoprint): Response
    {
        $data = $this->receiptData($record, $autoprint);
        $orNumber = $data['orNumber'];
        $amount = $data['amount'];
        $member = $data['member'];
        $loan = $data['loan'];
        $date = $data['date'];
        $method = $data['method'];
        $status = $data['status'];
        $postedByName = $data['postedByName'];
        $generatedAt = $data['generatedAt'];
        $statusClass = $data['statusClass'];
        $notes = $data['notes'];

        $notesBlock = $data['notes']
          ? "<div class='notes-box'><div class='notes-label'>Notes</div>{$notes}</div>"
          : '';
        $autoPrintJs = $data['autoprint']
          ? '<script>window.addEventListener("load",function(){window.print();});</script>'
          : '';
        $voidStamp = strtolower($record->status) === 'void'
          ? '<div class="void-stamp">VOID</div>'
          : '';

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
      <title>Receipt — {$orNumber}</title>
{$autoPrintJs}
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:system-ui,-apple-system,sans-serif;background:#f1f5f9;min-height:100vh;display:flex;flex-direction:column;align-items:center;padding:2rem 1rem;}
.toolbar{display:flex;gap:.6rem;margin-bottom:1.25rem;width:100%;max-width:460px;}
.btn{display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1.1rem;border-radius:8px;font-size:.8rem;font-weight:600;border:none;cursor:pointer;text-decoration:none;transition:opacity .15s;}
.btn:hover{opacity:.82;}
.btn-green{background:#059669;color:#fff;}
.btn-gray{background:#fff;color:#374151;border:1px solid #d1d5db;}
.receipt{background:#fff;border-radius:16px;box-shadow:0 4px 32px rgba(0,0,0,.1);overflow:hidden;width:100%;max-width:460px;position:relative;}
.r-header{background:linear-gradient(135deg,#1e3a5f,#2563eb);padding:1.8rem 1.8rem 1.5rem;color:#fff;text-align:center;}
.r-header .coop{font-size:.78rem;font-weight:700;letter-spacing:.06em;opacity:.75;margin-bottom:.3rem;text-transform:uppercase;}
.r-header h1{font-size:1.3rem;font-weight:800;}
.r-header .or{display:inline-block;margin-top:.6rem;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.25);border-radius:999px;padding:.22rem .85rem;font-size:.72rem;font-weight:700;letter-spacing:.1em;font-family:monospace;}
.amount-strip{background:linear-gradient(135deg,#f0fdf4,#dcfce7);border-bottom:1px solid #bbf7d0;padding:1.3rem;text-align:center;}
.amount-label{font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#15803d;margin-bottom:.25rem;}
.amount-value{font-size:2.4rem;font-weight:800;color:#059669;letter-spacing:-.02em;line-height:1;}
.sep{border-top:2px dashed #e5e7eb;margin:0 1.4rem;}
.details{padding:1.1rem 1.6rem;}
.row{display:flex;justify-content:space-between;align-items:center;padding:.5rem 0;border-bottom:1px solid #f3f4f6;gap:1rem;}
.row:last-child{border:none;}
.rlabel{font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;flex-shrink:0;}
.rvalue{font-size:.82rem;font-weight:600;color:#111827;text-align:right;}
.badge{display:inline-block;padding:2px 8px;border-radius:999px;font-size:.68rem;font-weight:700;}
.badge-green{background:#dcfce7;color:#15803d;}
.badge-yellow{background:#fef3c7;color:#92400e;}
.badge-red{background:#fee2e2;color:#991b1b;}
.badge-gray{background:#f3f4f6;color:#374151;}
.notes-box{margin:0 1.6rem .8rem;padding:.7rem 1rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;font-size:.78rem;color:#64748b;line-height:1.5;}
.notes-label{font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;margin-bottom:.2rem;}
.r-footer{background:#f8fafc;border-top:1px solid #e2e8f0;padding:.85rem 1.6rem;text-align:center;font-size:.65rem;color:#94a3b8;line-height:1.7;}
.r-footer strong{color:#64748b;}
.void-stamp{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%) rotate(-35deg);font-size:5rem;font-weight:900;color:rgba(239,68,68,.1);pointer-events:none;letter-spacing:.1em;white-space:nowrap;}
@media print{
  body{background:#fff;padding:0;}
  .toolbar{display:none!important;}
  .receipt{box-shadow:none;border-radius:0;max-width:100%;}
}
</style>
</head>
<body>

<div class="toolbar">
  <button class="btn btn-green" onclick="window.print()">
    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
    Print / Save as PDF
  </button>
  <button class="btn btn-gray" onclick="window.close()">✕ Close</button>
</div>

<div class="receipt">
  {$voidStamp}

  <div class="r-header">
    <div class="coop">Cooperative Management System</div>
    <h1>Official Receipt</h1>
    <div class="or">{$orNumber}</div>
  </div>

  <div class="amount-strip">
    <div class="amount-label">Amount Paid</div>
    <div class="amount-value">₱{$amount}</div>
  </div>

  <div class="sep" style="margin-top:.1rem;"></div>

  <div class="details">
    <div class="row">
      <span class="rlabel">Member</span>
      <span class="rvalue">{$member}</span>
    </div>
    <div class="row">
      <span class="rlabel">Loan Account</span>
      <span class="rvalue" style="font-family:monospace;">{$loan}</span>
    </div>
    <div class="row">
      <span class="rlabel">Payment Date</span>
      <span class="rvalue">{$date}</span>
    </div>
    <div class="row">
      <span class="rlabel">Payment Method</span>
      <span class="rvalue">{$method}</span>
    </div>
    <div class="row">
      <span class="rlabel">Status</span>
      <span class="rvalue"><span class="badge {$statusClass}">{$status}</span></span>
    </div>
    <div class="row">
      <span class="rlabel">Posted By</span>
      <span class="rvalue">{$postedByName}</span>
    </div>
  </div>

  {$notesBlock}

  <div class="sep"></div>

  <div class="r-footer">
    Generated: <strong>{$generatedAt}</strong><br>
    This is an official digital receipt. Please keep this for your records.
  </div>
</div>

</body>
</html>
HTML;

        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}
