<x-filament-panels::page>
<div>
<style>
    .pal-wrap * { box-sizing: border-box; }

    /* ── Hero ── */
    .pal-hero { background: linear-gradient(135deg, #1e3a5f 0%, #0f2744 60%, #0a1628 100%); border-radius: 1rem; padding: 2rem 2.5rem; position: relative; overflow: hidden; margin-bottom: 1.5rem; }
    .pal-hero::before { content:''; position:absolute; top:-60px; right:-60px; width:220px; height:220px; border-radius:50%; background:rgba(16,185,129,.08); }
    .pal-hero::after  { content:''; position:absolute; bottom:-40px; left:30%; width:140px; height:140px; border-radius:50%; background:rgba(16,185,129,.05); }
    .pal-hero-badge { display:inline-flex; align-items:center; gap:6px; background:rgba(245,158,11,.15); border:1px solid rgba(245,158,11,.35); color:#fbbf24; font-size:.68rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; padding:4px 12px; border-radius:999px; margin-bottom:.75rem; }
    .pal-hero-title { font-size:1.75rem; font-weight:800; color:#fff; margin:0 0 .4rem; letter-spacing:-.02em; }
    .pal-hero-sub   { color:rgba(255,255,255,.55); font-size:.875rem; margin:0; }
    .pal-stats      { display:flex; gap:1.5rem; margin-top:1.5rem; flex-wrap:wrap; }
    .pal-stat       { background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.1); border-radius:.75rem; padding:.75rem 1.25rem; min-width:130px; }
    .pal-stat-value { font-size:1.4rem; font-weight:800; color:#fbbf24; line-height:1; }
    .pal-stat-label { font-size:.7rem; color:rgba(255,255,255,.45); text-transform:uppercase; letter-spacing:.08em; margin-top:3px; }

    /* ── Priority bar ── */
    .pal-priority-wrap { background:#fff; border:1px solid #f0f0f0; border-radius:1rem; padding:1.25rem 1.5rem; margin-bottom:1.5rem; box-shadow:0 1px 4px rgba(0,0,0,.04); }
    .dark .pal-priority-wrap { background:#1f2937; border-color:rgba(255,255,255,.07); }
    .pal-priority-label { font-size:.68rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:#6b7280; margin-bottom:1rem; }
    .pal-priority-label span { color:#3b82f6; cursor:pointer; margin-left:.5rem; }
    .pal-priority-steps { display:flex; align-items:center; gap:.75rem; flex-wrap:wrap; }
    .pal-step { display:flex; align-items:center; gap:.6rem; background:#f9fafb; border:1.5px solid #e5e7eb; border-radius:.75rem; padding:.6rem 1rem; user-select:none; }
    .dark .pal-step { background:#111827; border-color:rgba(255,255,255,.08); }
    .pal-step-num { width:26px; height:26px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.75rem; font-weight:800; flex-shrink:0; }
    .pal-step-num-1 { background:#1e3a5f; color:#fff; } .pal-step-num-2 { background:#6366f1; color:#fff; } .pal-step-num-3 { background:#8b5cf6; color:#fff; }
    .pal-step-name { font-size:.82rem; font-weight:700; color:#111827; } .dark .pal-step-name { color:#f9fafb; }
    .pal-step-sub  { font-size:.68rem; color:#9ca3af; margin-top:1px; }
    .pal-arrow     { color:#d1d5db; font-size:.9rem; flex-shrink:0; }
    .pal-override-note { margin-top:.85rem; display:inline-flex; align-items:center; gap:.4rem; background:#fffbeb; border:1px solid #fde68a; border-radius:.5rem; padding:.4rem .85rem; font-size:.73rem; color:#92400e; font-weight:600; }

    /* ── Feature cards ── */
    .pal-features-label { font-size:.68rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:#6b7280; margin-bottom:.85rem; }
    .pal-cards { display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; margin-bottom:1.5rem; }
    @media(max-width:900px) { .pal-cards { grid-template-columns:1fr; } }
    .pal-card { background:#fff; border:1px solid #f0f0f0; border-radius:1rem; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.04); }
    .dark .pal-card { background:#1f2937; border-color:rgba(255,255,255,.07); }
    .pal-card-top { height:4px; }
    .pal-card-top-yellow { background:linear-gradient(90deg,#f59e0b,#fbbf24); }
    .pal-card-top-red    { background:linear-gradient(90deg,#ef4444,#f87171); }
    .pal-card-top-green  { background:linear-gradient(90deg,#10b981,#34d399); }
    .pal-card-body { padding:1.25rem; }
    .pal-card-icon { font-size:1.6rem; margin-bottom:.75rem; }
    .pal-card-title { font-size:.9rem; font-weight:800; color:#111827; margin-bottom:.5rem; } .dark .pal-card-title { color:#f9fafb; }
    .pal-card-badges { display:flex; gap:.35rem; flex-wrap:wrap; margin-bottom:.75rem; }
    .pal-card-badge { font-size:.65rem; font-weight:700; padding:2px 8px; border-radius:999px; }
    .pal-card-badge-partial  { background:#1d4ed8; color:#fff; }
    .pal-card-badge-advance  { background:#047857; color:#fff; }
    .pal-card-badge-overpayment { background:#b45309; color:#fff; }
    .pal-card-badge-void  { background:#dc2626; color:#fff; }
    .pal-card-badge-edit     { background:#374151; color:#fff; }
    .pal-card-badge-audit { background:#6d28d9; color:#fff; }
    .pal-card-badge-auto     { background:#059669; color:#fff; }
    .pal-card-badge-override { background:#2563eb; color:#fff; }
    .pal-card-desc { font-size:.78rem; color:#6b7280; line-height:1.6; margin-bottom:1rem; }
    .pal-card-footer { display:flex; align-items:center; justify-content:space-between; }
    .pal-card-note { font-size:.7rem; color:#9ca3af; font-style:italic; }
    .pal-card-note-yellow { color:#d97706; } .pal-card-note-red { color:#dc2626; } .pal-card-note-green { color:#059669; }

    /* ── Buttons — solid, no pastels ── */
    .pal-btn { display:inline-flex; align-items:center; gap:5px; font-size:.78rem; font-weight:600; padding:7px 16px; border-radius:8px; border:none; cursor:pointer; transition:all .15s; font-family:inherit; }
    .pal-btn:hover { filter:brightness(1.1); transform:scale(1.02); }
    .pal-btn-yellow  { background:#d97706; color:#fff; }
    .pal-btn-red     { background:#dc2626; color:#fff; }
    .pal-btn-green   { background:#059669; color:#fff; }
    .pal-btn-primary { background:#1e3a5f; color:#fff; }
    .pal-btn-ghost   { background:#374151; color:#fff; }
    .pal-btn-danger  { background:#dc2626; color:#fff; }
    .pal-btn:disabled { opacity:.5; cursor:not-allowed; transform:none; filter:none; }

    /* ── Searchable Dropdown ── */
    .pal-search-wrap { position:relative; }
    .pal-search-input { width:100%; padding:.55rem .8rem .55rem 2.2rem; border:1.5px solid #e5e7eb; border-radius:.5rem; font-size:.82rem; color:#111827; background:#fff; transition:border-color .15s; font-family:inherit; }
    .dark .pal-search-input { background:#111827; border-color:rgba(255,255,255,.1); color:#f9fafb; }
    .pal-search-input:focus { outline:none; border-color:#1e3a5f; box-shadow:0 0 0 3px rgba(30,58,95,.08); }
    .pal-search-icon  { position:absolute; left:.65rem; top:50%; transform:translateY(-50%); color:#9ca3af; pointer-events:none; }
    .pal-search-clear { position:absolute; right:.65rem; top:50%; transform:translateY(-50%); color:#9ca3af; cursor:pointer; font-size:.9rem; display:none; }
    .pal-dropdown { position:absolute; top:100%; left:0; right:0; background:#fff; border:1.5px solid #1e3a5f; border-top:none; border-radius:0 0 .5rem .5rem; z-index:1000; max-height:240px; overflow-y:auto; box-shadow:0 8px 24px rgba(0,0,0,.1); display:none; }
    .dark .pal-dropdown { background:#111827; border-color:rgba(255,255,255,.15); }
    .pal-dropdown.pal-open { display:block; }
    .pal-dropdown-item { padding:.65rem .85rem; cursor:pointer; border-bottom:1px solid #f3f4f6; transition:background .1s; }
    .dark .pal-dropdown-item { border-color:rgba(255,255,255,.05); }
    .pal-dropdown-item:last-child { border-bottom:none; }
    .pal-dropdown-item:hover { background:#dbeafe; }
    .pal-dropdown-item-main { font-size:.82rem; font-weight:700; color:#111827; } .dark .pal-dropdown-item-main { color:#f9fafb; }
    .pal-dropdown-item-sub  { font-size:.7rem; color:#6b7280; margin-top:1px; display:flex; gap:.5rem; flex-wrap:wrap; }
    .pal-dropdown-item-badge { font-size:.62rem; font-weight:700; padding:1px 6px; border-radius:999px; background:#059669; color:#fff; }
    .pal-dropdown-empty { padding:1rem; text-align:center; color:#9ca3af; font-size:.8rem; }
    .pal-selected-chip { display:none; align-items:center; gap:.5rem; padding:.5rem .85rem; background:#1e3a5f; border-radius:.5rem; font-size:.8rem; font-weight:600; color:#fff; margin-top:.4rem; }
    .pal-selected-chip.pal-visible { display:flex; }
    .pal-selected-chip-clear { cursor:pointer; color:rgba(255,255,255,.6); font-size:1rem; margin-left:auto; }
    .pal-selected-chip-clear:hover { color:#fff; }

    /* ── Simulator ── */
    .pal-simulator { background:#fff; border:1px solid #f0f0f0; border-radius:1rem; box-shadow:0 1px 4px rgba(0,0,0,.04); margin-bottom:1.5rem; overflow:hidden; }
    .dark .pal-simulator { background:#1f2937; border-color:rgba(255,255,255,.07); }
    .pal-sim-header { padding:1rem 1.5rem; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; justify-content:space-between; }
    .dark .pal-sim-header { border-color:rgba(255,255,255,.06); }
    .pal-sim-title { font-size:.9rem; font-weight:800; color:#111827; } .dark .pal-sim-title { color:#f9fafb; }
    .pal-sim-sub { font-size:.72rem; color:#9ca3af; margin-top:1px; }
    .pal-sim-badge { display:inline-flex; align-items:center; gap:4px; background:#374151; color:#fff; font-size:.65rem; font-weight:700; padding:3px 10px; border-radius:999px; letter-spacing:.05em; }
    .pal-sim-body { padding:1.5rem; }
    .pal-sim-grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.25rem; }
    @media(max-width:640px) { .pal-sim-grid { grid-template-columns:1fr; } }
    .pal-sim-field-full { grid-column:1/-1; }
    .pal-sim-label { display:block; font-size:.73rem; font-weight:700; color:#374151; margin-bottom:.35rem; } .dark .pal-sim-label { color:#d1d5db; }
    .pal-sim-input { width:100%; padding:.55rem .8rem; border:1.5px solid #e5e7eb; border-radius:.5rem; font-size:.82rem; color:#111827; background:#fff; transition:border-color .15s; font-family:inherit; }
    .dark .pal-sim-input { background:#111827; border-color:rgba(255,255,255,.1); color:#f9fafb; }
    .pal-sim-input:focus { outline:none; border-color:#1e3a5f; box-shadow:0 0 0 3px rgba(30,58,95,.08); }
    .pal-sim-input:disabled { background:#f9fafb; color:#9ca3af; cursor:not-allowed; }
    .dark .pal-sim-input:disabled { background:#374151; }
    .pal-sim-preview-note { display:flex; align-items:center; gap:.5rem; background:#1e3a5f; color:#fff; font-size:.72rem; font-weight:600; padding:.55rem 1rem; border-radius:.5rem; margin-bottom:1rem; }

    /* ── Result / Flow ── */
    .pal-result { background:#f9fafb; border:1px solid #e5e7eb; border-radius:.85rem; overflow:hidden; margin-top:1rem; }
    .dark .pal-result { background:#111827; border-color:rgba(255,255,255,.08); }
    .pal-result-header { padding:.5rem 1rem; background:#f3f4f6; border-bottom:1px solid #e5e7eb; display:flex; align-items:center; justify-content:space-between; }
    .dark .pal-result-header { background:#1f2937; border-color:rgba(255,255,255,.06); }
    .pal-result-title { font-size:.68rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:#6b7280; }
    .pal-result-grid { display:grid; grid-template-columns:repeat(3,1fr); }
    @media(max-width:640px) { .pal-result-grid { grid-template-columns:repeat(2,1fr); } }
    .pal-result-item { padding:.85rem 1rem; border-right:1px solid #e5e7eb; border-bottom:1px solid #e5e7eb; }
    .dark .pal-result-item { border-color:rgba(255,255,255,.06); }
    .pal-result-item:nth-child(3n) { border-right:none; } .pal-result-item:nth-last-child(-n+3) { border-bottom:none; }
    .pal-result-item-label { font-size:.65rem; color:#9ca3af; text-transform:uppercase; letter-spacing:.06em; }
    .pal-result-item-value { font-size:.92rem; font-weight:800; color:#111827; margin-top:2px; } .dark .pal-result-item-value { color:#f9fafb; }
    .pal-result-item-value.green { color:#059669; } .pal-result-item-value.amber { color:#d97706; } .pal-result-item-value.red { color:#dc2626; }
    .pal-flow { display:flex; align-items:stretch; gap:.5rem; margin-top:1rem; flex-wrap:wrap; }
    .pal-flow-step { flex:1; min-width:100px; background:#fff; border:1.5px solid #e5e7eb; border-radius:.75rem; padding:.75rem; transition:all .3s; }
    .dark .pal-flow-step { background:#1f2937; border-color:rgba(255,255,255,.08); }
    .pal-flow-step.pal-flow-active  { border-color:#10b981; background:#059669; }
    .pal-flow-step.pal-flow-active .pal-flow-step-num { background:#fff; color:#059669; }
    .pal-flow-step.pal-flow-active .pal-flow-step-name { color:#fff; }
    .pal-flow-step.pal-flow-active .pal-flow-step-value { color:#fff; }
    .pal-flow-step.pal-flow-partial { border-color:#d97706; background:#d97706; }
    .pal-flow-step.pal-flow-partial .pal-flow-step-num { background:#fff; color:#d97706; }
    .pal-flow-step.pal-flow-partial .pal-flow-step-name { color:#fff; }
    .pal-flow-step.pal-flow-partial .pal-flow-step-value { color:#fff; }
    .pal-flow-step-num { width:20px; height:20px; border-radius:50%; font-size:.65rem; font-weight:800; display:flex; align-items:center; justify-content:center; margin-bottom:.4rem; background:#e5e7eb; color:#6b7280; }
    .pal-flow-step-name  { font-size:.72rem; font-weight:700; color:#374151; } .dark .pal-flow-step-name { color:#d1d5db; }
    .pal-flow-step-value { font-size:.85rem; font-weight:800; color:#111827; margin-top:2px; } .dark .pal-flow-step-value { color:#f9fafb; }
    .pal-flow-arrow { display:flex; align-items:center; color:#d1d5db; font-size:1rem; flex-shrink:0; align-self:center; }
    .pal-carry-note { margin-top:.75rem; display:flex; align-items:flex-start; gap:.6rem; background:#1e3a5f; border-radius:.65rem; padding:.65rem .9rem; font-size:.75rem; color:#fff; font-weight:600; line-height:1.5; }
    .pal-carry-note svg { flex-shrink:0; margin-top:1px; color:#fbbf24; }
    .pal-collateral-warn { display:none; align-items:flex-start; gap:.75rem; background:#dc2626; border-radius:.75rem; padding:.9rem 1rem; margin-bottom:1rem; }
    .pal-collateral-warn-text { font-size:.8rem; color:#fff; font-weight:600; line-height:1.5; }

    /* ── Collections & Posting Table ── */
    .pal-posted-wrap { background:#fff; border:1px solid #f0f0f0; border-radius:1rem; box-shadow:0 1px 4px rgba(0,0,0,.04); overflow:hidden; margin-bottom:1.5rem; }
    .dark .pal-posted-wrap { background:#1f2937; border-color:rgba(255,255,255,.07); }
    .pal-posted-header { padding:1rem 1.5rem; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; }
    .dark .pal-posted-header { border-color:rgba(255,255,255,.06); }
    .pal-posted-title { font-size:.9rem; font-weight:800; color:#111827; } .dark .pal-posted-title { color:#f9fafb; }
    .pal-posted-sub   { font-size:.72rem; color:#9ca3af; margin-top:1px; }
    .pal-posted-count { font-size:.72rem; font-weight:700; background:#1e3a5f; color:#fff; padding:3px 10px; border-radius:999px; }
    .pal-table-scroll { overflow-x:auto; }
    .pal-table { width:100%; border-collapse:collapse; font-size:.78rem; min-width:680px; }
    .pal-table th { text-align:left; padding:.6rem .85rem; background:#1e3a5f; color:#fff; font-size:.64rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase; border-bottom:1px solid #f0f0f0; white-space:nowrap; }
    .dark .pal-table th { background:#0f172a; }
    .pal-table td { padding:.7rem .85rem; border-bottom:1px solid #f9fafb; color:#374151; white-space:nowrap; vertical-align:middle; }
    .dark .pal-table td { color:#d1d5db; border-color:rgba(255,255,255,.04); }
    .pal-table tr:last-child td { border-bottom:none; }
    .pal-table tbody tr:hover td { background:#f0f9ff; } .dark .pal-table tbody tr:hover td { background:rgba(255,255,255,.02); }
    .pal-table-empty { text-align:center; padding:2.5rem 1rem; color:#9ca3af; }
    .pal-table-empty-icon { font-size:2rem; margin-bottom:.4rem; }
    .pal-table-empty-text { font-size:.8rem; }
    .pal-type-badge { display:inline-flex; padding:2px 8px; border-radius:999px; font-size:.65rem; font-weight:700; }
    .pal-type-partial     { background:#1d4ed8; color:#fff; }
    .pal-type-advance     { background:#047857; color:#fff; }
    .pal-type-overpayment { background:#b45309; color:#fff; }
    .pal-status-posted    { background:#059669; color:#fff; }
    .pal-status-void      { background:#dc2626; color:#fff; }
    @keyframes palRowFlash { from { background:#d1fae5; } to { background:transparent; } }
    .pal-row-new td { animation: palRowFlash 2.5s ease forwards; }

    /* ── Sort toolbar ── */
    .pal-sort-bar { display:flex; align-items:center; gap:.4rem; padding:.55rem 1rem; border-bottom:1px solid #f0f0f0; background:#f8fafc; flex-wrap:wrap; }
    .dark .pal-sort-bar { background:#111827; border-color:rgba(255,255,255,.05); }
    .pal-sort-bar-label { font-size:.62rem; font-weight:700; letter-spacing:.09em; text-transform:uppercase; color:#9ca3af; margin-right:.2rem; white-space:nowrap; flex-shrink:0; }
    .pal-sort-pill { display:inline-flex; align-items:center; gap:.3rem; padding:.28rem .7rem; border-radius:999px; border:1.5px solid #e5e7eb; background:#fff; font-size:.71rem; font-weight:600; color:#6b7280; cursor:pointer; transition:all .15s; user-select:none; white-space:nowrap; }
    .dark .pal-sort-pill { background:#1f2937; border-color:rgba(255,255,255,.1); color:#9ca3af; }
    .pal-sort-pill:hover { border-color:#1e3a5f; color:#fff; background:#1e3a5f; }
    .pal-sort-pill.sp-asc  { border-color:#1e3a5f; background:#1e3a5f; color:#fff; }
    .pal-sort-pill.sp-desc { border-color:#3b82f6; background:#3b82f6; color:#fff; }
    .pal-sort-pill .sp-arrow { font-size:.68rem; opacity:.6; }
    .pal-sort-pill.sp-asc .sp-arrow, .pal-sort-pill.sp-desc .sp-arrow { opacity:1; }
    .pal-sort-reset { display:none; align-items:center; gap:.2rem; padding:.26rem .6rem; border-radius:999px; border:1.5px solid #dc2626; background:#dc2626; color:#fff; font-size:.67rem; font-weight:600; cursor:pointer; margin-left:.15rem; }
    .pal-sort-reset:hover { background:#b91c1c; }
    .pal-sort-reset.sr-show { display:inline-flex; }
    .pal-sort-divider { width:1px; height:14px; background:#e5e7eb; margin:0 .15rem; flex-shrink:0; }

    /* ── Action Buttons — solid ── */
    .pal-action-btn { display:inline-flex; align-items:center; gap:4px; font-size:.72rem; font-weight:600; padding:5px 11px; border-radius:6px; border:none; cursor:pointer; transition:all .15s; font-family:inherit; white-space:nowrap; color:#fff; }
    .pal-action-btn:hover { filter:brightness(1.1); transform:translateY(-1px); }
    .pal-action-view  { background:#0369a1; }
    .pal-action-audit { background:#6d28d9; }
    .pal-action-edit  { background:#d97706; }
    .pal-action-void  { background:#dc2626; }

    /* ── Toast ── */
    .pal-toast { position:fixed; bottom:1.5rem; right:1.5rem; background:#111827; color:#fff; padding:.7rem 1.2rem; border-radius:.75rem; font-size:.8rem; font-weight:600; display:flex; align-items:center; gap:.5rem; z-index:99999; transform:translateY(80px); opacity:0; transition:all .3s ease; box-shadow:0 8px 24px rgba(0,0,0,.2); pointer-events:none; }
    .pal-toast.pal-toast-show { transform:translateY(0); opacity:1; }
    .pal-toast-dot { width:18px; height:18px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.65rem; flex-shrink:0; }
    .pal-toast-success .pal-toast-dot { background:#10b981; } .pal-toast-danger .pal-toast-dot { background:#ef4444; }

    /* ── Modals (pal-) ── */
    .pal-modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:9999; align-items:center; justify-content:center; padding:1rem; backdrop-filter:blur(2px); }
    .pal-modal-overlay.pal-open { display:flex; }
    .pal-modal { background:#fff; border-radius:1rem; width:100%; max-width:520px; box-shadow:0 20px 60px rgba(0,0,0,.2); overflow:hidden; } .dark .pal-modal { background:#1f2937; }
    .pal-modal-header { padding:1.25rem 1.5rem 1rem; border-bottom:1px solid #f3f4f6; display:flex; align-items:flex-start; justify-content:space-between; } .dark .pal-modal-header { border-color:rgba(255,255,255,.06); }
    .pal-modal-title { font-size:.95rem; font-weight:800; color:#111827; } .dark .pal-modal-title { color:#f9fafb; }
    .pal-modal-sub   { font-size:.72rem; color:#6b7280; margin-top:2px; }
    .pal-modal-close { width:28px; height:28px; border-radius:7px; border:none; background:#374151; cursor:pointer; font-size:1rem; color:#fff; display:flex; align-items:center; justify-content:center; } .pal-modal-close:hover { background:#111827; }
    .pal-modal-body  { padding:1.5rem; }
    .pal-modal-footer { padding:1rem 1.5rem; border-top:1px solid #f3f4f6; display:flex; gap:.5rem; justify-content:flex-end; } .dark .pal-modal-footer { border-color:rgba(255,255,255,.06); }
    .pal-cfg-row { display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:.75rem 1rem; background:#f9fafb; border-radius:.65rem; border:1px solid #e5e7eb; margin-bottom:.5rem; cursor:grab; } .dark .pal-cfg-row { background:#111827; border-color:rgba(255,255,255,.08); } .pal-cfg-row:hover { border-color:#3b82f6; }
    .pal-cfg-row-left { display:flex; align-items:center; gap:.6rem; }
    .pal-cfg-row-num  { width:22px; height:22px; border-radius:50%; font-size:.7rem; font-weight:800; display:flex; align-items:center; justify-content:center; }
    .pal-cfg-row-name { font-size:.82rem; font-weight:700; color:#111827; } .dark .pal-cfg-row-name { color:#f9fafb; }
    .pal-cfg-row-desc { font-size:.7rem; color:#9ca3af; } .pal-cfg-handle { color:#d1d5db; font-size:1rem; }
    .pal-toggle { position:relative; display:inline-block; width:38px; height:20px; }
    .pal-toggle input { opacity:0; width:0; height:0; }
    .pal-toggle-slider { position:absolute; cursor:pointer; inset:0; background:#d1d5db; border-radius:999px; transition:.3s; }
    .pal-toggle-slider::before { content:''; position:absolute; width:14px; height:14px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.3s; }
    .pal-toggle input:checked + .pal-toggle-slider { background:#10b981; }
    .pal-toggle input:checked + .pal-toggle-slider::before { transform:translateX(18px); }

    /* ── ve modals ── */
    .ve-modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.55); z-index:10000; align-items:center; justify-content:center; padding:1rem; backdrop-filter:blur(3px); }
    .ve-modal-overlay.ve-open { display:flex; }
    .ve-modal { background:#fff; border-radius:1rem; width:100%; max-width:640px; box-shadow:0 24px 64px rgba(0,0,0,.22); overflow:hidden; max-height:92vh; display:flex; flex-direction:column; }
    .dark .ve-modal { background:#1f2937; }
    .ve-modal-sm { max-width:460px; }
    .ve-modal-header { padding:1.25rem 1.5rem 1rem; border-bottom:1px solid #f3f4f6; display:flex; align-items:flex-start; justify-content:space-between; flex-shrink:0; background:#1e3a5f; } .dark .ve-modal-header { border-color:rgba(255,255,255,.06); }
    .ve-modal-title { font-size:.95rem; font-weight:800; color:#fff; }
    .ve-modal-sub   { font-size:.72rem; color:rgba(255,255,255,.6); margin-top:2px; }
    .ve-modal-close { width:28px; height:28px; border-radius:7px; border:none; background:rgba(255,255,255,.15); cursor:pointer; font-size:1rem; color:#fff; display:flex; align-items:center; justify-content:center; flex-shrink:0; } .ve-modal-close:hover { background:rgba(255,255,255,.3); }
    .ve-modal-body   { padding:1.5rem; overflow-y:auto; flex:1; }
    .ve-modal-footer { padding:1rem 1.5rem; border-top:1px solid #f3f4f6; display:flex; gap:.5rem; justify-content:flex-end; flex-shrink:0; } .dark .ve-modal-footer { border-color:rgba(255,255,255,.06); }
    .ve-form-grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
    @media(max-width:600px) { .ve-form-grid { grid-template-columns:1fr; } }
    .ve-form-full { grid-column:1/-1; }
    .ve-form-label { display:block; font-size:.72rem; font-weight:700; color:#374151; margin-bottom:.3rem; } .dark .ve-form-label { color:#d1d5db; }
    .ve-form-input,.ve-form-select,.ve-form-textarea { width:100%; padding:.52rem .8rem; border:1.5px solid #e5e7eb; border-radius:.5rem; font-size:.82rem; color:#111827; background:#fff; transition:border-color .15s; font-family:inherit; }
    .dark .ve-form-input,.dark .ve-form-select,.dark .ve-form-textarea { background:#111827; border-color:rgba(255,255,255,.1); color:#f9fafb; }
    .ve-form-input:focus,.ve-form-select:focus,.ve-form-textarea:focus { outline:none; border-color:#1e3a5f; box-shadow:0 0 0 3px rgba(30,58,95,.08); }
    .ve-form-input:disabled { background:#f9fafb; color:#9ca3af; cursor:not-allowed; } .dark .ve-form-input:disabled { background:#374151; color:#6b7280; }
    .ve-form-textarea { resize:vertical; min-height:78px; line-height:1.55; }
    .ve-form-note { font-size:.67rem; color:#9ca3af; margin-top:.2rem; }
    .ve-summary { background:#f9fafb; border:1px solid #e5e7eb; border-radius:.75rem; overflow:hidden; margin-bottom:1.25rem; }
    .dark .ve-summary { background:#111827; border-color:rgba(255,255,255,.08); }
    .ve-summary-header { padding:.4rem 1rem; background:#1e3a5f; font-size:.63rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:#fff; border-bottom:1px solid #e5e7eb; }
    .dark .ve-summary-header { border-color:rgba(255,255,255,.06); }
    .ve-summary-grid { display:grid; grid-template-columns:repeat(3,1fr); }
    @media(max-width:520px) { .ve-summary-grid { grid-template-columns:repeat(2,1fr); } }
    .ve-summary-item { padding:.65rem 1rem; border-right:1px solid #e5e7eb; border-bottom:1px solid #e5e7eb; }
    .dark .ve-summary-item { border-color:rgba(255,255,255,.06); }
    .ve-summary-item:nth-child(3n) { border-right:none; } .ve-summary-item:nth-last-child(-n+3) { border-bottom:none; }
    .ve-summary-label { font-size:.61rem; color:#9ca3af; text-transform:uppercase; letter-spacing:.06em; }
    .ve-summary-value { font-size:.85rem; font-weight:700; color:#111827; margin-top:2px; } .dark .ve-summary-value { color:#f9fafb; }
    .ve-warn { display:flex; align-items:flex-start; gap:.75rem; background:#dc2626; border-radius:.75rem; padding:.9rem 1rem; margin-bottom:1.25rem; }
    .ve-warn-icon { font-size:1.1rem; flex-shrink:0; margin-top:1px; } .ve-warn-text { font-size:.8rem; color:#fff; font-weight:600; line-height:1.55; }
    .ve-reason-footer { display:flex; align-items:center; justify-content:space-between; margin-top:.3rem; }
    .ve-progress-bar  { height:3px; flex:1; background:#e5e7eb; border-radius:999px; overflow:hidden; margin-right:.75rem; }
    .ve-progress-fill { height:100%; border-radius:999px; transition:width .2s, background .2s; }
    .ve-char-count { font-size:.65rem; color:#9ca3af; white-space:nowrap; }
    .ve-char-count.ve-ok { color:#059669; font-weight:700; }
    .ve-timeline { display:flex; flex-direction:column; }
    .ve-tl-item { display:flex; gap:.85rem; align-items:flex-start; }
    .ve-tl-spine { display:flex; flex-direction:column; align-items:center; flex-shrink:0; width:28px; }
    .ve-tl-dot { width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.72rem; font-weight:700; flex-shrink:0; }
    .ve-tl-dot-created { background:#059669; color:#fff; }
    .ve-tl-dot-edit    { background:#d97706; color:#fff; }
    .ve-tl-dot-void    { background:#dc2626; color:#fff; }
    .ve-tl-line { width:2px; background:#e5e7eb; flex:1; min-height:20px; margin:3px 0; } .dark .ve-tl-line { background:rgba(255,255,255,.07); }
    .ve-tl-body { flex:1; padding-bottom:1.25rem; }
    .ve-tl-card { background:#f9fafb; border:1px solid #e5e7eb; border-radius:.65rem; padding:.75rem 1rem; }
    .dark .ve-tl-card { background:#111827; border-color:rgba(255,255,255,.07); }
    .ve-tl-action { font-size:.8rem; font-weight:700; color:#111827; } .dark .ve-tl-action { color:#f9fafb; }
    .ve-tl-meta  { font-size:.68rem; color:#9ca3af; margin-top:2px; }
    .ve-tl-note  { font-size:.73rem; color:#374151; margin-top:.4rem; padding-top:.4rem; border-top:1px solid #f0f0f0; line-height:1.55; font-style:italic; }
    .dark .ve-tl-note { border-color:rgba(255,255,255,.06); color:#9ca3af; }
    .ve-tl-changes { margin-top:.5rem; border-top:1px solid #f0f0f0; padding-top:.5rem; }
    .dark .ve-tl-changes { border-color:rgba(255,255,255,.06); }
    .ve-tl-change-row { display:flex; gap:.4rem; align-items:flex-start; font-size:.7rem; margin-bottom:.25rem; }
    .ve-tl-change-field { font-weight:700; color:#374151; min-width:110px; flex-shrink:0; }
    .dark .ve-tl-change-field { color:#d1d5db; }
    .ve-tl-change-from { color:#dc2626; text-decoration:line-through; }
    .ve-tl-change-arrow { color:#9ca3af; flex-shrink:0; }
    .ve-tl-change-to   { color:#059669; font-weight:700; }
    .ve-audit-empty { text-align:center; padding:2.5rem 1rem; color:#9ca3af; font-size:.82rem; }
    .ve-btn { display:inline-flex; align-items:center; gap:4px; font-size:.74rem; font-weight:600; padding:5px 11px; border-radius:7px; border:none; cursor:pointer; transition:all .15s; font-family:inherit; white-space:nowrap; color:#fff; }
    .ve-btn:hover { filter:brightness(1.1); transform:translateY(-1px); }
    .ve-btn:disabled { opacity:.45; cursor:not-allowed; transform:none; filter:none; }
    .ve-btn-ghost   { background:#374151; }
    .ve-btn-primary { background:#1e3a5f; }
    .ve-btn-danger  { background:#dc2626; }

    @media(max-width:640px) { .pal-hero { padding:1.5rem; } .pal-hero-title { font-size:1.3rem; } .pal-stats { gap:.75rem; } }
</style>

<div class="pal-wrap">

{{-- ══ HERO ══ --}}
<div class="pal-hero">
    <div class="pal-hero-badge"><svg width="8" height="8" viewBox="0 0 8 8" fill="currentColor"><circle cx="4" cy="4" r="4"/></svg> Payment Management</div>
    <h1 class="pal-hero-title">Payment Allocation Logic</h1>
    <p class="pal-hero-sub">Configure how payments are distributed across interest, principal, and penalties — with override controls.</p>
    <div class="pal-stats">
        <div class="pal-stat"><div class="pal-stat-value" id="pal-stat-mode">Auto</div><div class="pal-stat-label">Allocation Mode</div></div>
        <div class="pal-stat"><div class="pal-stat-value">3</div><div class="pal-stat-label">Priority Levels</div></div>
        <div class="pal-stat"><div class="pal-stat-value" style="color:#f87171;">{{ \App\Models\LoanPayment::where('status','Void')->count() }}</div><div class="pal-stat-label">Voided Payments</div></div>
    </div>
</div>

{{-- ══ PRIORITY BAR ══ --}}
<div class="pal-priority-wrap">
    <div class="pal-priority-label">Default Allocation Priority <span onclick="palOpenModal('pal-modal-configure')">— CLICK TO REORDER</span></div>
    <div class="pal-priority-steps">
        <div class="pal-step"><div class="pal-step-num pal-step-num-1">1</div><div><div class="pal-step-name">Interest</div><div class="pal-step-sub">Applied first</div></div></div>
        <div class="pal-arrow">→</div>
        <div class="pal-step"><div class="pal-step-num pal-step-num-2">2</div><div><div class="pal-step-name">Principal</div><div class="pal-step-sub">Loan balance</div></div></div>
        <div class="pal-arrow">→</div>
        <div class="pal-step"><div class="pal-step-num pal-step-num-3">3</div><div><div class="pal-step-name">Penalties</div><div class="pal-step-sub">Applied last</div></div></div>
    </div>
    <div class="pal-override-note">⚠ Manual override available — admin can reorder allocation per payment</div>
</div>

{{-- ══ FEATURE CARDS ══ --}}
<div class="pal-features-label">Allocation Features</div>
<div class="pal-cards">
    <div class="pal-card">
        <div class="pal-card-top pal-card-top-yellow"></div>
        <div class="pal-card-body">
            <div class="pal-card-icon">⚙️</div>
            <div class="pal-card-title">Partial &amp; Advance Payments</div>
            <div class="pal-card-badges"><span class="pal-card-badge pal-card-badge-partial">Partial</span><span class="pal-card-badge pal-card-badge-advance">Advance</span><span class="pal-card-badge pal-card-badge-overpayment">Overpayment</span></div>
            <div class="pal-card-desc">Full support for partial, advance, and overpayments with automatic carry-forward to the next due date.</div>
            <div class="pal-card-footer"><button class="pal-btn pal-btn-yellow" onclick="palOpenModal('pal-modal-configure')"><svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="3"/><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg> Configure</button><span class="pal-card-note pal-card-note-yellow">Auto carry-forward</span></div>
        </div>
    </div>
    <div class="pal-card">
        <div class="pal-card-top pal-card-top-red"></div>
        <div class="pal-card-body">
            <div class="pal-card-icon">❌</div>
            <div class="pal-card-title">Void / Edit Payments</div>
            <div class="pal-card-badges"><span class="pal-card-badge pal-card-badge-void">Void</span><span class="pal-card-badge pal-card-badge-edit">Edit</span><span class="pal-card-badge pal-card-badge-audit">Audit Log</span></div>
            <div class="pal-card-desc">Void or edit posted payments with mandatory reason entry. Every change is permanently logged with user and timestamp.</div>
            <div class="pal-card-footer">
                <button class="pal-btn pal-btn-red" onclick="palScrollToCollections()">
                    <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Manage Voids
                </button>
                <span class="pal-card-note pal-card-note-red">Reason required</span>
            </div>
        </div>
    </div>
    <div class="pal-card">
        <div class="pal-card-top pal-card-top-green"></div>
        <div class="pal-card-body">
            <div class="pal-card-icon">🔗</div>
            <div class="pal-card-title">Auto-Apply to Loan</div>
            <div class="pal-card-badges"><span class="pal-card-badge pal-card-badge-auto">Auto-match</span><span class="pal-card-badge pal-card-badge-override">Override</span></div>
            <div class="pal-card-desc">Automatically matches and applies payments to the correct loan account. Admin can manually override the target account if needed.</div>
            <div class="pal-card-footer"><button class="pal-btn pal-btn-green" onclick="palOpenModal('pal-modal-rules')"><svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg> View Rules</button><span class="pal-card-note pal-card-note-green">Smart matching</span></div>
        </div>
    </div>
</div>

{{-- ══ PAYMENT SIMULATOR (preview only — not saved) ══ --}}
<div class="pal-simulator">
    <div class="pal-sim-header">
        <div>
            <div class="pal-sim-title">⚡ Allocation Simulator <span class="pal-sim-badge">PREVIEW ONLY — Not Saved</span></div>
            <div class="pal-sim-sub">Preview how a payment would be allocated. To post a real payment, use the Collections &amp; Posting section below.</div>
        </div>
    </div>
    <div class="pal-sim-body">
        <div class="pal-sim-preview-note">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            This simulator is for preview only. No data is saved. Use <strong style="margin:0 3px;">Collections &amp; Posting</strong> below to post actual payments.
        </div>
        <div id="pal-accounts-data" style="display:none;">
            @foreach(\App\Models\LoanAccount::with(['loanApplication.member.profile'])->where('status','Active')->get() as $acc)
            <span data-id="{{ $acc->loan_account_id }}"
                data-label="LA-{{ str_pad($acc->loan_account_id,5,'0',STR_PAD_LEFT) }}"
                data-member="{{ $acc->loanApplication?->member?->profile?->full_name ?? 'Unknown' }}"
                data-balance="{{ $acc->balance }}" data-due="{{ $acc->monthly_amortization }}"
                data-rate="{{ $acc->interest_rate }}" data-principal="{{ $acc->principal_amount }}"
                data-collateral="{{ $acc->loanApplication?->collateral_status }}"></span>
            @endforeach
        </div>
        <div class="pal-sim-grid">
            <div class="pal-sim-field-full">
                <label class="pal-sim-label">Loan Account (for preview)</label>
                <div class="pal-search-wrap" id="pal-search-wrap">
                    <svg class="pal-search-icon" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35"/></svg>
                    <input type="text" class="pal-search-input" id="pal-search-input" placeholder="Search by loan ID, member name, or balance…" oninput="palFilterAccounts()" onfocus="palShowDropdown()" autocomplete="off" />
                    <span class="pal-search-clear" id="pal-search-clear" onclick="palClearSearch()">✕</span>
                    <div class="pal-dropdown" id="pal-dropdown"></div>
                </div>
                <div class="pal-selected-chip" id="pal-selected-chip">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span id="pal-selected-text"></span>
                    <span class="pal-selected-chip-clear" onclick="palClearSearch()" title="Change account">✕ Change</span>
                </div>
            </div>
            <div><label class="pal-sim-label">Current Balance</label><input class="pal-sim-input" id="pal-balance" disabled placeholder="—" /></div>
            <div><label class="pal-sim-label">Monthly Amortization (Due)</label><input class="pal-sim-input" id="pal-due" disabled placeholder="—" /></div>
            <div><label class="pal-sim-label">Payment Date</label><input type="date" class="pal-sim-input" id="pal-date" value="{{ date('Y-m-d') }}" /></div>
            <div><label class="pal-sim-label">Amount Paid</label><input type="number" class="pal-sim-input" id="pal-amount" placeholder="0.00" min="0" step="0.01" oninput="palCompute()" /></div>
            <div><label class="pal-sim-label">Penalty Amount</label><input type="number" class="pal-sim-input" id="pal-penalty" placeholder="0.00" min="0" step="0.01" value="0" oninput="palCompute()" /></div>
        </div>
        <div class="pal-result" id="pal-result" style="display:none;">
            <div class="pal-result-header"><div class="pal-result-title">Allocation Breakdown (Preview)</div><div id="pal-type-badge"></div></div>
            <div class="pal-result-grid">
                <div class="pal-result-item"><div class="pal-result-item-label">Amount Due</div><div class="pal-result-item-value" id="pal-r-due">—</div></div>
                <div class="pal-result-item"><div class="pal-result-item-label">Amount Paid</div><div class="pal-result-item-value" id="pal-r-paid">—</div></div>
                <div class="pal-result-item"><div class="pal-result-item-label">Overpayment / Carry Fwd</div><div class="pal-result-item-value amber" id="pal-r-carry">₱0.00</div></div>
                <div class="pal-result-item"><div class="pal-result-item-label">Interest Applied</div><div class="pal-result-item-value" id="pal-r-interest">₱0.00</div></div>
                <div class="pal-result-item"><div class="pal-result-item-label">Principal Applied</div><div class="pal-result-item-value" id="pal-r-principal">₱0.00</div></div>
                <div class="pal-result-item"><div class="pal-result-item-label">New Balance (est.)</div><div class="pal-result-item-value green" id="pal-r-balance">—</div></div>
            </div>
            <div style="padding:1rem;">
                <div style="font-size:.68rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#9ca3af;margin-bottom:.5rem;">Allocation Flow</div>
                <div class="pal-flow">
                    <div class="pal-flow-step" id="pal-flow-interest"><div class="pal-flow-step-num">1</div><div class="pal-flow-step-name">Interest</div><div class="pal-flow-step-value" id="pal-flow-interest-val">₱0.00</div></div>
                    <div class="pal-flow-arrow">→</div>
                    <div class="pal-flow-step" id="pal-flow-principal"><div class="pal-flow-step-num">2</div><div class="pal-flow-step-name">Principal</div><div class="pal-flow-step-value" id="pal-flow-principal-val">₱0.00</div></div>
                    <div class="pal-flow-arrow">→</div>
                    <div class="pal-flow-step" id="pal-flow-penalty"><div class="pal-flow-step-num">3</div><div class="pal-flow-step-name">Penalty</div><div class="pal-flow-step-value" id="pal-flow-penalty-val">₱0.00</div></div>
                    <div class="pal-flow-arrow">→</div>
                    <div class="pal-flow-step" id="pal-flow-carry"><div class="pal-flow-step-num">4</div><div class="pal-flow-step-name">Carry Fwd</div><div class="pal-flow-step-value" id="pal-flow-carry-val">₱0.00</div></div>
                </div>
                <div class="pal-carry-note" id="pal-carry-note" style="display:none;">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span id="pal-carry-note-text"></span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══ COLLECTIONS & POSTING TABLE ══ --}}
<div class="pal-posted-wrap" id="pal-posted-wrap">
    <div class="pal-posted-header">
        <div>
            <div class="pal-posted-title">💳 Collections &amp; Posting</div>
            <div class="pal-posted-sub">All posted payments. Use the actions to View details, check the Audit Log, Edit, or Void payments.</div>
        </div>
        <div style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;">
            <input type="text" id="pal-tbl-search" placeholder="Filter by member or LA…"
                style="padding:.4rem .75rem;border:1.5px solid #e5e7eb;border-radius:.5rem;font-size:.75rem;font-family:inherit;width:190px;"
                oninput="palFilterTable(this.value)" />
            <span class="pal-posted-count" id="pal-posted-count">{{ \App\Models\LoanPayment::count() }} records</span>
        </div>
    </div>

    {{-- Sort toolbar --}}
    <div class="pal-sort-bar" id="pal-sort-bar">
        <span class="pal-sort-bar-label">Sort by</span>
        <button class="pal-sort-pill" id="pal-sp-member" onclick="palSortTable(2)">
            <svg width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            Member <span class="sp-arrow">↕</span>
        </button>
        <button class="pal-sort-pill" id="pal-sp-type" onclick="palSortTable(3)">Type <span class="sp-arrow">↕</span>
        </button>
        <button class="pal-sort-pill" id="pal-sp-date" onclick="palSortTable(4)">
            <svg width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            Date <span class="sp-arrow">↕</span>
        </button>
        <button class="pal-sort-pill" id="pal-sp-status" onclick="palSortTable(7)">Status <span class="sp-arrow">↕</span>
        </button>
        <div class="pal-sort-divider"></div>
        <button class="pal-sort-reset" id="pal-sort-reset" onclick="palSortReset()">
            <svg width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            Clear
        </button>
    </div>

    <div class="pal-table-scroll">
        <table class="pal-table" id="pal-main-table">
            <thead>
                <tr>
                    <th style="width:60px;">ID</th>
                    <th style="width:110px;">Loan Account</th>
                    <th data-col="2">Member</th>
                    <th data-col="3">Type</th>
                    <th data-col="4">Date</th>
                    <th data-col="5">Amount Paid</th>
                    <th data-col="6">Remaining Bal.</th>
                    <th data-col="7">Status</th>
                    <th>Posted By</th>
                    <th style="min-width:200px;">Actions</th>
                </tr>
            </thead>
            <tbody id="pal-posted-tbody">
                @forelse(\App\Models\LoanPayment::with(['loanAccount','loanApplication.member.profile','postedBy'])->orderByDesc('created_at')->take(50)->get() as $pay)
                @php
                    $member = $pay->loanApplication?->member?->profile?->full_name ?? '—';
                    $tc = match($pay->payment_type??''){
                        'Partial'=>'pal-type-partial','Advance'=>'pal-type-advance',
                        'Overpayment'=>'pal-type-overpayment',default=>''
                    };
                    $isPosted = $pay->status === 'Posted';
                @endphp
                <tr class="pal-db-row"
                    data-id="{{ $pay->loan_payment_id }}"
                    data-member="{{ strtolower($member) }}"
                    data-label="la-{{ str_pad($pay->loan_account_id,5,'0',STR_PAD_LEFT) }}"
                    data-status="{{ $pay->status }}">
                    <td style="color:#9ca3af;font-size:.72rem;font-weight:600;">#{{ $pay->loan_payment_id }}</td>
                    <td style="font-weight:700;color:#1e3a5f;">LA-{{ str_pad($pay->loan_account_id,5,'0',STR_PAD_LEFT) }}</td>
                    <td style="font-weight:500;">{{ $member }}</td>
                    <td>
                        @if($pay->payment_type)
                            <span class="pal-type-badge {{ $tc }}">{{ $pay->payment_type }}</span>
                        @else <span style="color:#9ca3af;">—</span> @endif
                    </td>
                    <td style="color:#6b7280;">{{ $pay->payment_date?->format('M d, Y') ?? '—' }}</td>
                    <td style="font-weight:700;">₱{{ number_format($pay->amount_paid,2) }}</td>
                    <td style="font-weight:800;color:{{ ($pay->remaining_balance??0)<=0 ? '#059669' : '#111827' }};">
                        ₱{{ number_format($pay->remaining_balance??0,2) }}
                        @if(($pay->remaining_balance??0)<=0)
                            <span style="font-size:.6rem;background:#059669;color:#fff;padding:1px 6px;border-radius:999px;font-weight:700;margin-left:3px;">PAID OFF</span>
                        @endif
                    </td>
                    <td>
                        <span class="pal-type-badge {{ $isPosted ? 'pal-status-posted' : 'pal-status-void' }}">{{ $pay->status }}</span>
                    </td>
                    <td style="color:#6b7280;font-size:.75rem;">{{ $pay->postedBy?->name ?? '—' }}</td>
                    <td>
                        <div style="display:flex;gap:5px;align-items:center;flex-wrap:nowrap;">
                            <button class="pal-action-btn pal-action-view" onclick="veOpenView({{ $pay->loan_payment_id }})">
                                <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                View
                            </button>
                            <button class="pal-action-btn pal-action-audit" onclick="veOpenAudit({{ $pay->loan_payment_id }})">
                                <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                Log
                            </button>
                            @if($isPosted)
                            <button class="pal-action-btn pal-action-edit" onclick="veOpenEdit({{ $pay->loan_payment_id }})">
                                <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Edit
                            </button>
                            <button class="pal-action-btn pal-action-void"
                                onclick="veOpenVoid({{ $pay->loan_payment_id }},'{{ addslashes($member) }}',{{ $pay->amount_paid }},'{{ $pay->payment_date?->format('M d, Y')??'—' }}')">
                                <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Void
                            </button>
                            @else
                            <span style="font-size:.7rem;color:#9ca3af;font-style:italic;padding:4px 8px;">Voided</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr id="pal-empty-row"><td colspan="10"><div class="pal-table-empty"><div class="pal-table-empty-icon">🧾</div><div class="pal-table-empty-text">No payments posted yet.</div></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ══ CONFIGURE MODAL ══ --}}
<div class="pal-modal-overlay" id="pal-modal-configure" onclick="palBgClose(event,'pal-modal-configure')">
    <div class="pal-modal">
        <div class="pal-modal-header"><div><div class="pal-modal-title">Configure Allocation</div><div class="pal-modal-sub">Set priority order and payment behavior options.</div></div><button class="pal-modal-close" onclick="palCloseModal('pal-modal-configure')">✕</button></div>
        <div class="pal-modal-body">
            <div style="font-size:.73rem;font-weight:700;color:#374151;margin-bottom:.6rem;">Priority Order (drag to reorder)</div>
            <div id="pal-cfg-list">
                <div class="pal-cfg-row" draggable="true" data-key="interest"><div class="pal-cfg-row-left"><div class="pal-cfg-row-num" style="background:#1e3a5f;color:#fff;">1</div><div><div class="pal-cfg-row-name">Interest</div><div class="pal-cfg-row-desc">Applied first from every payment</div></div></div><div class="pal-cfg-handle">⠿</div></div>
                <div class="pal-cfg-row" draggable="true" data-key="principal"><div class="pal-cfg-row-left"><div class="pal-cfg-row-num" style="background:#6366f1;color:#fff;">2</div><div><div class="pal-cfg-row-name">Principal</div><div class="pal-cfg-row-desc">Reduces outstanding loan balance</div></div></div><div class="pal-cfg-handle">⠿</div></div>
                <div class="pal-cfg-row" draggable="true" data-key="penalty"><div class="pal-cfg-row-left"><div class="pal-cfg-row-num" style="background:#8b5cf6;color:#fff;">3</div><div><div class="pal-cfg-row-name">Penalties</div><div class="pal-cfg-row-desc">Applied last from remaining amount</div></div></div><div class="pal-cfg-handle">⠿</div></div>
            </div>
            <div style="margin-top:1.25rem;">
                <div style="font-size:.73rem;font-weight:700;color:#374151;margin-bottom:.75rem;">Behavior Options</div>
                <div style="display:flex;flex-direction:column;gap:.6rem;">
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:.65rem .85rem;background:#f9fafb;border-radius:.6rem;border:1px solid #e5e7eb;"><div><div style="font-size:.8rem;font-weight:700;color:#111827;">Auto Carry-Forward</div><div style="font-size:.68rem;color:#9ca3af;">Overpayment credit applied to next due</div></div><label class="pal-toggle"><input type="checkbox" checked id="pal-toggle-carry"><span class="pal-toggle-slider"></span></label></div>
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:.65rem .85rem;background:#f9fafb;border-radius:.6rem;border:1px solid #e5e7eb;"><div><div style="font-size:.8rem;font-weight:700;color:#111827;">Allow Partial Payments</div><div style="font-size:.68rem;color:#9ca3af;">Accept amounts less than amount due</div></div><label class="pal-toggle"><input type="checkbox" checked id="pal-toggle-partial"><span class="pal-toggle-slider"></span></label></div>
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:.65rem .85rem;background:#f9fafb;border-radius:.6rem;border:1px solid #e5e7eb;"><div><div style="font-size:.8rem;font-weight:700;color:#111827;">Allow Advance Payments</div><div style="font-size:.68rem;color:#9ca3af;">Accept amounts greater than amount due</div></div><label class="pal-toggle"><input type="checkbox" checked id="pal-toggle-advance"><span class="pal-toggle-slider"></span></label></div>
                </div>
            </div>
        </div>
        <div class="pal-modal-footer"><button class="pal-btn pal-btn-ghost" onclick="palCloseModal('pal-modal-configure')">Cancel</button><button class="pal-btn pal-btn-primary" onclick="palSaveConfig()"><svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Save Configuration</button></div>
    </div>
</div>

{{-- ══ RULES MODAL ══ --}}
<div class="pal-modal-overlay" id="pal-modal-rules" onclick="palBgClose(event,'pal-modal-rules')">
    <div class="pal-modal">
        <div class="pal-modal-header"><div><div class="pal-modal-title">Auto-Apply Rules</div><div class="pal-modal-sub">How payments are automatically matched to loan accounts.</div></div><button class="pal-modal-close" onclick="palCloseModal('pal-modal-rules')">✕</button></div>
        <div class="pal-modal-body">
            <div style="display:flex;flex-direction:column;gap:.75rem;">
                @foreach([['#10b981','1','Match by Member ID','Payment is matched to the active loan account of the member.'],['#3b82f6','2','Match by Loan Account','If a specific LA number is provided, it takes priority.'],['#f59e0b','3','Earliest Due First','Multiple active loans are prioritized by earliest maturity date.'],['#8b5cf6','4','Admin Override','Admin can reassign the payment target at any time before posting.']] as [$color,$num,$title,$desc])
                <div style="display:flex;gap:.85rem;align-items:flex-start;padding:.85rem 1rem;background:#f9fafb;border-radius:.7rem;border:1px solid #e5e7eb;"><div style="width:26px;height:26px;border-radius:50%;background:{{ $color }};color:#fff;font-size:.72rem;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">{{ $num }}</div><div><div style="font-size:.82rem;font-weight:700;color:#111827;">{{ $title }}</div><div style="font-size:.72rem;color:#6b7280;margin-top:2px;">{{ $desc }}</div></div></div>
                @endforeach
            </div>
        </div>
        <div class="pal-modal-footer"><button class="pal-btn pal-btn-ghost" onclick="palCloseModal('pal-modal-rules')">Close</button></div>
    </div>
</div>

{{-- ══ VIEW MODAL ══ --}}
<div class="ve-modal-overlay" id="ve-modal-view" onclick="veBgClose(event,'ve-modal-view')">
    <div class="ve-modal">
        <div class="ve-modal-header">
            <div>
                <div class="ve-modal-title">👁 Payment Details <span id="ve-view-id-label" style="color:rgba(255,255,255,.55);font-size:.82rem;font-weight:500;"></span></div>
                <div class="ve-modal-sub">Full breakdown of this payment record.</div>
            </div>
            <button class="ve-modal-close" onclick="veCloseModal('ve-modal-view')">✕</button>
        </div>
        <div class="ve-modal-body">
            <div id="ve-view-loading" style="text-align:center;padding:2.5rem;color:#9ca3af;"><div style="font-size:1.5rem;margin-bottom:.5rem;">⏳</div><div style="font-size:.8rem;">Loading…</div></div>
            <div id="ve-view-content" style="display:none;">
                <div id="ve-view-chips" style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1.25rem;"></div>
                <div class="ve-summary">
                    <div class="ve-summary-header">Payment Information</div>
                    <div class="ve-summary-grid">
                        <div class="ve-summary-item"><div class="ve-summary-label">Loan Account</div><div class="ve-summary-value" id="ve-v-account">—</div></div>
                        <div class="ve-summary-item"><div class="ve-summary-label">Member</div><div class="ve-summary-value" id="ve-v-member">—</div></div>
                        <div class="ve-summary-item"><div class="ve-summary-label">Payment Date</div><div class="ve-summary-value" id="ve-v-date">—</div></div>
                        <div class="ve-summary-item"><div class="ve-summary-label">Amount Paid</div><div class="ve-summary-value" id="ve-v-amount" style="color:#059669;">—</div></div>
                        <div class="ve-summary-item"><div class="ve-summary-label">Amount Due</div><div class="ve-summary-value" id="ve-v-due">—</div></div>
                        <div class="ve-summary-item"><div class="ve-summary-label">Type</div><div class="ve-summary-value" id="ve-v-type">—</div></div>
                    </div>
                </div>
                <div class="ve-summary" style="margin-top:.85rem;">
                    <div class="ve-summary-header">Allocation Breakdown</div>
                    <div class="ve-summary-grid">
                        <div class="ve-summary-item"><div class="ve-summary-label">Interest</div><div class="ve-summary-value" id="ve-v-interest">—</div></div>
                        <div class="ve-summary-item"><div class="ve-summary-label">Principal</div><div class="ve-summary-value" id="ve-v-principal">—</div></div>
                        <div class="ve-summary-item"><div class="ve-summary-label">Penalty</div><div class="ve-summary-value" id="ve-v-penalty">—</div></div>
                        <div class="ve-summary-item"><div class="ve-summary-label">Carry Forward</div><div class="ve-summary-value" id="ve-v-carry" style="color:#d97706;">—</div></div>
                        <div class="ve-summary-item"><div class="ve-summary-label">Remaining Balance</div><div class="ve-summary-value" id="ve-v-balance">—</div></div>
                        <div class="ve-summary-item"><div class="ve-summary-label">Status</div><div class="ve-summary-value" id="ve-v-status">—</div></div>
                    </div>
                </div>
                <div style="margin-top:.85rem;display:flex;gap:1rem;flex-wrap:wrap;">
                    <div style="font-size:.72rem;color:#9ca3af;">Posted by: <strong id="ve-v-postedby" style="color:#374151;">—</strong></div>
                    <div style="font-size:.72rem;color:#9ca3af;">Posted on: <strong id="ve-v-createdat" style="color:#374151;">—</strong></div>
                    <div id="ve-v-remarks-wrap" style="font-size:.72rem;color:#9ca3af;display:none;">Remarks: <strong id="ve-v-remarks" style="color:#374151;">—</strong></div>
                </div>
            </div>
        </div>
        <div class="ve-modal-footer">
            <button class="ve-btn ve-btn-ghost" onclick="veCloseModal('ve-modal-view');veOpenAudit(parseInt(document.getElementById('ve-view-id-label').textContent.replace(/\D/g,'')))">
                <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                View Audit Log
            </button>
            <button class="ve-btn ve-btn-ghost" onclick="veCloseModal('ve-modal-view')">Close</button>
        </div>
    </div>
</div>

{{-- ══ EDIT MODAL ══ --}}
<div class="ve-modal-overlay" id="ve-modal-edit" onclick="veBgClose(event,'ve-modal-edit')">
    <div class="ve-modal">
        <div class="ve-modal-header">
            <div>
                <div class="ve-modal-title">✏️ Edit Payment <span id="ve-edit-id-label" style="color:rgba(255,255,255,.55);font-size:.82rem;font-weight:500;"></span></div>
                <div class="ve-modal-sub">Modify payment details. A reason is required and logged permanently.</div>
            </div>
            <button class="ve-modal-close" onclick="veCloseModal('ve-modal-edit')">✕</button>
        </div>
        <div class="ve-modal-body">
            <div id="ve-edit-loading" style="text-align:center;padding:2.5rem;color:#9ca3af;display:none;"><div style="font-size:1.5rem;margin-bottom:.5rem;">⏳</div><div style="font-size:.8rem;">Loading…</div></div>
            <div id="ve-edit-warn" class="ve-warn" style="display:none;"><div class="ve-warn-icon">⚠️</div><div class="ve-warn-text" id="ve-edit-warn-text"></div></div>
            <div id="ve-edit-content" style="display:none;">
                <div class="ve-summary" style="margin-bottom:1.25rem;">
                    <div class="ve-summary-header">Current Record</div>
                    <div class="ve-summary-grid">
                        <div class="ve-summary-item"><div class="ve-summary-label">Account</div><div class="ve-summary-value" id="ve-s-account">—</div></div>
                        <div class="ve-summary-item"><div class="ve-summary-label">Member</div><div class="ve-summary-value" id="ve-s-member">—</div></div>
                        <div class="ve-summary-item"><div class="ve-summary-label">Posted By</div><div class="ve-summary-value" id="ve-s-postedby">—</div></div>
                        <div class="ve-summary-item"><div class="ve-summary-label">Amount</div><div class="ve-summary-value" id="ve-s-amount">—</div></div>
                        <div class="ve-summary-item"><div class="ve-summary-label">Type</div><div class="ve-summary-value" id="ve-s-type">—</div></div>
                        <div class="ve-summary-item"><div class="ve-summary-label">Created</div><div class="ve-summary-value" id="ve-s-created">—</div></div>
                    </div>
                </div>
                <input type="hidden" id="ve-edit-payment-id" />
                <div class="ve-form-grid">
                    <div><label class="ve-form-label">Payment Date</label><input type="date" class="ve-form-input" id="ve-edit-date" /></div>
                    <div><label class="ve-form-label">Payment Type</label><select class="ve-form-select" id="ve-edit-type"><option value="Partial">Partial</option><option value="Advance">Advance</option><option value="Overpayment">Overpayment</option></select></div>
                    <div><label class="ve-form-label">Amount Paid</label><input type="number" class="ve-form-input" id="ve-edit-amount" step="0.01" /></div>
                    <div><label class="ve-form-label">Amount Due</label><input type="number" class="ve-form-input" id="ve-edit-due" step="0.01" /></div>
                    <div><label class="ve-form-label">Interest Paid</label><input type="number" class="ve-form-input" id="ve-edit-interest" step="0.01" /></div>
                    <div><label class="ve-form-label">Principal Paid</label><input type="number" class="ve-form-input" id="ve-edit-principal" step="0.01" /></div>
                    <div><label class="ve-form-label">Penalty Paid</label><input type="number" class="ve-form-input" id="ve-edit-penalty" step="0.01" /></div>
                    <div><label class="ve-form-label">Carry Forward</label><input type="number" class="ve-form-input" id="ve-edit-carry" step="0.01" /></div>
                    <div class="ve-form-full"><label class="ve-form-label">Remaining Balance</label><input type="number" class="ve-form-input" id="ve-edit-remaining" step="0.01" /></div>
                    <div class="ve-form-full"><label class="ve-form-label">Remarks</label><input type="text" class="ve-form-input" id="ve-edit-remarks" /></div>
                    <div class="ve-form-full">
                        <label class="ve-form-label">Reason for Edit <span style="color:#ef4444;">*</span></label>
                        <textarea class="ve-form-textarea" id="ve-edit-reason" placeholder="Describe why this payment is being edited (minimum 10 characters)…" oninput="veCharCount('ve-edit-reason','ve-edit-reason-count','ve-edit-reason-bar',10)"></textarea>
                        <div class="ve-reason-footer"><div class="ve-progress-bar"><div class="ve-progress-fill" id="ve-edit-reason-bar" style="width:0%"></div></div><span class="ve-char-count" id="ve-edit-reason-count">0 / 10 min</span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="ve-modal-footer">
            <button class="ve-btn ve-btn-ghost" onclick="veCloseModal('ve-modal-edit')">Cancel</button>
            <button class="ve-btn ve-btn-primary" id="ve-edit-submit" disabled onclick="veSubmitEdit()">
                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Save Changes
            </button>
        </div>
    </div>
</div>

{{-- ══ VOID MODAL ══ --}}
<div class="ve-modal-overlay" id="ve-modal-void" onclick="veBgClose(event,'ve-modal-void')">
    <div class="ve-modal ve-modal-sm">
        <div class="ve-modal-header">
            <div>
                <div class="ve-modal-title">🚫 Void Payment</div>
                <div class="ve-modal-sub" id="ve-void-subtitle"></div>
            </div>
            <button class="ve-modal-close" onclick="veCloseModal('ve-modal-void')">✕</button>
        </div>
        <div class="ve-modal-body">
            <div id="ve-void-info" style="display:flex;gap:.4rem;flex-wrap:wrap;margin-bottom:1rem;"></div>
            <div class="ve-warn"><div class="ve-warn-icon">⚠️</div><div class="ve-warn-text">This action is <strong>irreversible</strong>. The payment will be marked void and the loan balance will be restored.</div></div>
            <input type="hidden" id="ve-void-payment-id" />
            <div>
                <label class="ve-form-label">Reason for Void <span style="color:#ef4444;">*</span></label>
                <textarea class="ve-form-textarea" id="ve-void-reason" placeholder="Describe why this payment is being voided (minimum 10 characters)…" oninput="veCharCount('ve-void-reason','ve-void-reason-count','ve-void-reason-bar',10)"></textarea>
                <div class="ve-reason-footer"><div class="ve-progress-bar"><div class="ve-progress-fill" id="ve-void-reason-bar" style="width:0%"></div></div><span class="ve-char-count" id="ve-void-reason-count">0 / 10 min</span></div>
            </div>
        </div>
        <div class="ve-modal-footer">
            <button class="ve-btn ve-btn-ghost" onclick="veCloseModal('ve-modal-void')">Cancel</button>
            <button class="ve-btn ve-btn-danger" id="ve-void-submit" onclick="veSubmitVoid()">
                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Confirm Void
            </button>
        </div>
    </div>
</div>

{{-- ══ AUDIT LOG MODAL ══ --}}
<div class="ve-modal-overlay" id="ve-modal-audit" onclick="veBgClose(event,'ve-modal-audit')">
    <div class="ve-modal">
        <div class="ve-modal-header">
            <div>
                <div class="ve-modal-title">📋 Audit Log <span id="ve-audit-id-label" style="color:rgba(255,255,255,.55);font-size:.82rem;font-weight:500;"></span></div>
                <div class="ve-modal-sub">Full history of changes made to this payment.</div>
            </div>
            <button class="ve-modal-close" onclick="veCloseModal('ve-modal-audit')">✕</button>
        </div>
        <div class="ve-modal-body">
            <div id="ve-audit-loading" style="text-align:center;padding:2.5rem;color:#9ca3af;"><div style="font-size:1.5rem;margin-bottom:.5rem;">⏳</div><div style="font-size:.8rem;">Loading…</div></div>
            <div class="ve-timeline" id="ve-audit-timeline" style="display:none;"></div>
        </div>
        <div class="ve-modal-footer"><button class="ve-btn ve-btn-ghost" onclick="veCloseModal('ve-modal-audit')">Close</button></div>
    </div>
</div>

<div class="pal-toast" id="pal-toast"><div class="pal-toast-dot" id="pal-toast-dot">✓</div><span id="pal-toast-msg"></span></div>

</div>{{-- end .pal-wrap --}}

<script>
// ══════════════════════════════════════════════════════════════════
// CORE STATE & UTILITIES
// ══════════════════════════════════════════════════════════════════
var palS = { balance:0, due:0, rate:0, principal:0, selectedAccount:null };
var palAccounts = [];
document.querySelectorAll('#pal-accounts-data span').forEach(function(el){
    palAccounts.push({
        id: el.dataset.id, label: el.dataset.label, member: el.dataset.member,
        balance: parseFloat(el.dataset.balance||0), due: parseFloat(el.dataset.due||0),
        rate: parseFloat(el.dataset.rate||0), principal: parseFloat(el.dataset.principal||0),
        collateral: el.dataset.collateral
    });
});

// ── Modal helpers ──
function palOpenModal(id)  { document.getElementById(id).classList.add('pal-open'); }
function palCloseModal(id) { document.getElementById(id).classList.remove('pal-open'); }
function palBgClose(e,id)  { if(e.target===e.currentTarget) palCloseModal(id); }
function veOpenModal(id)   { document.getElementById(id).classList.add('ve-open'); }
function veCloseModal(id)  { document.getElementById(id).classList.remove('ve-open'); }
function veBgClose(e,id)   { if(e.target===e.currentTarget) veCloseModal(id); }

// ── Scroll to Collections & Posting ──
function palScrollToCollections(){
    var el = document.getElementById('pal-posted-wrap');
    if(el) el.scrollIntoView({ behavior:'smooth', block:'start' });
}

// ── Toast ──
var palToastTimer;
function palToast(msg, type){
    type = type||'success';
    var t = document.getElementById('pal-toast');
    t.className = 'pal-toast pal-toast-'+type;
    document.getElementById('pal-toast-dot').textContent = type==='success'?'✓':'✕';
    document.getElementById('pal-toast-msg').textContent = msg;
    t.classList.add('pal-toast-show');
    clearTimeout(palToastTimer);
    palToastTimer = setTimeout(function(){ t.classList.remove('pal-toast-show'); }, 3500);
}

function palFmt(n){ return '₱'+parseFloat(n||0).toLocaleString('en-PH',{minimumFractionDigits:2,maximumFractionDigits:2}); }
function veFmt(n) { return palFmt(n); }

// ── Format date string (YYYY-MM-DD) → "Jan 01, 2026" ──
function palFmtDate(d){
    if(!d) return '—';
    var dt = new Date(d+'T00:00:00');
    if(isNaN(dt)) return d;
    return dt.toLocaleDateString('en-US',{month:'short',day:'2-digit',year:'numeric'});
}

// ══════════════════════════════════════════════════════════════════
// SEARCHABLE DROPDOWN
// ══════════════════════════════════════════════════════════════════
function palFilterAccounts(){
    var q = document.getElementById('pal-search-input').value.trim().toLowerCase();
    document.getElementById('pal-search-clear').style.display = q ? 'block' : 'none';
    palShowDropdown(q);
}
function palShowDropdown(q){
    q = (q===undefined) ? document.getElementById('pal-search-input').value.trim().toLowerCase() : q;
    var dd = document.getElementById('pal-dropdown');
    var filtered = palAccounts.filter(function(a){
        return a.id.toString().includes(q) || a.label.toLowerCase().includes(q)
            || a.member.toLowerCase().includes(q) || a.balance.toString().includes(q);
    });
    if(!filtered.length){
        dd.innerHTML = '<div class="pal-dropdown-empty">No matching loan accounts found.</div>';
    } else {
        dd.innerHTML = filtered.slice(0,20).map(function(a){
            var hi = function(s){
                if(!q) return s;
                return s.replace(new RegExp('('+q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')+')','gi'),
                    '<mark style="background:#fef08a;padding:0 1px;border-radius:2px;">$1</mark>');
            };
            return '<div class="pal-dropdown-item" onclick="palSelectAccount(\''+a.id+'\')">'
                +'<div class="pal-dropdown-item-main">'+hi(a.label)+' — '+hi(a.member)+'</div>'
                +'<div class="pal-dropdown-item-sub">'
                +'<span>Balance: <strong>'+palFmt(a.balance)+'</strong></span>'
                +'<span>Due/mo: '+palFmt(a.due)+'</span>'
                +(a.collateral==='Approved'?'<span class="pal-dropdown-item-badge">✓ Collateral OK</span>':'')
                +'</div></div>';
        }).join('');
    }
    dd.classList.add('pal-open');
}
function palSelectAccount(id){
    var acc = palAccounts.find(function(a){ return a.id==id; });
    if(!acc) return;
    palS.balance = acc.balance; palS.due = acc.due;
    palS.rate = acc.rate; palS.principal = acc.principal;
    palS.selectedAccount = acc;
    document.getElementById('pal-search-input').value = '';
    document.getElementById('pal-search-input').style.display = 'none';
    document.getElementById('pal-search-clear').style.display = 'none';
    document.getElementById('pal-dropdown').classList.remove('pal-open');
    document.getElementById('pal-selected-text').textContent =
        acc.label+' — '+acc.member+'   |   Balance: '+palFmt(acc.balance)+'   |   Due/mo: '+palFmt(acc.due);
    document.getElementById('pal-selected-chip').classList.add('pal-visible');
    document.getElementById('pal-balance').value = palFmt(acc.balance);
    document.getElementById('pal-due').value     = palFmt(acc.due);
    palCompute();
}
function palClearSearch(){
    palS.selectedAccount = null; palS.balance = 0; palS.due = 0; palS.rate = 0;
    document.getElementById('pal-search-input').value = '';
    document.getElementById('pal-search-input').style.display = '';
    document.getElementById('pal-search-clear').style.display = 'none';
    document.getElementById('pal-dropdown').classList.remove('pal-open');
    document.getElementById('pal-selected-chip').classList.remove('pal-visible');
    document.getElementById('pal-balance').value = '';
    document.getElementById('pal-due').value = '';
    document.getElementById('pal-result').style.display = 'none';
    document.getElementById('pal-search-input').focus();
}
document.addEventListener('click', function(e){
    var wrap = document.getElementById('pal-search-wrap');
    if(wrap && !wrap.contains(e.target)) document.getElementById('pal-dropdown').classList.remove('pal-open');
});

// ══════════════════════════════════════════════════════════════════
// ALLOCATION COMPUTE (simulator — preview only, nothing is saved)
// Interest = outstanding_balance × (annual_rate / 100) / 12
// Carry forward = amount paid - (interest + principal + penalty due)
//   i.e. the OVERPAYMENT amount left over after all obligations are met
// ══════════════════════════════════════════════════════════════════
var palAllocationOrder = ['interest','principal','penalty'];

function palCompute(){
    var paid    = parseFloat(document.getElementById('pal-amount').value||0);
    var due     = palS.due;
    var bal     = palS.balance;
    var rate    = palS.rate;  // annual interest rate %
    var pen     = parseFloat(document.getElementById('pal-penalty').value||0);

    if(!paid || !due || !bal){
        document.getElementById('pal-result').style.display = 'none';
        return;
    }

    // ── Correct interest formula: monthly interest on current outstanding balance ──
    var monthlyInterest = Math.round(bal * (rate / 100) / 12 * 100) / 100;

    // Remaining payment after each allocation step
    var rem = paid;
    var allocated = { interest:0, principal:0, penalty:0 };

    palAllocationOrder.forEach(function(key){
        if(key === 'interest'){
            allocated.interest  = Math.min(rem, monthlyInterest);
            rem = Math.max(0, rem - allocated.interest);
        } else if(key === 'principal'){
            // Apply remaining to principal (up to current balance)
            allocated.principal = Math.min(rem, bal);
            rem = Math.max(0, rem - allocated.principal);
        } else if(key === 'penalty'){
            allocated.penalty   = Math.min(rem, pen);
            rem = Math.max(0, rem - allocated.penalty);
        }
    });

    // Carry forward = leftover after all obligations are settled (overpayment)
    var carry   = Math.round(rem * 100) / 100;
    var newBal  = Math.max(0, Math.round((bal - allocated.principal) * 100) / 100);

    // Classify payment type
    var type = 'Partial';
    if(paid >= due && carry > 0) type = 'Overpayment';
    else if(paid >= due)         type = 'Advance';
    else                         type = 'Partial';

    // ── Update result UI ──
    var bm = {
        'Partial':     'background:#1d4ed8;color:#fff',
        'Advance':     'background:#047857;color:#fff',
        'Overpayment': 'background:#b45309;color:#fff'
    };
    var tb = document.getElementById('pal-type-badge');
    tb.textContent = type;
    tb.style.cssText = 'font-size:.68rem;font-weight:700;padding:2px 10px;border-radius:999px;' + (bm[type]||'');

    document.getElementById('pal-r-due').textContent       = palFmt(due);
    document.getElementById('pal-r-paid').textContent      = palFmt(paid);
    document.getElementById('pal-r-carry').textContent     = palFmt(carry);
    document.getElementById('pal-r-interest').textContent  = palFmt(allocated.interest);
    document.getElementById('pal-r-principal').textContent = palFmt(allocated.principal);

    var rb = document.getElementById('pal-r-balance');
    rb.textContent = palFmt(newBal);
    rb.className = 'pal-result-item-value ' + (newBal <= 0 ? 'green' : 'amber');

    // Flow steps
    function sf(id, vid, val, cls){
        document.getElementById(id).className  = 'pal-flow-step ' + (val>0 ? cls : '');
        document.getElementById(vid).textContent = palFmt(val);
        document.getElementById(vid).className  = 'pal-flow-step-value';
    }
    sf('pal-flow-interest',  'pal-flow-interest-val',  allocated.interest,  'pal-flow-active');
    sf('pal-flow-principal', 'pal-flow-principal-val', allocated.principal, 'pal-flow-active');
    sf('pal-flow-penalty',   'pal-flow-penalty-val',   allocated.penalty,   'pal-flow-active');
    sf('pal-flow-carry',     'pal-flow-carry-val',     carry,               'pal-flow-partial');

    var cn = document.getElementById('pal-carry-note');
    if(carry > 0){
        cn.style.display = 'flex';
        document.getElementById('pal-carry-note-text').textContent =
            palFmt(carry)+' overpayment will be carried forward and credited to the next payment due date automatically.';
    } else {
        cn.style.display = 'none';
    }

    document.getElementById('pal-result').style.display = 'block';
}

// ══════════════════════════════════════════════════════════════════
// CLIENT-SIDE FILTER
// ══════════════════════════════════════════════════════════════════
function palFilterTable(q){
    q = q.trim().toLowerCase();
    document.querySelectorAll('#pal-posted-tbody .pal-db-row').forEach(function(row){
        var m = row.dataset.member||'', l = row.dataset.label||'';
        row.style.display = (!q || m.includes(q) || l.includes(q)) ? '' : 'none';
    });
}

// ══════════════════════════════════════════════════════════════════
// SORT
// ══════════════════════════════════════════════════════════════════
var palSortState = { col:-1, asc:true };
var palPillMap = { 2:'pal-sp-member', 3:'pal-sp-type', 4:'pal-sp-date', 7:'pal-sp-status' };

function palSortSyncPills(activeCol, asc){
    Object.values(palPillMap).forEach(function(pid){
        var pill = document.getElementById(pid);
        if(!pill) return;
        pill.classList.remove('sp-asc','sp-desc');
        pill.querySelector('.sp-arrow').textContent = '↕';
    });
    var pid = palPillMap[activeCol];
    if(pid){
        var pill = document.getElementById(pid);
        if(pill){ pill.classList.add(asc?'sp-asc':'sp-desc'); pill.querySelector('.sp-arrow').textContent = asc?'↑':'↓'; }
    }
    var reset = document.getElementById('pal-sort-reset');
    if(reset) reset.classList.toggle('sr-show', activeCol !== -1);
}

function palSortTable(col){
    var tbody = document.getElementById('pal-posted-tbody');
    var rows = [...tbody.querySelectorAll('tr.pal-db-row')];
    if(!rows.length) return;
    var asc = (palSortState.col === col) ? !palSortState.asc : true;
    palSortState = { col:col, asc:asc };
    palSortSyncPills(col, asc);
    rows.sort(function(a,b){
        var ac = a.cells[col], bc = b.cells[col];
        if(!ac||!bc) return 0;
        var av = ac.textContent.trim().replace(/[₱,]/g,'');
        var bv = bc.textContent.trim().replace(/[₱,]/g,'');
        var an = parseFloat(av), bn = parseFloat(bv);
        if(!isNaN(an)&&!isNaN(bn)) return asc?(an-bn):(bn-an);
        var ad = Date.parse(av), bd = Date.parse(bv);
        if(!isNaN(ad)&&!isNaN(bd)) return asc?(ad-bd):(bd-ad);
        return asc ? av.localeCompare(bv) : bv.localeCompare(av);
    });
    rows.forEach(function(r){ tbody.appendChild(r); });
}

function palSortReset(){
    palSortState = { col:-1, asc:true };
    palSortSyncPills(-1, true);
    var tbody = document.getElementById('pal-posted-tbody');
    var rows = [...tbody.querySelectorAll('tr.pal-db-row')];
    rows.sort(function(a,b){ return parseInt(b.dataset.id||0) - parseInt(a.dataset.id||0); });
    rows.forEach(function(r){ tbody.appendChild(r); });
}

// ══════════════════════════════════════════════════════════════════
// LIVE ROW UPDATE HELPERS
// ══════════════════════════════════════════════════════════════════
function palFindRow(id){
    return document.querySelector('#pal-posted-tbody tr.pal-db-row[data-id="'+id+'"]')
        || (function(){
            var rows = document.querySelectorAll('#pal-posted-tbody tr.pal-db-row');
            for(var i=0;i<rows.length;i++){
                if(rows[i].cells[0]&&rows[i].cells[0].textContent.trim().replace(/\D/g,'')==String(id)) return rows[i];
            }
            return null;
        })();
}

function palBuildActions(id, member, amount, date, isPosted){
    var m = String(member||'').replace(/'/g,"\\'");
    var d = String(date||'').replace(/'/g,"\\'");
    var amt = parseFloat(amount||0);
    var view = '<button class="pal-action-btn pal-action-view" onclick="veOpenView('+id+')">'
        +'<svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>View</button>';
    var log = '<button class="pal-action-btn pal-action-audit" onclick="veOpenAudit('+id+')">'
        +'<svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>Log</button>';
    var edit = '<button class="pal-action-btn pal-action-edit" onclick="veOpenEdit('+id+')">'
        +'<svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>Edit</button>';
    var vd = '<button class="pal-action-btn pal-action-void" onclick="veOpenVoid('+id+',\''+m+'\','+amt+',\''+d+'\')">'
        +'<svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Void</button>';
    var voidedLabel = '<span style="font-size:.7rem;color:#9ca3af;font-style:italic;padding:4px 8px;">Voided</span>';
    return '<td><div style="display:flex;gap:5px;align-items:center;flex-wrap:nowrap;">'+view+log+(isPosted?edit+vd:voidedLabel)+'</div></td>';
}

function palUpdateRowAfterVoid(id){
    var row = palFindRow(id); if(!row) return;
    row.dataset.status = 'Void';
    if(row.cells[7]) row.cells[7].innerHTML = '<span class="pal-type-badge pal-status-void">Void</span>';
    var member = row.cells[2] ? row.cells[2].textContent.trim() : '';
    var amount = row.cells[5] ? parseFloat(row.cells[5].textContent.replace(/[₱,]/g,'')) : 0;
    var date   = row.cells[4] ? row.cells[4].textContent.trim() : '';
    row.cells[row.cells.length-1].outerHTML = palBuildActions(id, member, amount, date, false);
    row.style.transition = 'background .3s';
    row.style.background = '#fee2e2';
    setTimeout(function(){ row.style.background = ''; }, 2500);
}

function palUpdateRowAfterEdit(id, newAmount, newBal, newDate, newType){
    var row = palFindRow(id); if(!row) return;
    if(row.cells[5]) row.cells[5].textContent = palFmt(newAmount);
    if(row.cells[6]){
        var badge = newBal<=0 ? ' <span style="font-size:.6rem;background:#059669;color:#fff;padding:1px 6px;border-radius:999px;font-weight:700;">PAID OFF</span>' : '';
        row.cells[6].innerHTML = '<span style="font-weight:800;color:'+(newBal<=0?'#059669':'#111827')+';">'+palFmt(newBal)+badge+'</span>';
    }
    if(row.cells[4] && newDate) row.cells[4].textContent = palFmtDate(newDate);
    if(row.cells[3] && newType){
        var tc = {'Partial':'pal-type-partial','Advance':'pal-type-advance','Overpayment':'pal-type-overpayment'}[newType]||'';
        row.cells[3].innerHTML = '<span class="pal-type-badge '+tc+'">'+newType+'</span>';
    }
    row.style.transition = 'background .3s';
    row.style.background = '#d1fae5';
    setTimeout(function(){ row.style.background = ''; }, 2500);
}

// ══════════════════════════════════════════════════════════════════
// VIEW MODAL
// ══════════════════════════════════════════════════════════════════
function veOpenView(id){
    document.getElementById('ve-view-loading').style.display = 'block';
    document.getElementById('ve-view-content').style.display = 'none';
    document.getElementById('ve-view-id-label').textContent  = '— #'+id;
    veOpenModal('ve-modal-view');
    @this.call('getPaymentData', id).then(function(p){
        document.getElementById('ve-view-loading').style.display = 'none';
        if(!p){ veCloseModal('ve-modal-view'); palToast('Payment not found.','danger'); return; }
        var typeColors = {'Partial':'#1d4ed8','Advance':'#047857','Overpayment':'#b45309'};
        var typeBg     = {'Partial':'#1d4ed8','Advance':'#047857','Overpayment':'#b45309'};
        var sc = p.status==='Posted' ? '#059669' : '#dc2626';
        document.getElementById('ve-view-chips').innerHTML =
            '<span style="font-size:.72rem;background:#374151;color:#fff;padding:3px 10px;border-radius:999px;font-weight:700;">#'+p.loan_payment_id+'</span>'
            +(p.payment_type ? '<span style="font-size:.72rem;background:'+(typeBg[p.payment_type]||'#374151')+';color:#fff;padding:3px 10px;border-radius:999px;font-weight:700;">'+p.payment_type+'</span>' : '')
            +'<span style="font-size:.72rem;background:'+sc+';color:#fff;padding:3px 10px;border-radius:999px;font-weight:700;">'+p.status+'</span>';
        document.getElementById('ve-v-account').textContent   = p.loan_account_label;
        document.getElementById('ve-v-member').textContent    = p.member_name;
        document.getElementById('ve-v-date').textContent      = p.payment_date_fmt || palFmtDate(p.payment_date);
        document.getElementById('ve-v-amount').textContent    = veFmt(p.amount_paid);
        document.getElementById('ve-v-due').textContent       = veFmt(p.amount_due);
        document.getElementById('ve-v-type').textContent      = p.payment_type||'—';
        document.getElementById('ve-v-interest').textContent  = veFmt(p.interest_paid);
        document.getElementById('ve-v-principal').textContent = veFmt(p.principal_paid);
        document.getElementById('ve-v-penalty').textContent   = veFmt(p.penalty_paid);
        document.getElementById('ve-v-carry').textContent     = veFmt(p.carry_forward);
        var balEl = document.getElementById('ve-v-balance');
        balEl.textContent = veFmt(p.remaining_balance);
        balEl.style.color = parseFloat(p.remaining_balance||0)<=0 ? '#059669' : '#374151';
        document.getElementById('ve-v-status').innerHTML = '<span style="display:inline-flex;padding:2px 9px;border-radius:999px;font-size:.75rem;font-weight:700;background:'+sc+';color:#fff;">'+p.status+'</span>';
        document.getElementById('ve-v-postedby').textContent  = p.posted_by_name;
        document.getElementById('ve-v-createdat').textContent = p.created_at;
        var rw = document.getElementById('ve-v-remarks-wrap');
        if(p.remarks){ document.getElementById('ve-v-remarks').textContent = p.remarks; rw.style.display = 'block'; }
        else { rw.style.display = 'none'; }
        document.getElementById('ve-view-content').style.display = 'block';
    });
}

// ══════════════════════════════════════════════════════════════════
// EDIT MODAL
// ══════════════════════════════════════════════════════════════════
function veOpenEdit(id){
    document.getElementById('ve-edit-loading').style.display  = 'block';
    document.getElementById('ve-edit-content').style.display  = 'none';
    document.getElementById('ve-edit-warn').style.display     = 'none';
    document.getElementById('ve-edit-submit').disabled        = true;
    document.getElementById('ve-edit-id-label').textContent   = '— Payment #'+id;
    document.getElementById('ve-edit-reason').value           = '';
    veCharCount('ve-edit-reason','ve-edit-reason-count','ve-edit-reason-bar',10);
    veOpenModal('ve-modal-edit');
    @this.call('getPaymentData', id).then(function(p){
        document.getElementById('ve-edit-loading').style.display = 'none';
        if(!p){ veCloseModal('ve-modal-edit'); palToast('Payment not found.','danger'); return; }
        document.getElementById('ve-s-account').textContent  = p.loan_account_label;
        document.getElementById('ve-s-member').textContent   = p.member_name;
        document.getElementById('ve-s-postedby').textContent = p.posted_by_name;
        document.getElementById('ve-s-amount').textContent   = veFmt(p.amount_paid);
        document.getElementById('ve-s-type').textContent     = p.payment_type||'—';
        document.getElementById('ve-s-created').textContent  = p.created_at||'—';
        document.getElementById('ve-edit-payment-id').value  = p.loan_payment_id;
        document.getElementById('ve-edit-date').value        = p.payment_date||'';
        document.getElementById('ve-edit-type').value        = p.payment_type||'Partial';
        document.getElementById('ve-edit-amount').value      = p.amount_paid||0;
        document.getElementById('ve-edit-due').value         = p.amount_due||0;
        document.getElementById('ve-edit-interest').value    = p.interest_paid||0;
        document.getElementById('ve-edit-principal').value   = p.principal_paid||0;
        document.getElementById('ve-edit-penalty').value     = p.penalty_paid||0;
        document.getElementById('ve-edit-carry').value       = p.carry_forward||0;
        document.getElementById('ve-edit-remaining').value   = p.remaining_balance||0;
        document.getElementById('ve-edit-remarks').value     = p.remarks||'';
        document.getElementById('ve-edit-content').style.display = 'block';
        document.getElementById('ve-edit-submit').disabled   = false;
    });
}

function veSubmitEdit(){
    var reason = document.getElementById('ve-edit-reason').value.trim();
    if(reason.length < 10){ palToast('Edit reason must be at least 10 characters.','danger'); return; }
    var btn = document.getElementById('ve-edit-submit');
    btn.disabled = true; btn.textContent = 'Saving…';
    document.getElementById('ve-edit-warn').style.display = 'none';
    @this.call('editPayment',{
        loan_payment_id:   parseInt(document.getElementById('ve-edit-payment-id').value),
        payment_date:      document.getElementById('ve-edit-date').value,
        payment_type:      document.getElementById('ve-edit-type').value,
        amount_paid:       parseFloat(document.getElementById('ve-edit-amount').value||0),
        amount_due:        parseFloat(document.getElementById('ve-edit-due').value||0),
        interest_paid:     parseFloat(document.getElementById('ve-edit-interest').value||0),
        principal_paid:    parseFloat(document.getElementById('ve-edit-principal').value||0),
        penalty_paid:      parseFloat(document.getElementById('ve-edit-penalty').value||0),
        carry_forward:     parseFloat(document.getElementById('ve-edit-carry').value||0),
        remaining_balance: parseFloat(document.getElementById('ve-edit-remaining').value||0),
        remarks:           document.getElementById('ve-edit-remarks').value,
        edit_reason:       reason,
    }).then(function(r){
        if(r && r.success){
            veCloseModal('ve-modal-edit');
            palToast(r.message||'Payment updated successfully.','success');
            palUpdateRowAfterEdit(
                parseInt(document.getElementById('ve-edit-payment-id').value),
                parseFloat(document.getElementById('ve-edit-amount').value||0),
                parseFloat(document.getElementById('ve-edit-remaining').value||0),
                document.getElementById('ve-edit-date').value,
                document.getElementById('ve-edit-type').value
            );
        } else {
            var w = document.getElementById('ve-edit-warn');
            document.getElementById('ve-edit-warn-text').textContent = r&&r.message ? r.message : 'Unexpected error.';
            w.style.display = 'flex';
        }
        btn.disabled = false;
        btn.innerHTML = '<svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Save Changes';
    });
}

// ══════════════════════════════════════════════════════════════════
// VOID MODAL
// ══════════════════════════════════════════════════════════════════
function veOpenVoid(id, member, amount, date){
    document.getElementById('ve-void-payment-id').value = id;
    document.getElementById('ve-void-subtitle').textContent = 'Payment #'+id+' — '+member;
    document.getElementById('ve-void-reason').value = '';
    veCharCount('ve-void-reason','ve-void-reason-count','ve-void-reason-bar',10);
    document.getElementById('ve-void-info').innerHTML =
        '<span style="font-size:.72rem;background:#dc2626;color:#fff;padding:3px 10px;border-radius:999px;font-weight:700;">#'+id+'</span>'
        +'<span style="font-size:.72rem;background:#374151;color:#fff;padding:3px 10px;border-radius:999px;font-weight:600;">'+member+'</span>'
        +'<span style="font-size:.72rem;background:#374151;color:#fff;padding:3px 10px;border-radius:999px;font-weight:600;">'+veFmt(amount)+'</span>'
        +'<span style="font-size:.72rem;background:#374151;color:#fff;padding:3px 10px;border-radius:999px;font-weight:600;">'+date+'</span>';
    veOpenModal('ve-modal-void');
}

function veSubmitVoid(){
    var reason = document.getElementById('ve-void-reason').value.trim();
    if(reason.length < 10){ palToast('Void reason must be at least 10 characters.','danger'); return; }
    var btn = document.getElementById('ve-void-submit');
    btn.disabled = true; btn.textContent = 'Processing…';
    @this.call('voidPaymentWithReason',{
        loan_payment_id: parseInt(document.getElementById('ve-void-payment-id').value),
        void_reason:     reason,
    }).then(function(r){
        if(r && r.success){
            veCloseModal('ve-modal-void');
            palToast(r.message||'Payment voided.','success');
            palUpdateRowAfterVoid(parseInt(document.getElementById('ve-void-payment-id').value));
        } else {
            palToast(r&&r.message ? r.message : 'Error voiding payment.','danger');
        }
        btn.disabled = false;
        btn.innerHTML = '<svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Confirm Void';
    });
}

// ══════════════════════════════════════════════════════════════════
// AUDIT LOG MODAL — renders changes diff when present
// ══════════════════════════════════════════════════════════════════
function veOpenAudit(id){
    document.getElementById('ve-audit-id-label').textContent   = '— Payment #'+id;
    document.getElementById('ve-audit-loading').style.display  = 'block';
    document.getElementById('ve-audit-timeline').style.display = 'none';
    document.getElementById('ve-audit-timeline').innerHTML     = '';
    veOpenModal('ve-modal-audit');
    @this.call('getAuditLog', id).then(function(logs){
        document.getElementById('ve-audit-loading').style.display = 'none';
        var tl = document.getElementById('ve-audit-timeline');
        if(!logs || !logs.length){
            tl.innerHTML = '<div class="ve-audit-empty">📭 No audit log entries found for this payment.</div>';
            tl.style.display = 'block';
            return;
        }
        var iconMap  = { created:'✓', edit:'✏', void:'✕' };
        var clsMap   = { created:'ve-tl-dot-created', edit:'ve-tl-dot-edit', void:'ve-tl-dot-void' };
        var labelMap = { created:'Payment Posted', edit:'Payment Edited', void:'Payment Voided' };

        tl.innerHTML = logs.map(function(log, i){
            var isLast = (i === logs.length-1);

            // Build changes diff rows if present
            var changesHtml = '';
            if(log.changes && log.changes.length){
                changesHtml = '<div class="ve-tl-changes"><div style="font-size:.63rem;font-weight:700;color:#9ca3af;letter-spacing:.08em;text-transform:uppercase;margin-bottom:.35rem;">Changes</div>'
                    + log.changes.map(function(c){
                        return '<div class="ve-tl-change-row">'
                            +'<span class="ve-tl-change-field">'+c.field+'</span>'
                            +'<span class="ve-tl-change-from">'+(c.before!==null&&c.before!==''?c.before:'—')+'</span>'
                            +'<span class="ve-tl-change-arrow"> → </span>'
                            +'<span class="ve-tl-change-to">'+(c.after!==null&&c.after!==''?c.after:'—')+'</span>'
                            +'</div>';
                    }).join('')
                    +'</div>';
            }

            return '<div class="ve-tl-item">'
                +'<div class="ve-tl-spine">'
                    +'<div class="ve-tl-dot '+(clsMap[log.action]||'ve-tl-dot-created')+'">'+(iconMap[log.action]||'•')+'</div>'
                    +(!isLast ? '<div class="ve-tl-line"></div>' : '')
                +'</div>'
                +'<div class="ve-tl-body">'
                    +'<div class="ve-tl-card">'
                        +'<div class="ve-tl-action">'+(labelMap[log.action]||log.action)+'</div>'
                        +'<div class="ve-tl-meta">By <strong>'+(log.actor||'—')+'</strong> · '+(log.timestamp||'—')+'</div>'
                        +(log.note ? '<div class="ve-tl-note">'+log.note+'</div>' : '')
                        +changesHtml
                    +'</div>'
                +'</div>'
            +'</div>';
        }).join('');

        tl.style.display = 'flex';
    });
}

// ══════════════════════════════════════════════════════════════════
// CHAR COUNT HELPER
// ══════════════════════════════════════════════════════════════════
function veCharCount(fieldId, countId, barId, min){
    var val = document.getElementById(fieldId).value, len = val.length;
    var pct = Math.min(100, Math.round(len/min*100));
    var bar = document.getElementById(barId), cnt = document.getElementById(countId);
    if(bar){ bar.style.width = pct+'%'; bar.style.background = len>=min ? '#10b981' : '#f59e0b'; }
    if(cnt){ cnt.textContent = len+' / '+min+' min'; cnt.className = 've-char-count'+(len>=min?' ve-ok':''); }
}

// ══════════════════════════════════════════════════════════════════
// CONFIGURE MODAL — DRAG REORDER + SAVE
// ══════════════════════════════════════════════════════════════════
var palDragging = null;
document.querySelectorAll('#pal-cfg-list .pal-cfg-row').forEach(function(row){
    row.addEventListener('dragstart', function(){ palDragging = this; this.style.opacity='.4'; });
    row.addEventListener('dragend',   function(){ this.style.opacity='1'; palRenumberCfg(); });
    row.addEventListener('dragover',  function(e){ e.preventDefault(); this.style.borderColor='#10b981'; });
    row.addEventListener('dragleave', function(){ this.style.borderColor=''; });
    row.addEventListener('drop', function(e){
        e.preventDefault(); this.style.borderColor='';
        if(palDragging && palDragging!==this){
            var list = document.getElementById('pal-cfg-list');
            var items = [...list.querySelectorAll('.pal-cfg-row')];
            var fi = items.indexOf(palDragging), ti = items.indexOf(this);
            if(fi < ti) list.insertBefore(palDragging, this.nextSibling);
            else        list.insertBefore(palDragging, this);
        }
    });
});

function palRenumberCfg(){
    var colors = ['#1e3a5f','#6366f1','#8b5cf6'];
    document.querySelectorAll('#pal-cfg-list .pal-cfg-row').forEach(function(row, i){
        row.querySelector('.pal-cfg-row-num').textContent = i+1;
        row.querySelector('.pal-cfg-row-num').style.background = colors[i];
    });
}

function palSaveConfig(){
    var partial = document.getElementById('pal-toggle-partial').checked;
    var advance = document.getElementById('pal-toggle-advance').checked;
    var carry   = document.getElementById('pal-toggle-carry').checked;
    document.getElementById('pal-stat-mode').textContent = (partial&&advance) ? 'Auto' : 'Manual';
    var rows   = [...document.querySelectorAll('#pal-cfg-list .pal-cfg-row')];
    var colors = ['#1e3a5f','#6366f1','#8b5cf6'];
    var steps  = document.querySelectorAll('.pal-priority-steps .pal-step');
    var subMap = { interest:'Applied first', principal:'Loan balance', penalty:'Applied last' };
    rows.forEach(function(row, i){
        var key  = row.dataset.key;
        var name = row.querySelector('.pal-cfg-row-name').textContent;
        if(steps[i]){
            var numEl = steps[i].querySelector('.pal-step-num');
            numEl.textContent = i+1;
            numEl.style.background = colors[i]; numEl.style.color = '#fff';
            steps[i].querySelector('.pal-step-name').textContent = name;
            steps[i].querySelector('.pal-step-sub').textContent  = subMap[key]||'';
        }
    });
    palAllocationOrder = rows.map(function(r){ return r.dataset.key; });
    var note = document.querySelector('.pal-override-note');
    if(note){
        var opts = [];
        if(carry)   opts.push('Carry-Forward');
        if(partial) opts.push('Partial');
        if(advance) opts.push('Advance');
        note.textContent = '✓ Active: '+(opts.length?opts.join(', '):'None')+(partial&&advance?' — Auto mode':' — Manual mode');
    }
    palCloseModal('pal-modal-configure');
    palToast('Configuration saved — priority & behavior updated.','success');
}
</script>

</x-filament-panels::page>