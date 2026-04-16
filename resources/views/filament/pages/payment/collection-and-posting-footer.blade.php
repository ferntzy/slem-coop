<div>
<style>
  .cp-badge { display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 999px; font-size: 0.65rem; font-weight: 700; }
  .cp-badge-success { background-color: rgb(236 253 245 / 1); color: rgb(5 150 105 / 1); }
  .cp-badge-danger  { background-color: rgb(254 242 242 / 1); color: rgb(220 38 38 / 1); }
  .cp-badge-warning { background-color: rgb(255 251 235 / 1); color: rgb(217 119 6 / 1); }
  .cp-badge-gray    { background-color: rgb(243 244 246 / 1); color: rgb(55 65 81 / 1); }

  .dark .cp-badge-success { background-color: rgb(6 78 59 / 0.4);  color: rgb(52 211 153 / 1); }
  .dark .cp-badge-danger  { background-color: rgb(127 29 29 / 0.4); color: rgb(252 165 165 / 1); }
  .dark .cp-badge-warning { background-color: rgb(120 53 15 / 0.4); color: rgb(253 186 116 / 1); }
  .dark .cp-badge-gray    { background-color: rgb(55 65 81 / 0.4);  color: rgb(209 213 219 / 1); }

  .cp-btn { display: inline-flex; align-items: center; gap: 5px; font-size: 0.72rem; font-weight: 600; padding: 5px 13px; border-radius: 7px; border: 1px solid rgb(226 232 240 / 1); background: rgb(248 250 252 / 1); color: rgb(51 65 85 / 1); cursor: pointer; font-family: inherit; transition: all 0.15s; }
  .dark .cp-btn { border-color: rgb(51 65 85 / 1); background: rgb(30 41 59 / 1); color: rgb(203 213 225 / 1); }
  .cp-btn:hover { filter: brightness(0.96); }

  .cp-tag { font-size: 0.65rem; font-weight: 600; padding: 3px 9px; border-radius: 999px; background: rgb(241 245 249 / 1); color: rgb(100 116 139 / 1); }
  .dark .cp-tag { background: rgb(30 41 59 / 1); color: rgb(148 163 184 / 1); }

  .cp-audit-section { background: #ffffff; border: 1px solid rgb(226 232 240 / 1); border-radius: 14px; overflow: hidden; }
  .dark .cp-audit-section { background: #0f172a; border-color: rgb(30 41 59 / 1); }

  .cp-audit-header { display: flex; align-items: center; justify-content: space-between; padding: 0.9rem 1.2rem; border-bottom: 1px solid rgb(241 245 249 / 1); background: rgb(248 250 252 / 1); }
  .dark .cp-audit-header { background: rgb(15 23 42 / 1); border-bottom-color: rgb(30 41 59 / 1); }

  .cp-audit-title { font-size: 0.82rem; font-weight: 700; color: #1e293b; }
  .dark .cp-audit-title { color: #f1f5f9; }
  .cp-audit-subtitle { font-size: 0.68rem; color: #94a3b8; margin-top: 1px; }

  .cp-audit-table { width: 100%; border-collapse: collapse; font-size: 0.78rem; }
  .cp-audit-table thead tr { background: rgb(249 250 251 / 1); border-bottom: 1px solid rgb(226 232 240 / 1); }
  .dark .cp-audit-table thead tr { background: rgb(15 23 42 / 1); border-bottom-color: rgb(30 41 59 / 1); }
  .cp-audit-table th { padding: 0.55rem 1rem; font-size: 0.62rem; font-weight: 700; letter-spacing: 0.07em; text-transform: uppercase; color: #94a3b8; text-align: left; white-space: nowrap; }
  .cp-audit-table td { padding: 0.7rem 1rem; border-bottom: 1px solid rgb(248 250 252 / 1); color: #374151; vertical-align: middle; }
  .dark .cp-audit-table td { border-bottom-color: rgb(30 41 59 / 1); color: #cbd5e1; }
  .cp-audit-table tbody tr:last-child td { border-bottom: none; }
  .cp-audit-table tbody tr:hover td { background: rgb(248 250 252 / 1); }
  .dark .cp-audit-table tbody tr:hover td { background: rgb(15 23 42 / 0.6); }

  .cp-audit-footer { padding: 0.65rem 1.2rem; background: rgb(248 250 252 / 1); border-top: 1px solid rgb(241 245 249 / 1); display: flex; align-items: center; justify-content: space-between; }
  .dark .cp-audit-footer { background: rgb(15 23 42 / 1); border-top-color: rgb(30 41 59 / 1); }
  .cp-immutable { display: inline-flex; align-items: center; gap: 5px; font-size: 0.67rem; font-weight: 600; color: #94a3b8; }
  .cp-showing { font-size: 0.67rem; color: #94a3b8; }
</style>

<div class="cp-audit-section">
  <div class="cp-audit-header">
    <div style="display:flex;align-items:center;gap:10px;">
      <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:#475569;flex-shrink:0;">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
      </svg>
      <div>
        <div class="cp-audit-title">Audit Trail</div>
        <div class="cp-audit-subtitle">Full activity log · Immutable · Compliance-ready</div>
      </div>
    </div>
    <div style="display:flex;align-items:center;gap:8px;">
      <span class="cp-tag">
        {{ ($auditLogs ?? collect())->count() }} {{ ($auditLogs ?? collect())->count() === 1 ? 'entry' : 'entries' }}
      </span>
      <button class="cp-btn" onclick="cpToast('Audit log exported.','success')">
        <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Export CSV
      </button>
    </div>
  </div>

  @if(isset($auditLogs) && $auditLogs->isNotEmpty())
    <div style="overflow-x:auto;">
      <table class="cp-audit-table">
        <thead>
          <tr>
            <th>Action</th>
            <th>OR / Loan #</th>
            <th>Member</th>
            <th>Amount</th>
            <th>Posted by</th>
            <th>Date &amp; time</th>
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
              <td style="font-family:monospace;font-size:0.72rem;font-weight:700;color:#1e3a5f;" class="dark:!text-sky-400">
                {{ $log['reference'] }}
              </td>
              <td style="font-weight:500;">{{ $log['member'] }}</td>
              <td style="font-weight:700;color:#059669;" class="dark:!text-emerald-400">
                ₱{{ number_format($log['amount'], 2) }}
              </td>
              <td>{{ $log['user'] }}</td>
              <td>
                <div style="font-size:0.75rem;">{{ \Carbon\Carbon::parse($log['timestamp'])->format('M j, Y g:i A') }}</div>
                <div style="font-size:0.65rem;color:#94a3b8;margin-top:1px;">{{ \Carbon\Carbon::parse($log['timestamp'])->diffForHumans() }}</div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @else
    <div style="text-align:center;padding:3rem 1rem;color:#9ca3af;font-size:0.8rem;">
      <svg width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="margin:0 auto 0.6rem;display:block;opacity:0.3;">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
      </svg>
      <strong style="display:block;color:#6b7280;font-size:0.85rem;margin-bottom:3px;">No audit entries yet</strong>
      Entries appear here automatically after payments are posted.
    </div>
  @endif

  <div class="cp-audit-footer">
    <div class="cp-immutable">
      <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
      </svg>
      Records are immutable and cannot be edited or deleted
    </div>
    <span class="cp-showing">Showing last {{ ($auditLogs ?? collect())->count() }} entries</span>
  </div>
</div>