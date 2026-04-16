<div>
<style>
  /* ================= LIGHT MODE (DEFAULT) ================= */

  .cp-badge { display: inline-flex; align-items: center; padding: 2px 10px; border-radius: 999px; font-size: 0.65rem; font-weight: 700; }
  .cp-badge-success { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
  .cp-badge-danger  { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
  .cp-badge-warning { background-color: #ffedd5; color: #9a3412; border: 1px solid #fed7aa; }
  .cp-badge-gray    { background-color: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb; }

  .cp-btn {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 0.72rem; font-weight: 600; padding: 5px 13px;
    border-radius: 7px; border: 1px solid #d1d5db;
    background: #ffffff; color: #374151;
    cursor: pointer; transition: all 0.15s;
  }
  .cp-btn:hover { background: #f3f4f6; }

  .cp-tag {
    font-size: 0.65rem; font-weight: 600; padding: 3px 9px;
    border-radius: 999px; background: #f3f4f6; color: #4b5563;
    border: 1px solid #e5e7eb;
  }

  .cp-audit-section {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    overflow: hidden;
  }

  .cp-audit-header,
  .cp-audit-footer {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0.9rem 1.2rem;
    background: #ffffff;
    border-color: #e5e7eb;
  }

  .cp-audit-header { border-bottom: 1px solid #e5e7eb; }
  .cp-audit-footer { border-top: 1px solid #e5e7eb; }

  .cp-audit-title { font-size: 0.82rem; font-weight: 700; color: #111827; }
  .cp-audit-subtitle { font-size: 0.68rem; color: #6b7280; }

  .cp-audit-table { width: 100%; border-collapse: collapse; font-size: 0.78rem; }

  .cp-audit-table thead tr {
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
  }

  .cp-audit-table th {
    padding: 0.6rem 1rem;
    font-size: 0.62rem; font-weight: 700;
    letter-spacing: 0.07em; text-transform: uppercase;
    color: #6b7280;
  }

  .cp-audit-table td {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f3f4f6;
    color: #111827;
  }

  .cp-audit-table tbody tr:nth-child(odd) td { background: #ffffff; }
  .cp-audit-table tbody tr:nth-child(even) td { background: #f9fafb; }

  .cp-audit-table tbody tr:hover td {
    background: #f3f4f6 !important;
  }

  .cp-ref, .cp-amount { color: #111827; font-weight: 600; }
  .cp-member { color: #111827; }
  .cp-posted-by { color: #6b7280; }
  .cp-date-primary { color: #111827; }
  .cp-date-ago { color: #9ca3af; }

  .cp-empty {
    text-align: center; padding: 3rem 1rem;
    color: #6b7280; background: #ffffff;
  }

  /* ================= DARK MODE ================= */

  .dark .cp-badge-success { background-color: #052e16; color: #4ade80; border: 1px solid #166534; }
  .dark .cp-badge-danger  { background-color: #450a0a; color: #f87171; border: 1px solid #991b1b; }
  .dark .cp-badge-warning { background-color: #422006; color: #fb923c; border: 1px solid #9a3412; }
  .dark .cp-badge-gray    { background-color: #1f2937; color: #9ca3af; border: 1px solid #374151; }

  .dark .cp-btn {
    background: #1f2937;
    color: #d1d5db;
    border-color: #374151;
  }
  .dark .cp-btn:hover { background: #374151; }

  .dark .cp-tag {
    background: #1f2937;
    color: #9ca3af;
    border-color: #374151;
  }

  .dark .cp-audit-section {
    background: #111318;
    border-color: #1f2937;
  }

  .dark .cp-audit-header,
  .dark .cp-audit-footer {
    background: #111318;
    border-color: #1f2937;
  }

  /* ✅ FIXED TITLE VISIBILITY */
  .dark .cp-audit-title {
    color: #f9fafb;
  }

  .dark .cp-audit-subtitle {
    color: #9ca3af;
  }

  .dark .cp-audit-table thead tr {
    background: #111318;
    border-color: #1f2937;
  }

  .dark .cp-audit-table td {
    color: #e5e7eb;
    border-color: #1a1c23;
  }

  .dark .cp-audit-table tbody tr:nth-child(odd) td { background: #141519; }
  .dark .cp-audit-table tbody tr:nth-child(even) td { background: #111318; }

  .dark .cp-audit-table tbody tr:hover td {
    background: #1c1e26 !important;
  }

  .dark .cp-ref,
  .dark .cp-amount { color: #f9fafb; }

  .dark .cp-member { color: #e5e7eb; }
  .dark .cp-posted-by { color: #9ca3af; }
  .dark .cp-date-primary { color: #e5e7eb; }
  .dark .cp-date-ago { color: #6b7280; }

  .dark .cp-empty {
    background: #111318;
  }
</style>

<div class="cp-audit-section">

  <!-- HEADER -->
  <div class="cp-audit-header">
    <div>
      <div class="cp-audit-title">Audit Trail</div>
      <div class="cp-audit-subtitle">Full activity log · Immutable · Compliance-ready</div>
    </div>

    <div style="display:flex;gap:8px;">
      <span class="cp-tag">
        {{ ($auditLogs ?? collect())->count() }} entries
      </span>

      <button class="cp-btn">
        Export CSV
      </button>
    </div>
  </div>

  <!-- TABLE -->
  @if(isset($auditLogs) && $auditLogs->isNotEmpty())
    <div style="overflow-x:auto;">
      <table class="cp-audit-table">
        <thead>
          <tr>
            <th>Action</th>
            <th>Reference</th>
            <th>Member</th>
            <th>Amount</th>
            <th>Posted by</th>
            <th>Date</th>
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
            <td class="cp-ref">{{ $log['reference'] }}</td>
            <td class="cp-member">{{ $log['member'] }}</td>
            <td class="cp-amount">₱{{ number_format($log['amount'], 2) }}</td>
            <td class="cp-posted-by">{{ $log['user'] }}</td>
            <td>
              <div class="cp-date-primary">
                {{ \Carbon\Carbon::parse($log['timestamp'])->format('M j, Y g:i A') }}
              </div>
              <div class="cp-date-ago">
                {{ \Carbon\Carbon::parse($log['timestamp'])->diffForHumans() }}
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @else
    <div class="cp-empty">
      <strong>No audit entries yet</strong>
      Entries appear after transactions.
    </div>
  @endif

  <!-- FOOTER -->
  <div class="cp-audit-footer">
    <span class="cp-showing">
      Showing {{ ($auditLogs ?? collect())->count() }} entries
    </span>
  </div>

</div>
</div>