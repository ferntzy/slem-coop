<div class="cp">
<style>
    * { box-sizing: border-box; }

    .cp {
        --cp-green-950: #052E16;
        --cp-green-900: #14532D;
        --cp-green-700: #15803D;
        --cp-green-600: #16A34A;
        --cp-emerald-500: #10B981;
        --cp-emerald-400: #34D399;
        --cp-emerald-200: #A7F3D0;
        --cp-emerald-50: #ECFDF5;
        --cp-amber-500: #F59E0B;
        --cp-amber-400: #FBBF24;
        --cp-slate-50: #F9FAFB;
        --cp-slate-100: #F3F4F6;
        --cp-slate-200: #E5E7EB;
        --cp-slate-400: #9CA3AF;
        --cp-slate-500: #6B7280;
        --cp-slate-900: #111827;
    }

    /* ── Hero ── */
    .cp-hero { background: linear-gradient(135deg, #14532D 0%, #16A34A 60%, #0b401e 100%); border-radius: 1rem; padding: 2rem 2.5rem; position: relative; overflow: hidden; margin-bottom: 1.5rem; }
    .cp-hero::before { content: ''; position: absolute; top: -60px; right: -60px; width: 220px; height: 220px; border-radius: 50%; background: rgba(255,255,255,0.07); }
    .cp-hero::after  { content: ''; position: absolute; bottom: -40px; left: 30%; width: 140px; height: 140px; border-radius: 50%; background: rgba(255,255,255,0.05); }
    .cp-hero-badge { display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,0.10); border: 1px solid rgba(255,255,255,0.18); color: var(--cp-emerald-200); font-size: 0.7rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; padding: 4px 12px; border-radius: 999px; margin-bottom: 0.75rem; }
    .cp-hero-title { font-size: 1.75rem; font-weight: 800; color: #fff; margin: 0 0 0.4rem; letter-spacing: -0.02em; }
    .cp-hero-sub { color: rgba(255,255,255,0.55); font-size: 0.875rem; margin: 0; }
    .cp-stats { display: flex; gap: 1.5rem; margin-top: 1.5rem; flex-wrap: wrap; }
    .cp-stat { background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.75rem; padding: 0.75rem 1.25rem; min-width: 120px; }
    .cp-stat-value { font-size: 1.4rem; font-weight: 800; color: var(--cp-emerald-200); line-height: 1; }
    .cp-stat-label { font-size: 0.7rem; color: rgba(255,255,255,0.45); text-transform: uppercase; letter-spacing: 0.08em; margin-top: 3px; }

    /* ── Section label ── */
    .cp-section-label { font-size: 0.7rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: var(--cp-slate-500); margin: 0 0 0.5rem; padding-left: 2px; }

    /* ── Feature cards grid ── */
    .cp-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; margin-bottom: 1rem; }
    .cp-card { background: #052E16; border-radius: 1rem; border: 1px solid #14532D; padding: 1.5rem; box-shadow: 0 1px 6px rgba(0,0,0,0.05); transition: all 0.2s ease; position: relative; overflow: hidden; display: flex; flex-direction: column; min-height: 260px; }
    .cp-card:hover { box-shadow: 0 8px 28px rgba(0,0,0,0.1); transform: translateY(-2px); border-color: rgba(52,211,153,0.55); }
    .cp-card-accent { position: absolute; top: 0; left: 0; right: 0; height: 3px; border-radius: 1rem 1rem 0 0; background: linear-gradient(90deg, var(--cp-green-600), var(--cp-emerald-400)); }
    .cp-card-top { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.1rem; }
    .cp-card-icon-wrap { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.10); }
    .cp-card-tag { font-size: 0.62rem; font-weight: 700; padding: 3px 9px; border-radius: 999px; letter-spacing: 0.06em; text-transform: uppercase; white-space: nowrap; align-self: flex-start; margin-top: 2px; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: rgba(236,253,245,0.85); }
    .cp-card-tag-warning { background: rgba(16,185,129,0.12); border-color: rgba(16,185,129,0.25); color: var(--cp-emerald-400); }
    .cp-card-body { flex: 1; }
    .cp-card-title { font-size: 1rem; font-weight: 700; color: #ECFDF5; margin-bottom: 0.45rem; }
    .cp-card-desc { font-size: 0.8rem; color: #ECFDF3; line-height: 1.6; }
    .cp-card-extra { margin-top: 0.75rem; }
    .cp-card-footer { margin-top: 1.25rem; }
    .cp-btn-full { display: flex; align-items: center; justify-content: center; gap: 6px; width: 100%; padding: 10px 16px; font-size: 0.82rem; font-weight: 600; border-radius: 9px; border: 1px solid transparent; cursor: pointer; transition: all 0.15s; font-family: inherit; }
    .cp-btn-full:hover { filter: brightness(1.07); transform: translateY(-1px); }
    .cp-btn-full:focus { outline: none; box-shadow: 0 0 0 3px rgba(16,185,129,0.25); }
    .cp-btn-full:disabled { opacity: 0.65; cursor: not-allowed; transform: none; }
    .cp-btn-primary { background: #c2f2d5; color: #000000; }
    .cp-btn-info    { background: #c2f2d5; color: #000000; }
    .cp-btn-success { background: #10b981; color: #fff; }
    .cp-btn-warning { background: #10b981; color: #fff; }
    .cp-btn-gray    { background: #f3f4f6; border-color: #e5e7eb; color: #111827; }
    .cp-btn-gray:hover { background: #e5e7eb; }

    .cp-audit-row { display: flex; align-items: center; gap: 0.6rem; padding: 0.45rem 0; border-bottom: 1px solid rgba(255,255,255,0.08); font-size: 0.78rem; color: rgba(236,253,245,0.85); }
    .cp-audit-row:last-child { border-bottom: none; }
    .cp-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }

    /* ── Modals ── */
    .cp-modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 1rem; }
    .cp-modal-overlay.cp-open { display: flex; }
    .cp-modal { background: #ffffff; border-radius: 1rem; width: 100%; max-width: 540px; box-shadow: 0 20px 60px rgba(0,0,0,0.2); overflow: hidden; max-height: 92vh; display: flex; flex-direction: column; }
    .cp-modal-header { padding: 1.25rem 1.5rem 1rem; border-bottom: 1px solid #f3f4f6; display: flex; align-items: flex-start; justify-content: space-between; flex-shrink: 0; background: #ffffff; }
    .cp-modal-title { font-size: 0.95rem; font-weight: 800; color: #111827; }
    .cp-modal-sub { font-size: 0.72rem; color: #6b7280; margin-top: 2px; }
    .cp-modal-close { width: 28px; height: 28px; border-radius: 7px; border: none; background: #f3f4f6; cursor: pointer; font-size: 1rem; color: #6b7280; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .cp-modal-close:hover { background: #e5e7eb; }
    .cp-modal-body { padding: 1.5rem; overflow-y: auto; flex: 1; background: #ffffff; }
    .cp-modal-footer { padding: 1rem 1.5rem; border-top: 1px solid #f3f4f6; display: flex; gap: 0.5rem; justify-content: flex-end; flex-shrink: 0; background: #ffffff; }

    .cp-form-group { margin-bottom: 1rem; }
    .cp-form-label { display: block; font-size: 0.73rem; font-weight: 700; color: #374151; margin-bottom: 0.35rem; }
    .cp-form-input, .cp-form-textarea { width: 100%; padding: 0.55rem 0.8rem; border: 1.5px solid #e5e7eb; border-radius: 0.5rem; font-size: 0.82rem; color: #111827; background: #ffffff; transition: border-color 0.15s; font-family: inherit; box-sizing: border-box; }
    .cp-form-input:focus, .cp-form-textarea:focus { outline: none; border-color: #1e3a5f; box-shadow: 0 0 0 3px rgba(30,58,95,0.08); }
    .cp-form-textarea { resize: vertical; min-height: 60px; }

    .cp-ao-row { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 0; border-bottom: 1px solid #f3f4f6; font-size: 0.78rem; }
    .cp-ao-row:last-child { border-bottom: none; }

    .cp-summary-grid { background: #f9fafb; border-radius: 0.75rem; padding: 1rem; margin-bottom: 1rem; display: grid; grid-template-columns: repeat(3,1fr); gap: 0.75rem; text-align: center; }
    .cp-summary-val { font-size: 1.1rem; font-weight: 800; }
    .cp-summary-lbl { font-size: 0.65rem; color: #9ca3af; text-transform: uppercase; margin-top: 2px; }
    .cp-variance-row { display: flex; justify-content: space-between; align-items: center; padding: 0.6rem 0.8rem; border-radius: 0.5rem; font-size: 0.82rem; font-weight: 700; margin-top: 0.75rem; }

    .cp-empty { text-align: center; padding: 2rem 1rem; color: #9ca3af; font-size: 0.8rem; }
    .cp-empty svg { margin: 0 auto 0.5rem; display: block; opacity: 0.35; color: #9ca3af; }

    /* ── Receipt ── */
    .cp-receipt-row { display: flex; justify-content: space-between; align-items: center; padding: .45rem 0; border-bottom: 1px solid #f3f4f6; font-size: .82rem; }
    .cp-receipt-row:last-child { border-bottom: none; }
    .cp-receipt-label { color: #6b7280; }
    .cp-receipt-value { font-weight: 600; color: #111827; }
    .cp-receipt-amount-row { display: flex; justify-content: space-between; align-items: center; padding: .75rem 0; border-bottom: 2px solid #e5e7eb; }
    .cp-receipt-balance-box { display: flex; justify-content: space-between; background: #f0fdf4; border-radius: .5rem; padding: .5rem .75rem; margin-top: .6rem; font-size: .75rem; }
    .cp-receipt-due-box { display: flex; justify-content: space-between; background: #ecfdf5; border: 1px solid rgba(16,185,129,0.18); border-radius: .5rem; padding: .5rem .75rem; margin-top: .4rem; font-size: .75rem; }

    /* ── Toast ── */
    .cp-toast { position: fixed; bottom: 1.5rem; right: 1.5rem; background: #111827; color: #fff; padding: 0.7rem 1.2rem; border-radius: 0.75rem; font-size: 0.8rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; z-index: 99999; transform: translateY(80px); opacity: 0; transition: all 0.3s ease; box-shadow: 0 8px 24px rgba(0,0,0,0.2); pointer-events: none; }
    .cp-toast.cp-toast-show { transform: translateY(0); opacity: 1; }
    .cp-toast-dot { width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; flex-shrink: 0; }
    .cp-toast-success .cp-toast-dot { background: #10b981; }
    .cp-toast-danger  .cp-toast-dot { background: #ef4444; }
    .cp-toast-warning .cp-toast-dot { background: #f59e0b; }

 /* ══════════════════════════════════════
   DARK MODE OVERRIDES (FIXED)
══════════════════════════════════════ */

/* Modal container */
.dark .cp-modal {
    background: #000000;
}

/* Header / Body / Footer */
.dark .cp-modal-header,
.dark .cp-modal-body,
.dark .cp-modal-footer {
    background: #000000;
}

/* Borders */
.dark .cp-modal-header {
    border-bottom-color: #0f172a;
}
.dark .cp-modal-footer {
    border-top-color: #0f172a;
}

/* Title + subtitle */
.dark .cp-modal-title { color: #f8fafc; }
.dark .cp-modal-sub   { color: #94a3b8; }

/* Close button */
.dark .cp-modal-close {
    background: #000207;
    color: #94a3b8;
}
.dark .cp-modal-close:hover {
    background: #1e293b;
    color: #e2e8f0;
}

/* ❗ FIX: remove white/gray box inside modal */
.dark .cp-modal-body > div {
    background: transparent !important;
    border-color: #0f172a !important;
}

/* ❗ FIX: empty state (your screenshot issue) */
.dark .cp-empty {
    background: #020617;
    color: #64748b;
}
.dark .cp-empty svg {
    color: #334155;
}

/* Buttons */
.dark .cp-btn-gray {
    background: #0f172a;
    border-color: #1e293b;
    color: #e2e8f0;
}
.dark .cp-btn-gray:hover {
    background: #1e293b;
}

/* Rows */
.dark .cp-ao-row {
    border-bottom-color: #0f172a;
    color: #cbd5e1;
}

/* Inputs */
.dark .cp-form-label { color: #cbd5e1; }

.dark .cp-form-input,
.dark .cp-form-textarea {
    background: #020617;
    border-color: #0f172a;
    color: #f8fafc;
}

.dark .cp-form-input::placeholder,
.dark .cp-form-textarea::placeholder {
    color: #475569;
}

.dark .cp-form-input:focus,
.dark .cp-form-textarea:focus {
    border-color: #34d399;
    box-shadow: 0 0 0 3px rgba(52,211,153,0.12);
}

/* Receipt */
.dark #cp-receipt-printable {
    background: #020617;
    border-color: #0f172a;
}

.dark .cp-receipt-row {
    border-bottom-color: #0f172a;
}

.dark .cp-receipt-label { color: #94a3b8; }
.dark .cp-receipt-value { color: #f8fafc; }

.dark .cp-receipt-amount-row {
    border-bottom-color: #0f172a;
}

.dark .cp-receipt-balance-box,
.dark .cp-receipt-due-box {
    background: #052e16;
    border-color: rgba(52,211,153,0.2);
}

/* Receipt header text */
.dark #cp-receipt-printable .receipt-org-name {
    color: #f8fafc !important;
}
.dark #cp-receipt-printable .receipt-footer-txt {
    color: #64748b !important;
}

/* Summary */
.dark .cp-summary-grid {
    background: #020617;
}
.dark .cp-summary-lbl {
    color: #64748b;
}

/* Variance */
.dark .cp-variance-row {
    color: #f8fafc;
}

/* LIGHT MODE */
.cp-receipt-balance-box span:first-child,
.cp-receipt-due-box span:first-child {
    color: #6b7280;
}

.cp-receipt-balance-box span:last-child,
.cp-receipt-due-box span:last-child {
    color: #111827;
    font-weight: 700;
}

/* DARK MODE FIX */
.dark .cp-receipt-balance-box {
    background: #052e16;
}

.dark .cp-receipt-due-box {
    background: #022c22;
}

.dark .cp-receipt-balance-box span:first-child,
.dark .cp-receipt-due-box span:first-child {
    color: #94a3b8; /* softer label */
}

.dark .cp-receipt-balance-box span:last-child,
.dark .cp-receipt-due-box span:last-child {
    color: #f8fafc; /* strong readable value */
    font-weight: 700;
}

    @media (max-width: 640px) {
        .cp-hero { padding: 1.5rem; }
        .cp-hero-title { font-size: 1.3rem; }
        .cp-stats { gap: 0.75rem; }
        .cp-stat { min-width: 90px; }
        .cp-grid { grid-template-columns: 1fr; }
    }

    .fi-page-content > div { gap: 0 !important; }
    .fi-page-content > div > div { padding-top: 0 !important; padding-bottom: 0 !important; margin-top: 0 !important; margin-bottom: 0 !important; }
    .fi-ta-ctn { margin-top: 0 !important; margin-bottom: 0 !important; }
</style>

{{-- ── Hero ── --}}
<div class="cp-hero">
    <div class="cp-hero-badge">
        <svg width="10" height="10" viewBox="0 0 10 10" fill="currentColor"><circle cx="5" cy="5" r="5"/></svg>
        Payment Management
    </div>
    <h1 class="cp-hero-title">Cash &amp; Manual Payments</h1>
    <p class="cp-hero-sub">Manage cash payments, receipts, uploads, and daily collection entries with a full audit trail.</p>
    <div class="cp-stats">
        <div class="cp-stat">
            <div class="cp-stat-value">₱{{ number_format($todayTotal ?? 0, 2) }}</div>
            <div class="cp-stat-label">Today's Collection</div>
        </div>
        <div class="cp-stat">
            <div class="cp-stat-value">{{ $todayCount ?? 0 }}</div>
            <div class="cp-stat-label">Transactions</div>
        </div>
        <div class="cp-stat">
            <div class="cp-stat-value">{{ $pendingCount ?? 0 }}</div>
            <div class="cp-stat-label">Pending Posts</div>
        </div>
    </div>
</div>

{{-- ── Feature Cards ── --}}
<p class="cp-section-label">Features &amp; Actions</p>
<div class="cp-grid">

    {{-- Payment Collection --}}
    <div class="cp-card">
        <div class="cp-card-accent"></div>
        <div class="cp-card-top">
            <div class="cp-card-icon-wrap">
                <svg width="22" height="22" style="color:#a7f3d0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <span class="cp-card-tag">Cash Only</span>
        </div>
        <div class="cp-card-body">
            <div class="cp-card-title">Payment Collection</div>
            <div class="cp-card-desc">Handles cash payments only. Search member, select their active loan, and post payment with proof of payment attached.</div>
        </div>
        <div class="cp-card-footer">
            <button class="cp-btn-full cp-btn-primary" onclick="window.cpTriggerRecordPayment()">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Record Payment
            </button>
        </div>
    </div>

    {{-- Pending Payments --}}
    <div class="cp-card">
        <div class="cp-card-accent cp-card-accent-warning"></div>
        <div class="cp-card-top">
            <div class="cp-card-icon-wrap">
                <svg width="22" height="22" style="color:#10b981;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zM12 15.75h.008v.008H12v-.008z"/>
                </svg>
            </div>
            <span class="cp-card-tag cp-card-tag-warning">Needs Review</span>
        </div>
        <div class="cp-card-body">
            <div class="cp-card-title">Pending Payments</div>
            <div class="cp-card-desc">Payments submitted for approval (status: Draft). Approve or reject from the table actions.</div>
            <div class="cp-card-extra">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;">
                    <div style="font-size:.75rem;color:#9ca3af;">Pending count</div>
                    <div style="font-weight:800;color:#059669;">{{ $pendingCount ?? 0 }}</div>
                </div>
                @if(isset($pendingPayments) && $pendingPayments->isNotEmpty())
                    @foreach($pendingPayments->take(2) as $p)
                        <div class="cp-audit-row">
                            <div class="cp-dot" style="background:#10b981;"></div>
                            <span style="flex:1;">
                                {{ $p->member_name ?? 'Unknown' }}
                                <span style="opacity:.65;">({{ $p->loan_number ?? 'N/A' }})</span>
                            </span>
                            <span style="font-weight:800;color:#059669;">₱{{ number_format((float)($p->amount_paid ?? 0), 2) }}</span>
                        </div>
                    @endforeach
                @else
                    <div style="font-size:0.75rem;color:#9ca3af;">No pending payments right now.</div>
                @endif
            </div>
        </div>
        <div class="cp-card-footer">
            <button class="cp-btn-full cp-btn-warning" onclick="window.cpOpenModal('cp-modal-pending')">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                View Pending
            </button>
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════════
     RECEIPT MODAL
══════════════════════════════════════════════════════════════════ --}}
<div class="cp-modal-overlay" id="cp-modal-receipt" onclick="window.cpBgClose(event,'cp-modal-receipt')">
    <div class="cp-modal" style="max-width:500px;">
        <div class="cp-modal-header">
            <div>
                <div class="cp-modal-title" style="display:flex;align-items:center;gap:.4rem;">
                    <svg style="width:18px;height:18px;color:#059669;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Payment Receipt
                </div>
                <div class="cp-modal-sub">Payment posted successfully</div>
            </div>
            <button class="cp-modal-close" onclick="window.cpCloseModal('cp-modal-receipt')">✕</button>
        </div>

        <div class="cp-modal-body">
            <div id="cp-receipt-printable" style="background:#f9fafb;border:1px dashed #d1d5db;border-radius:.75rem;padding:1.5rem;">

                <div style="text-align:center;margin-bottom:1rem;padding-bottom:.75rem;border-bottom:1px dashed #e5e7eb;">
                    <div style="display:flex;justify-content:center;margin-bottom:.4rem;">
                        <svg style="width:34px;height:34px;color:#059669;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="receipt-org-name" style="font-weight:800;font-size:1rem;color:#111827;letter-spacing:.05em;">PAYMENT RECEIPT</div>
                    <div style="font-size:.68rem;color:#059669;font-weight:600;margin-top:2px;">Payment Posted Successfully</div>
                    <div class="receipt-footer-txt" style="font-size:.65rem;color:#9ca3af;margin-top:2px;">Cooperative Lending System</div>
                </div>

                <div>
                    <div class="cp-receipt-row">
                        <span class="cp-receipt-label">Member</span>
                        <span class="cp-receipt-value" id="rcpt-member">—</span>
                    </div>
                    <div class="cp-receipt-row">
                        <span class="cp-receipt-label">Loan #</span>
                        <span class="cp-receipt-value" id="rcpt-loan">—</span>
                    </div>
                    <div class="cp-receipt-row">
                        <span class="cp-receipt-label">Payment Date</span>
                        <span class="cp-receipt-value" id="rcpt-date">—</span>
                    </div>
                    <div class="cp-receipt-row">
                        <span class="cp-receipt-label">Method</span>
                        <span class="cp-receipt-value" id="rcpt-method">Cash</span>
                    </div>
                    <div class="cp-receipt-row">
                        <span class="cp-receipt-label">Posted By</span>
                        <span class="cp-receipt-value" id="rcpt-posted-by">—</span>
                    </div>
                    <div class="cp-receipt-amount-row">
                        <span style="font-weight:800;font-size:.95rem;color:#111827;">Amount Paid</span>
                        <span style="font-weight:800;font-size:.95rem;color:#059669;" id="rcpt-amount">—</span>
                    </div>
                  <div class="cp-receipt-balance-box">
                        <span>Remaining Balance</span>
                        <span id="rcpt-balance">—</span>
                    </div>
                   <div class="cp-receipt-due-box">
                        <span>Next Due Date</span>
                        <span id="rcpt-next-due">—</span>
                    </div>
                </div>

                <div class="receipt-footer-txt" style="text-align:center;margin-top:1rem;padding-top:.75rem;border-top:1px dashed #e5e7eb;font-size:.65rem;color:#9ca3af;">
                    <span id="rcpt-generated">—</span>
                    &nbsp;·&nbsp;
                    Status: <span style="color:#059669;font-weight:700;">POSTED</span>
                </div>

            </div>
        </div>

        <div class="cp-modal-footer">
            <button class="cp-btn-full cp-btn-gray" style="width:auto;padding:8px 20px;" onclick="window.cpCloseModal('cp-modal-receipt')">Close</button>
            <button class="cp-btn-full cp-btn-success" id="cp-btn-download" style="width:auto;padding:8px 20px;" onclick="window.cpDownloadReceipt()">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
                </svg>
                Download PNG
            </button>
        </div>
    </div>
</div>

{{-- ── Daily Collection Modal ── --}}
<div class="cp-modal-overlay" id="cp-modal-daily" onclick="window.cpBgClose(event,'cp-modal-daily')">
    <div class="cp-modal" style="max-width:580px;">
        <div class="cp-modal-header">
            <div>
                <div class="cp-modal-title">Daily Collection Summary</div>
                <div class="cp-modal-sub">Per Account Officer — auto-calculated from posted payments</div>
            </div>
            <button class="cp-modal-close" onclick="window.cpCloseModal('cp-modal-daily')">✕</button>
        </div>
        <div class="cp-modal-body">
            <div class="cp-summary-grid">
                <div>
                    <div class="cp-summary-val" style="color:#059669;">₱{{ number_format($todayTotal ?? 0, 2) }}</div>
                    <div class="cp-summary-lbl">System Total</div>
                </div>
                <div>
                    <div class="cp-summary-val" style="color:#16a34a;">{{ $todayCount ?? 0 }}</div>
                    <div class="cp-summary-lbl">Transactions</div>
                </div>
                <div>
                    <div class="cp-summary-val" style="color:#065f46;">{{ ($dailyByAo ?? collect())->count() }}</div>
                    <div class="cp-summary-lbl">Active AOs</div>
                </div>
            </div>
            @if(isset($dailyByAo) && $dailyByAo->isNotEmpty())
                <div style="font-size:0.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:0.5rem;">Breakdown by Account Officer</div>
                @foreach($dailyByAo as $ao)
                    <div class="cp-ao-row">
                        <div style="width:34px;height:34px;border-radius:50%;background:#eff6ff;display:flex;align-items:center;justify-content:center;font-size:0.7rem;font-weight:800;color:#1e3a5f;flex-shrink:0;">
                            {{ strtoupper(substr($ao['name'], 0, 2)) }}
                        </div>
                        <div style="flex:1;">
                            <div style="font-size:0.82rem;font-weight:700;">{{ $ao['name'] }}</div>
                            <div style="font-size:0.7rem;color:#9ca3af;">{{ $ao['count'] }} {{ $ao['count'] == 1 ? 'transaction' : 'transactions' }}</div>
                        </div>
                        <div style="font-weight:700;color:#059669;">₱{{ number_format($ao['total'], 2) }}</div>
                    </div>
                @endforeach
            @else
                <div class="cp-empty">
                    <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    No collections posted today yet.
                </div>
            @endif
            <div style="margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid #334155;">
                <div style="font-size:0.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:0.75rem;">Submit Your Cash on Hand</div>
                <div class="cp-form-group">
                    <label class="cp-form-label">Cash on Hand (₱)</label>
                    <input class="cp-form-input" type="number" id="cp-cash-on-hand" step="0.01" min="0" placeholder="0.00" oninput="window.cpCalcVariance()">
                </div>
                <div id="cp-variance-display" style="display:none;">
                    <div class="cp-variance-row" id="cp-variance-row">
                        <span>Variance</span>
                        <span id="cp-variance-val">₱0.00</span>
                    </div>
                </div>
                <div class="cp-form-group" style="margin-top:0.75rem;">
                    <label class="cp-form-label">Notes (optional)</label>
                    <textarea class="cp-form-textarea" id="cp-daily-notes" placeholder="Any discrepancy explanation..."></textarea>
                </div>
            </div>
        </div>
        <div class="cp-modal-footer">
            <button class="cp-btn-full cp-btn-gray" style="width:auto;padding:6px 16px;" onclick="window.cpCloseModal('cp-modal-daily')">Close</button>
            <button class="cp-btn-full cp-btn-info" style="width:auto;padding:6px 16px;" onclick="window.cpSubmitDaily()">
                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Submit
            </button>
        </div>
    </div>
</div>

{{-- ── Pending Payments Modal ── --}}
<div class="cp-modal-overlay" id="cp-modal-pending" onclick="window.cpBgClose(event,'cp-modal-pending')">
    <div class="cp-modal" style="max-width:720px;">
        <div class="cp-modal-header">
            <div>
                <div class="cp-modal-title">Pending Payments</div>
                <div class="cp-modal-sub">Draft payments awaiting approval</div>
            </div>
            <button class="cp-modal-close" onclick="window.cpCloseModal('cp-modal-pending')">✕</button>
        </div>
        <div class="cp-modal-body">
            @if(isset($pendingPayments) && $pendingPayments->isNotEmpty())
                <div style="font-size:0.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:0.5rem;">
                    Latest pending submissions
                </div>
                @foreach($pendingPayments as $p)
                    <div class="cp-ao-row">
                        <div style="width:34px;height:34px;border-radius:50%;background:#ecfdf5;display:flex;align-items:center;justify-content:center;font-size:0.7rem;font-weight:800;color:#059669;flex-shrink:0;">
                            {{ strtoupper(substr((string)($p->member_name ?? 'U'), 0, 2)) }}
                        </div>
                        <div style="flex:1;">
                            <div style="font-size:0.82rem;font-weight:700;">
                                {{ $p->member_name ?? 'Unknown' }}
                                <span style="font-weight:600;color:#9ca3af;">— {{ $p->loan_number ?? 'N/A' }}</span>
                            </div>
                            <div style="font-size:0.7rem;color:#9ca3af;">
                                {{ optional($p->payment_date)->format('M d, Y') ?? '—' }}
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-weight:800;color:#059669;">₱{{ number_format((float)($p->amount_paid ?? 0), 2) }}</div>
                            <a href="{{ \App\Filament\Resources\CollectionAndPostings\CollectionAndPostingResource::getUrl('view', ['record' => $p]) }}"
                               style="display:inline-block;margin-top:4px;font-size:.72rem;font-weight:700;color:#2563eb;text-decoration:none;">
                                Open
                            </a>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="cp-empty">
                    <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    No pending payments right now.
                </div>
            @endif
        </div>
        <div class="cp-modal-footer">
            <button class="cp-btn-full cp-btn-gray" style="width:auto;padding:6px 16px;" onclick="window.cpCloseModal('cp-modal-pending')">Close</button>
        </div>
    </div>
</div>

{{-- ── Toast ── --}}
<div class="cp-toast" id="cp-toast">
    <div class="cp-toast-dot" id="cp-toast-dot">✓</div>
    <span id="cp-toast-msg"></span>
</div>

<script>
(function () {
    if (window.__cpInitialized) return;
    window.__cpInitialized = true;

    var cpSystemTotal = {{ $todayTotal ?? 0 }};
    var cpToastTimer;
    var cpReceiptRecordId = null;
    var cpReceiptDownloadUrl = null;

    window.cpTriggerRecordPayment = function () { Livewire.dispatch('open-record-payment'); };
    window.cpOpenModal  = function (id) { var el = document.getElementById(id); if (el) el.classList.add('cp-open'); };
    window.cpCloseModal = function (id) { var el = document.getElementById(id); if (el) el.classList.remove('cp-open'); };
    window.cpBgClose    = function (e, id) { if (e.target === e.currentTarget) window.cpCloseModal(id); };

    window.cpCalcVariance = function () {
        var cash     = parseFloat(document.getElementById('cp-cash-on-hand').value) || 0;
        var variance = cpSystemTotal - cash;
        var display  = document.getElementById('cp-variance-display');
        var row      = document.getElementById('cp-variance-row');
        var val      = document.getElementById('cp-variance-val');
        if (!display) return;
        display.style.display = 'block';
        if (variance === 0) {
            row.style.background = '#ecfdf5'; row.style.color = '#059669';
            val.textContent = '₱0.00 — Balanced';
        } else if (variance > 0) {
            row.style.background = '#fef2f2'; row.style.color = '#dc2626';
            val.textContent = '-₱' + variance.toLocaleString('en-PH', {minimumFractionDigits:2,maximumFractionDigits:2}) + ' Short';
        } else {
            row.style.background = '#ecfdf5'; row.style.color = '#059669';
            val.textContent = '+₱' + Math.abs(variance).toLocaleString('en-PH', {minimumFractionDigits:2,maximumFractionDigits:2}) + ' Over';
        }
    };

    window.cpSubmitDaily = function () {
        var cash = document.getElementById('cp-cash-on-hand').value;
        if (!cash) { window.cpToast('Please enter cash on hand amount.', 'warning'); return; }
        window.cpToast('Daily collection submitted successfully.', 'success');
        window.cpCloseModal('cp-modal-daily');
    };

    window.cpToast = function (msg, type) {
        type = type || 'success';
        var t = document.getElementById('cp-toast');
        if (!t) return;
        t.className = 'cp-toast cp-toast-' + type;
        document.getElementById('cp-toast-dot').textContent = type === 'success' ? '✓' : type === 'warning' ? '!' : '✕';
        document.getElementById('cp-toast-msg').textContent = msg;
        t.classList.add('cp-toast-show');
        clearTimeout(cpToastTimer);
        cpToastTimer = setTimeout(function () { t.classList.remove('cp-toast-show'); }, 3000);
    };

    if (!window.html2canvas) {
        var s = document.createElement('script');
        s.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
        document.head.appendChild(s);
    }

    window.cpDownloadReceipt = function () {
        var downloadUrl = cpReceiptDownloadUrl;

        if (!downloadUrl && cpReceiptRecordId) {
            downloadUrl = '/receipts/' + encodeURIComponent(cpReceiptRecordId) + '/download';
        }

        if (downloadUrl) {
            window.location.href = downloadUrl;
            window.cpToast('Preparing PNG download...', 'success');
            return;
        }

        if (!window.html2canvas) { window.cpToast('Still loading, please try again.', 'warning'); return; }
        var btn      = document.getElementById('cp-btn-download');
        var original = btn.innerHTML;
        btn.disabled    = true;
        btn.textContent = 'Generating…';
        window.html2canvas(document.getElementById('cp-receipt-printable'), {
            scale: 2, useCORS: true, backgroundColor: '#f9fafb', logging: false,
        }).then(function (canvas) {
            var filename = 'Receipt-' + Date.now() + '.png';
            var link     = document.createElement('a');
            link.href    = canvas.toDataURL('image/png');
            link.download = filename;
            link.click();
            btn.disabled  = false;
            btn.innerHTML = original;
            window.cpToast('Downloaded as ' + filename, 'success');
        }).catch(function () {
            btn.disabled  = false;
            btn.innerHTML = original;
            window.cpToast('Download failed. Please try again.', 'danger');
        });
    };

    function attachReceiptListener() {
        if (window.Livewire) {
            Livewire.on('show-receipt', function (payload) {
                var d = Array.isArray(payload) ? payload[0] : payload;

                if (d && d.detail && typeof d.detail === 'object') {
                    d = d.detail;
                }

                cpReceiptRecordId = d.record_id || d.recordId || d.id || null;
                cpReceiptDownloadUrl = d.download_url || d.downloadUrl || null;
                document.getElementById('rcpt-member').textContent    = d.member        || '—';
                document.getElementById('rcpt-loan').textContent      = d.loan          || '—';
                document.getElementById('rcpt-date').textContent      = d.date          || '—';
                document.getElementById('rcpt-method').textContent    = d.method        || 'Cash';
                document.getElementById('rcpt-posted-by').textContent = d.posted_by     || '—';
                document.getElementById('rcpt-amount').textContent    = d.amount        || '—';
                document.getElementById('rcpt-balance').textContent   = d.balance       || '—';
                document.getElementById('rcpt-next-due').textContent  = d.next_due_date || '—';
                document.getElementById('rcpt-generated').textContent = 'Generated on ' + (d.generated_at || '—');
                window.cpOpenModal('cp-modal-receipt');
            });
        } else {
            setTimeout(attachReceiptListener, 200);
        }
    }
    attachReceiptListener();

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            ['cp-modal-receipt','cp-modal-daily','cp-modal-pending'].forEach(window.cpCloseModal);
        }
    });

    var st = document.createElement('style');
    st.textContent = '.fi-page-content{gap:0!important}.fi-page-content>*{margin-top:0!important;margin-bottom:0!important}.fi-ta-ctn,.fi-ta{margin-top:0!important;margin-bottom:0!important}';
    document.head.appendChild(st);
})();
</script>
</div>