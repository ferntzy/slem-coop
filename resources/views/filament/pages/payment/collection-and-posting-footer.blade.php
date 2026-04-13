<div>
<style>
    .cp-badge { display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 999px; font-size: 0.65rem; font-weight: 700; }
    .cp-badge-success { background: #ecfdf5; color: #059669; }
    .cp-badge-warning { background: #fffbeb; color: #d97706; }
    .cp-badge-danger  { background: #fef2f2; color: #dc2626; }
    .cp-badge-gray    { background: #f3f4f6; color: #374151; }
    .cp-btn { display: inline-flex; align-items: center; gap: 5px; font-size: 0.75rem; font-weight: 600; padding: 6px 14px; border-radius: 7px; border: none; cursor: pointer; transition: all 0.15s; font-family: inherit; }
    .cp-btn:hover { filter: brightness(1.08); transform: scale(1.02); }
    .cp-btn-gray { background: #f3f4f6; color: #374151; }
    .cp-tag { font-size: 0.65rem; font-weight: 600; padding: 3px 8px; border-radius: 999px; letter-spacing: 0.04em; }

    /* ── Audit Trail Section ── */
    .cp-audit-section { background: #fff; border: 1px solid #e2e8f0; border-radius: 0.875rem; box-shadow: 0 1px 4px rgba(0,0,0,0.04); overflow: hidden; margin-top: 0; }
    .cp-audit-section-header { display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; background: linear-gradient(135deg, #f8fafc, #f1f5f9); }
    .cp-audit-section-title { display: flex; align-items: center; gap: 0.5rem; }
    .cp-audit-section-title svg { color: #475569; flex-shrink: 0; }
    .cp-audit-section-title strong { font-size: 0.82rem; font-weight: 700; color: #1e293b; }
    .cp-audit-section-title small { font-size: 0.68rem; color: #94a3b8; font-weight: 500; }
    .cp-audit-full-table { width: 100%; border-collapse: collapse; font-size: 0.78rem; }
    .cp-audit-full-table thead tr { background: #f9fafb; border-bottom: 2px solid #e2e8f0; }
    .cp-audit-full-table th { text-align: left; padding: 0.6rem 1rem; font-size: 0.65rem; font-weight: 700; letter-spacing: 0.07em; text-transform: uppercase; color: #94a3b8; white-space: nowrap; }
    .cp-audit-full-table td { padding: 0.75rem 1rem; border-bottom: 1px solid #f8fafc; color: #374151; vertical-align: middle; }
    .cp-audit-full-table tbody tr:hover td { background: #fafbfc; }
    .cp-audit-full-table tbody tr:last-child td { border-bottom: none; }
    .cp-audit-section-footer { padding: 0.7rem 1.25rem; background: #f8fafc; border-top: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; }
    .cp-audit-immutable { display: inline-flex; align-items: center; gap: 4px; font-size: 0.67rem; font-weight: 600; color: #94a3b8; }
    .cp-audit-empty { text-align: center; padding: 3rem 1rem; color: #9ca3af; font-size: 0.8rem; }
    .cp-audit-empty svg { margin: 0 auto 0.6rem; display: block; opacity: 0.3; }
    .cp-audit-empty strong { display: block; color: #6b7280; font-size: 0.85rem; margin-bottom: 3px; }
</style>

{{-- ── Audit Trail (footer — renders below the Filament table) ── --}}
<div class="cp-audit-section">
    <div class="cp-audit-section-header">
        <div class="cp-audit-section-title">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <div>
                <div style="font-size:0.82rem;font-weight:800;color:#1e293b;letter-spacing:-0.01em;">Audit Trail</div>
                <div style="font-size:0.68rem;color:#94a3b8;font-weight:500;margin-top:1px;">Full Activity Log · Immutable · Compliance-ready</div>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:0.5rem;">
            <span class="cp-tag" style="background:#f1f5f9;color:#64748b;">
                {{ ($auditLogs ?? collect())->count() }} {{ ($auditLogs ?? collect())->count() === 1 ? 'entry' : 'entries' }}
            </span>
            <button class="cp-btn cp-btn-gray" onclick="cpToast('Audit log exported.','success')" style="font-size:0.7rem;padding:5px 12px;">
                <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export CSV
            </button>
        </div>
    </div>

    @if(isset($auditLogs) && $auditLogs->isNotEmpty())
        <div style="overflow-x:auto;">
            <table class="cp-audit-full-table">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>OR / Loan #</th>
                        <th>Member</th>
                        <th>Amount</th>
                        <th>Posted By</th>
                        <th>Date &amp; Time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($auditLogs as $log)
                        @php
                            $badgeClass = match(strtolower($log['action'] ?? '')) {
                                'posted' => 'cp-badge-success',
                                'voided' => 'cp-badge-danger',
                                'edited' => 'cp-badge-warning',
                                default  => 'cp-badge-gray',
                            };
                        @endphp
                        <tr>
                            <td><span class="cp-badge {{ $badgeClass }}">{{ $log['action'] }}</span></td>
                            <td style="font-family:monospace;font-size:0.72rem;color:#1e3a5f;font-weight:700;">{{ $log['reference'] }}</td>
                            <td style="font-weight:500;">{{ $log['member'] }}</td>
                            <td style="font-weight:700;color:#059669;">₱{{ number_format($log['amount'], 2) }}</td>
                            <td style="color:#374151;">{{ $log['user'] }}</td>
                            <td>
                                <div style="font-size:0.75rem;color:#374151;">{{ \Carbon\Carbon::parse($log['timestamp'])->format('M j, Y g:i A') }}</div>
                                <div style="font-size:0.65rem;color:#94a3b8;margin-top:1px;">{{ \Carbon\Carbon::parse($log['timestamp'])->diffForHumans() }}</div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="cp-audit-empty">
            <svg width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <strong>No audit entries yet</strong>
            Entries appear here automatically after payments are posted.
        </div>
    @endif

    <div class="cp-audit-section-footer">
        <div class="cp-audit-immutable">
            <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            Records are immutable and cannot be edited or deleted
        </div>
        <span style="font-size:0.67rem;color:#cbd5e1;">Showing last {{ ($auditLogs ?? collect())->count() }} entries</span>
    </div>
</div>