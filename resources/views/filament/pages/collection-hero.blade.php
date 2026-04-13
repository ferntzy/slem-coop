<div>
<style>
    * { box-sizing: border-box; }

    .cp-hero {
        background: linear-gradient(135deg, #1e3a5f 0%, #0f2744 60%, #0a1628 100%);
        border-radius: 1rem;
        padding: 2rem 2.5rem;
        position: relative;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .cp-hero::before {
        content: '';
        position: absolute;
        top: -60px; right: -60px;
        width: 220px; height: 220px;
        border-radius: 50%;
        background: rgba(251, 191, 36, 0.08);
    }
    .cp-hero::after {
        content: '';
        position: absolute;
        bottom: -40px; left: 30%;
        width: 140px; height: 140px;
        border-radius: 50%;
        background: rgba(251, 191, 36, 0.05);
    }
    .cp-hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(251, 191, 36, 0.15);
        border: 1px solid rgba(251, 191, 36, 0.3);
        color: #fbbf24;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        padding: 4px 12px;
        border-radius: 999px;
        margin-bottom: 0.75rem;
    }
    .cp-hero-title {
        font-size: 1.75rem;
        font-weight: 800;
        color: #fff;
        margin: 0 0 0.4rem;
        letter-spacing: -0.02em;
    }
    .cp-hero-sub {
        color: rgba(255,255,255,0.55);
        font-size: 0.875rem;
        margin: 0;
    }
    .cp-stats {
        display: flex;
        gap: 1.5rem;
        margin-top: 1.5rem;
        flex-wrap: wrap;
    }
    .cp-stat {
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 0.75rem;
        padding: 0.75rem 1.25rem;
        min-width: 120px;
    }
    .cp-stat-value {
        font-size: 1.4rem;
        font-weight: 800;
        color: #fbbf24;
        line-height: 1;
    }
    .cp-stat-label {
        font-size: 0.7rem;
        color: rgba(255,255,255,0.45);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-top: 3px;
    }
    @media (max-width: 640px) {
        .cp-hero { padding: 1.5rem; }
        .cp-hero-title { font-size: 1.3rem; }
        .cp-stats { gap: 0.75rem; }
        .cp-stat { min-width: 90px; }
    }
</style>

<div class="cp-hero">
    <div class="cp-hero-badge">
        <svg width="10" height="10" viewBox="0 0 10 10" fill="currentColor"><circle cx="5" cy="5" r="5"/></svg>
        Payment Management
    </div>
    <h1 class="cp-hero-title">Cash &amp; Manual Payments</h1>
    <p class="cp-hero-sub">Manage cash payments, receipts, uploads, and daily collection entries with a full audit trail.</p>
    <div class="cp-stats">
        <div class="cp-stat">
            <div class="cp-stat-value" id="cp-stat-total">₱0.00</div>
            <div class="cp-stat-label">Today's Collection</div>
        </div>
        <div class="cp-stat">
            <div class="cp-stat-value" id="cp-stat-txn">0</div>
            <div class="cp-stat-label">Transactions</div>
        </div>
        <div class="cp-stat">
            <div class="cp-stat-value" id="cp-stat-pending">0</div>
            <div class="cp-stat-label">Pending Posts</div>
        </div>
    </div>
</div>
</div>