<x-filament-panels::page>

<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;600;700&family=Oxanium:wght@400;600&family=Orbitron:wght@400;600&family=Syne:wght@400;700&family=Exo+2:wght@400;600&family=Bebas+Neue&family=Outfit:wght@400;600&display=swap" rel="stylesheet">

<style>
    /* ── Layout ─────────────────────────────────────────── */
    .ss-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 360px;
        gap: 1.5rem;
        align-items: start;
    }
    @media (max-width: 1100px) {
        .ss-layout { grid-template-columns: 1fr; }
        .ss-sticky  { position: static !important; }
    }
    .ss-sticky {
        position: sticky;
        top: 1.5rem;
    }

    /* ── Cards ──────────────────────────────────────────── */
    .ss-card {
        border-radius: 12px;
        border: 1px solid rgba(0,0,0,0.07);
        background: white;
        overflow: hidden;
        margin-bottom: 1rem;
    }
    .dark .ss-card {
        background: rgb(30 41 59 / 0.5);
        border-color: rgba(255,255,255,0.07);
    }
    .ss-card-head {
        padding: 0.875rem 1.125rem;
        border-bottom: 1px solid rgba(0,0,0,0.06);
        display: flex;
        align-items: center;
        gap: 0.625rem;
        background: rgba(0,0,0,0.015);
    }
    .dark .ss-card-head {
        border-bottom-color: rgba(255,255,255,0.05);
        background: rgba(255,255,255,0.02);
    }
    .ss-card-head-icon {
        width: 1.75rem; height: 1.75rem;
        border-radius: 7px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .ss-card-head-title {
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.07em;
        text-transform: uppercase;
        color: #6b7280;
    }
    .dark .ss-card-head-title { color: #94a3b8; }
    .ss-card-body { padding: 1.125rem; }

    /* ── Status pill ────────────────────────────────────── */
    .ss-status {
        display: inline-flex; align-items: center; gap: 0.375rem;
        font-size: 0.68rem; font-weight: 600; letter-spacing: 0.05em;
        padding: 0.25rem 0.7rem;
        border-radius: 9999px;
        border: 1px solid;
        transition: all 0.25s ease;
        white-space: nowrap;
    }
    .ss-status-dot {
        width: 6px; height: 6px; border-radius: 9999px;
        flex-shrink: 0;
        transition: background 0.25s;
    }
    .ss-status.ready  { color: #9ca3af; border-color: #e5e7eb; background: transparent; }
    .ss-status.ready .ss-status-dot  { background: #d1d5db; }
    .ss-status.saving { color: #d97706; border-color: #fcd34d; background: #fffbeb; }
    .ss-status.saving .ss-status-dot { background: #f59e0b; animation: ss-pulse 1s infinite; }
    .ss-status.saved  { color: #059669; border-color: #6ee7b7; background: #ecfdf5; }
    .ss-status.saved .ss-status-dot  { background: #10b981; }
    .dark .ss-status.ready  { border-color: rgba(255,255,255,0.1); }
    .dark .ss-status.saving { background: #451a03; border-color: #78350f; }
    .dark .ss-status.saved  { background: #022c22; border-color: #065f46; }
    @keyframes ss-pulse { 0%,100% { opacity:1; } 50% { opacity:0.3; } }

    /* ── Navbar mock ────────────────────────────────────── */
    .ss-nav {
        border-radius: 9px;
        padding: 0.55rem 1rem;
        display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;
    }
    .ss-nav-light {
        background: #fff;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 4px rgba(0,0,0,0.05);
    }
    .ss-nav-dark  {
        background: #0f172a;
        border: 1px solid #1e293b;
    }
    .ss-nav-brand { display:flex; align-items:center; gap:0.4rem; min-width:0; }
    .ss-nav-brand img { height: 1.4rem; width: auto; flex-shrink: 0; }
    .ss-nav-right { display:flex; align-items:center; gap:0.6rem; flex-shrink:0; }
    .ss-nav-search {
        border-radius: 5px; padding: 0.2rem 0.55rem;
        display: flex; align-items: center; gap: 0.25rem;
        font-size: 0.62rem;
    }
    .ss-nav-avatar {
        width: 1.5rem; height: 1.5rem; border-radius: 9999px;
        display:flex; align-items:center; justify-content:center;
        font-size: 0.55rem; font-weight: 700; color: white;
        flex-shrink: 0;
    }

    /* ── Preview label ──────────────────────────────────── */
    .ss-plabel {
        font-size: 0.62rem; font-weight: 700;
        letter-spacing: 0.1em; text-transform: uppercase;
        color: #9ca3af; margin-bottom: 0.4rem; display: block;
    }

    /* ── Color chips ────────────────────────────────────── */
    .ss-chips { display:flex; flex-wrap:wrap; gap:0.4rem; align-items:center; }
    .ss-chip-solid   { padding:0.25rem 0.65rem; border-radius:6px; font-size:0.68rem; font-weight:600; color:white; }
    .ss-chip-outline { padding:0.2rem 0.6rem; border-radius:6px; font-size:0.68rem; font-weight:600; border:1.5px solid; }
    .ss-chip-badge   { padding:0.18rem 0.55rem; border-radius:9999px; font-size:0.62rem; font-weight:700; }
    .ss-chip-nav     { padding:0.2rem 0.6rem; border-radius:0 5px 5px 0; font-size:0.68rem; font-weight:600; border-left:2.5px solid; }

    /* ── Font card ──────────────────────────────────────── */
    .ss-font-box {
        border-radius: 8px;
        border: 1px solid rgba(0,0,0,0.07);
        padding: 0.875rem 1rem;
        background: rgba(0,0,0,0.015);
    }
    .dark .ss-font-box { border-color: rgba(255,255,255,0.06); background: rgba(255,255,255,0.02); }

    /* ── Favicon tab mock ───────────────────────────────── */
    .ss-tab {
        display: inline-flex; align-items: center; gap: 0.3rem;
        background: #e9ecef; border: 1px solid #dee2e6;
        border-bottom: none; border-radius: 7px 7px 0 0;
        padding: 0.3rem 0.75rem; font-size: 0.62rem; color: #495057;
        font-weight: 500; max-width: 160px;
    }

    /* ── Section divider ────────────────────────────────── */
    .ss-divider {
        display: flex; align-items: center; gap: 0.75rem;
        margin: 1.25rem 0 1rem;
    }
    .ss-divider-line { flex:1; height:1px; background: rgba(0,0,0,0.07); }
    .dark .ss-divider-line { background: rgba(255,255,255,0.06); }
    .ss-divider-text {
        font-size: 0.65rem; font-weight: 700; letter-spacing: 0.1em;
        text-transform: uppercase; color: #9ca3af; white-space: nowrap;
    }

    /* ── Save button ────────────────────────────────────── */
    .ss-save-btn {
        display: inline-flex; align-items: center; gap: 0.5rem;
        padding: 0.55rem 1.375rem;
        border-radius: 8px; border: none; cursor: pointer;
        font-size: 0.78rem; font-weight: 700; letter-spacing: 0.05em;
        color: white; transition: filter 0.15s, transform 0.1s;
    }
    .ss-save-btn:hover  { filter: brightness(1.08); }
    .ss-save-btn:active { transform: scale(0.98); }
    .ss-save-btn:disabled { opacity: 0.6; cursor: not-allowed; }
</style>

<div class="ss-layout">

    {{-- ════════════════════════════════════════
         COLUMN 1 — FORM
    ════════════════════════════════════════ --}}
    <div>

        {{-- Page header --}}
        <div style="
            border-radius: 14px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.25rem;
            background: linear-gradient(135deg, {{ $this->primary_color }}1a 0%, {{ $this->primary_color }}08 100%);
            border: 1px solid {{ $this->primary_color }}30;
            display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;
        ">
            <div>
                <div style="font-size: 1rem; font-weight: 700; letter-spacing: 0.01em;">
                    Branding &amp; Appearance
                </div>
                <div style="font-size: 0.75rem; color: #6b7280; margin-top: 0.2rem;">
                    Adjust settings below. Click <strong>Save Changes</strong> to apply them to the system.
                </div>
            </div>
            <div class="ss-status ready" id="ss-status">
                <div class="ss-status-dot"></div>
                <span id="ss-status-text">Unsaved changes</span>
            </div>
        </div>

        {{-- Application Name --}}


        {{-- The actual Filament form --}}
        <div style="margin-top: -0.75rem;">
            {{ $this->form }}
        </div>

        <div style="display:flex; justify-content:flex-end; align-items:center; gap:0.75rem; margin-top:1.25rem;">
            <button
                wire:click="save"
                wire:loading.attr="disabled"
                wire:target="save"
                class="ss-save-btn"
                style="background: {{ $this->primary_color }};"
            >
                <svg wire:loading.remove wire:target="save" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M5 13l4 4L19 7"/>
                </svg>
                <svg wire:loading wire:target="save" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
                </svg>
                <span wire:loading.remove wire:target="save">Save Changes</span>
                <span wire:loading wire:target="save">Saving…</span>
            </button>
        </div>

    </div>

    {{-- ════════════════════════════════════════
         COLUMN 2 — LIVE PREVIEW (sticky)
    ════════════════════════════════════════ --}}
    <div class="ss-sticky">

        <div style="margin-bottom:0.75rem; display:flex; align-items:center; justify-content:space-between;">
            <span style="font-size:0.65rem; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#9ca3af;">Live Preview</span>
            <span style="font-size:0.65rem; color:#9ca3af; opacity:0.6;">Updates instantly</span>
        </div>

        {{-- Navbar Light --}}
        <div class="ss-card">
            <div class="ss-card-head">
                <span class="ss-card-head-title">Navbar · Light</span>
            </div>
            <div class="ss-card-body" style="padding: 0.75rem;">
                <div class="ss-nav ss-nav-light">
                    <div class="ss-nav-brand">
                        @if($this->getLogoUrl())
                            <img src="{{ $this->getLogoUrl() }}" alt="logo">
                        @else
                            <div style="width:1.4rem;height:1.4rem;background:#e5e7eb;border-radius:3px;flex-shrink:0;"></div>
                        @endif
                        <span style="font-family:'{{ $this->font }}',sans-serif;font-size:0.85rem;font-weight:600;letter-spacing:0.04em;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:130px;">
                            {{ $this->app_name ?: 'App Name' }}
                        </span>
                    </div>
                    <div class="ss-nav-right">
                        <div class="ss-nav-search" style="background:#f3f4f6;color:#9ca3af;">
                            <svg width="9" height="9" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                            Search
                        </div>
                        <span style="font-family:'{{ $this->font }}',sans-serif;font-size:{{ $this->topbar_font_size }}px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#374151;white-space:nowrap;">
                            {{ strtoupper(Auth::user()->name ?? 'Username') }}
                        </span>
                        <div class="ss-nav-avatar" style="background:{{ $this->primary_color }};">AU</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Navbar Dark --}}
        <div class="ss-card">
            <div class="ss-card-head">
                <span class="ss-card-head-title">Navbar · Dark</span>
            </div>
            <div class="ss-card-body" style="padding: 0.75rem;">
                <div class="ss-nav ss-nav-dark">
                    <div class="ss-nav-brand">
                        @if($this->getLogoUrl())
                            <img src="{{ $this->getLogoUrl() }}" alt="logo" style="filter:brightness(0) invert(1);">
                        @else
                            <div style="width:1.4rem;height:1.4rem;background:#334155;border-radius:3px;flex-shrink:0;"></div>
                        @endif
                        <span style="font-family:'{{ $this->font }}',sans-serif;font-size:0.85rem;font-weight:600;letter-spacing:0.04em;color:#f1f5f9;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:130px;">
                            {{ $this->app_name ?: 'App Name' }}
                        </span>
                    </div>
                    <div class="ss-nav-right">
                        <div class="ss-nav-search" style="background:#1e293b;color:#64748b;">
                            <svg width="9" height="9" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                            Search
                        </div>
                        <span style="font-family:'{{ $this->font }}',sans-serif;font-size:{{ $this->topbar_font_size }}px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#f1f5f9;white-space:nowrap;">
                            {{ strtoupper(Auth::user()->name ?? 'Username') }}
                        </span>
                        <div class="ss-nav-avatar" style="background:{{ $this->primary_color }};">AU</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Primary Color --}}
        <div class="ss-card">
            <div class="ss-card-head">
                <div style="width:14px;height:14px;border-radius:3px;background:{{ $this->primary_color }};flex-shrink:0;"></div>
                <span class="ss-card-head-title">Color &nbsp;<span style="font-family:monospace;font-weight:400;text-transform:none;letter-spacing:0;">{{ $this->primary_color }}</span></span>
            </div>
            <div class="ss-card-body">
                <div class="ss-chips">
                    <div class="ss-chip-solid"  style="background:{{ $this->primary_color }};">Button</div>
                    <div class="ss-chip-outline" style="border-color:{{ $this->primary_color }};color:{{ $this->primary_color }};">Outline</div>
                    <div class="ss-chip-badge"   style="background:{{ $this->primary_color }}20;color:{{ $this->primary_color }};">Active</div>
                    <div class="ss-chip-nav"     style="border-color:{{ $this->primary_color }};background:{{ $this->primary_color }}12;color:{{ $this->primary_color }};">Nav item</div>
                </div>
            </div>
        </div>

        {{-- Font --}}
        <div class="ss-card">
            <div class="ss-card-head">
                <span class="ss-card-head-title">Font · {{ $this->font }}</span>
            </div>
            <div class="ss-card-body">
                <div class="ss-font-box">
                    <div style="font-family:'{{ $this->font }}',sans-serif;font-size:1.3rem;font-weight:600;letter-spacing:0.03em;line-height:1.2;">
                        {{ $this->app_name ?: 'App Name' }}
                    </div>
                    <div style="font-family:'{{ $this->font }}',sans-serif;font-size:0.75rem;opacity:0.45;margin-top:0.2rem;letter-spacing:0.03em;">
                        ABCDEFGHIJKLMNOPQRSTUVWXYZ &nbsp; 0–9
                    </div>
                </div>
            </div>
        </div>

        {{-- Topbar Username Font Size --}}
        <div class="ss-card">
            <div class="ss-card-head">
                <span class="ss-card-head-title">Topbar Username · {{ $this->topbar_font_size }}px</span>
            </div>
            <div class="ss-card-body">
                <div style="
                    background: rgba(0,0,0,0.02);
                    border: 1px solid rgba(0,0,0,0.06);
                    border-radius: 8px;
                    padding: 0.75rem 1rem;
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                ">
                    <div class="ss-nav-avatar" style="background:{{ $this->primary_color }}; flex-shrink:0;">AU</div>
                    <span style="
                        font-family: '{{ $this->font }}', sans-serif;
                        font-size: {{ $this->topbar_font_size }}px;
                        font-weight: 700;
                        text-transform: uppercase;
                        letter-spacing: 0.06em;
                        color: #374151;
                        white-space: nowrap;
                    ">
                        {{ strtoupper(Auth::user()->name ?? 'Username') }}
                    </span>
                </div>
                <div style="display:flex; justify-content:space-between; margin-top:0.4rem;">
                    <span style="font-size:0.6rem;color:#9ca3af;">10px</span>
                    <span style="font-size:0.6rem;color:#9ca3af;font-weight:600;">{{ $this->topbar_font_size }}px</span>
                    <span style="font-size:0.6rem;color:#9ca3af;">24px</span>
                </div>
            </div>
        </div>

        {{-- Favicon --}}
        @if($this->getFaviconUrl())
        <div class="ss-card">
            <div class="ss-card-head">
                <span class="ss-card-head-title">Favicon</span>
            </div>
            <div class="ss-card-body">
                <div style="display:flex;align-items:flex-end;gap:1rem;flex-wrap:wrap;">
                    <div style="display:flex;flex-direction:column;align-items:center;gap:0.3rem;">
                        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:0.4rem;">
                            <img src="{{ $this->getFaviconUrl() }}" style="width:32px;height:32px;object-fit:contain;display:block;">
                        </div>
                        <span style="font-size:0.58rem;color:#9ca3af;">32px</span>
                    </div>
                    <div style="display:flex;flex-direction:column;align-items:center;gap:0.3rem;">
                        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:0.4rem;">
                            <img src="{{ $this->getFaviconUrl() }}" style="width:16px;height:16px;object-fit:contain;display:block;">
                        </div>
                        <span style="font-size:0.58rem;color:#9ca3af;">16px</span>
                    </div>
                    <div style="display:flex;flex-direction:column;align-items:flex-start;gap:0.3rem;">
                        <div class="ss-tab">
                            <img src="{{ $this->getFaviconUrl() }}" style="width:12px;height:12px;object-fit:contain;flex-shrink:0;">
                            <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ Str::limit($this->app_name ?: 'App', 14) }}</span>
                        </div>
                        <div style="height:2px;width:100%;background:#dee2e6;border-radius:0 0 3px 3px;"></div>
                        <span style="font-size:0.58rem;color:#9ca3af;">Browser tab</span>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>

<x-filament-actions::modals />

<script>
document.addEventListener('livewire:initialized', () => {
    const el   = document.getElementById('ss-status');
    const text = document.getElementById('ss-status-text');
    if (!el || !text) return;

    // Show "unsaved changes" when any Livewire property changes
    Livewire.hook('morph.updated', ({ el: updatedEl, component }) => {
        el.className = 'ss-status saving';
        text.textContent = 'Unsaved changes';
    });

    // Show "saved" after save completes
    Livewire.on('notificationSent', () => {
        el.className = 'ss-status saved';
        text.textContent = 'Saved ✓';
        setTimeout(() => {
            el.className = 'ss-status ready';
            text.textContent = 'Up to date';
        }, 3000);
    });
});
</script>

</x-filament-panels::page>
