@php
    try {
        $font     = \App\Models\SystemSetting::get('font', 'Rajdhani') ?? 'Rajdhani';
        $fontSize = \App\Models\SystemSetting::get('topbar_font_size', '14') ?? '14';
    } catch (\Throwable $e) {
        $font     = 'Rajdhani';
        $fontSize = '14';
    }

    $fontMap = [
        'Rajdhani'   => 'https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&display=swap',
        'Oxanium'    => 'https://fonts.googleapis.com/css2?family=Oxanium:wght@600;700&display=swap',
        'Orbitron'   => 'https://fonts.googleapis.com/css2?family=Orbitron:wght@600;700&display=swap',
        'Syne'       => 'https://fonts.googleapis.com/css2?family=Syne:wght@600;700&display=swap',
        'Exo 2'      => 'https://fonts.googleapis.com/css2?family=Exo+2:wght@600;700&display=swap',
        'Bebas Neue' => 'https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap',
        'Outfit'     => 'https://fonts.googleapis.com/css2?family=Outfit:wght@600;700&display=swap',
    ];

    $fontUrl = $fontMap[$font] ?? $fontMap['Rajdhani'];
@endphp

<link href="{{ $fontUrl }}" rel="stylesheet">

<style>
    #notif-fixed-widget {
        position: fixed;
        top: 0;
        right: 5rem;
        height: 3.5rem;
        z-index: 40;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0 0.25rem;
    }

    #topbar-username {
        font-family: '{{ $font }}', sans-serif;
        font-size: {{ $fontSize }}px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        white-space: nowrap;
        color: #1e293b;
        transition: font-size 0.2s ease;
        line-height: 1;
    }
    .dark #topbar-username { color: #f1f5f9; }

    .notif-bell-btn {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.1rem;
        height: 2.1rem;
        border-radius: 0.5rem;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #475569;
        cursor: pointer;
        transition: background 0.15s ease, border-color 0.15s ease,
                    box-shadow 0.15s ease, transform 0.1s ease;
        outline: none;
        flex-shrink: 0;
    }
    .notif-bell-btn:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transform: translateY(-1px);
    }
    .notif-bell-btn:focus-visible {
        box-shadow: 0 0 0 3px rgba(99,102,241,0.25);
        border-color: #818cf8;
    }
    .dark .notif-bell-btn {
        background: #1e293b;
        border-color: #334155;
        color: #94a3b8;
    }
    .dark .notif-bell-btn:hover {
        background: #273549;
        border-color: #475569;
    }
    .notif-bell-icon {
        width: 1rem;
        height: 1rem;
    }
    .notif-bell-btn[aria-expanded="true"] .notif-bell-icon {
        animation: bell-ring 0.4s ease;
    }
    @keyframes bell-ring {
        0%   { transform: rotate(0deg); }
        20%  { transform: rotate(-15deg); }
        40%  { transform: rotate(12deg); }
        60%  { transform: rotate(-8deg); }
        80%  { transform: rotate(5deg); }
        100% { transform: rotate(0deg); }
    }

    .notif-badge {
        position: absolute;
        top: -0.3rem;
        right: -0.3rem;
        min-width: 1.05rem;
        height: 1.05rem;
        padding: 0 0.2rem;
        border-radius: 999px;
        background: #ef4444;
        border: 2px solid #ffffff;
        font-size: 0.55rem;
        font-weight: 700;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        transition: transform 0.2s cubic-bezier(.34,1.56,.64,1);
    }
    .dark .notif-badge { border-color: #0f172a; }

    .notif-panel {
        position: fixed;
        top: 3.6rem;
        right: 1rem;
        z-index: 9999;
        width: 380px;
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.07),
                    0 10px 30px -5px rgba(0,0,0,0.14);
        overflow: hidden;
        transform-origin: top right;
    }
    .dark .notif-panel {
        background: #1e293b;
        border-color: #334155;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.3),
                    0 10px 30px -5px rgba(0,0,0,0.5);
    }
    .notif-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.875rem 1rem 0.75rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .dark .notif-panel-header { border-bottom-color: #334155; }
    .notif-panel-title {
        font-size: 0.8125rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #0f172a;
    }
    .dark .notif-panel-title { color: #f1f5f9; }
    .notif-count-chip {
        font-size: 0.65rem;
        font-weight: 700;
        padding: 0.15rem 0.45rem;
        border-radius: 999px;
        background: #f1f5f9;
        color: #64748b;
    }
    .dark .notif-count-chip { background: #273549; color: #94a3b8; }
    .notif-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        padding: 2rem 1rem;
        color: #94a3b8;
        font-size: 0.75rem;
        text-align: center;
    }
    .notif-empty svg { opacity: 0.35; }
    .notif-list {
        max-height: 18rem;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: #e2e8f0 transparent;
    }
    .notif-list::-webkit-scrollbar { width: 4px; }
    .notif-list::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 999px; }
    .dark .notif-list::-webkit-scrollbar-thumb { background: #334155; }
    .notif-item {
        cursor: pointer;
        display: flex;
        align-items: flex-start;
        gap: 0.625rem;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #f8fafc;
        transition: background 0.12s ease;
    }
    .notif-item:last-child { border-bottom: none; }
    .notif-item:hover { background: #f8fafc; }
    .dark .notif-item { border-bottom-color: #1e293b; }
    .dark .notif-item:hover { background: #273549; }
    .notif-dot {
        flex-shrink: 0;
        margin-top: 0.35rem;
        width: 0.45rem;
        height: 0.45rem;
        border-radius: 50%;
        background: #6366f1;
    }
    .notif-dot.read { background: transparent; }
    .notif-item-body { flex: 1; min-width: 0; }
    .notif-item-title {
        font-size: 0.75rem;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.3;
        margin-bottom: 0.1rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .dark .notif-item-title { color: #e2e8f0; }
    .notif-item-desc {
        font-size: 0.7rem;
        color: #64748b;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
    }
    .dark .notif-item-desc { color: #94a3b8; }
    .notif-item-time {
        margin-top: 0.25rem;
        font-size: 0.625rem;
        font-weight: 500;
        color: #cbd5e1;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .dark .notif-item-time { color: #475569; }
    .notif-delete-btn {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 1.4rem;
        height: 1.4rem;
        border-radius: 0.35rem;
        border: none;
        background: transparent;
        color: #cbd5e1;
        cursor: pointer;
        transition: background 0.12s ease, color 0.12s ease;
        outline: none;
    }
    .notif-delete-btn:hover { background: #fee2e2; color: #ef4444; }
    .dark .notif-delete-btn:hover { background: rgba(239,68,68,0.15); color: #f87171; }
    .notif-delete-btn svg { width: 0.75rem; height: 0.75rem; }
    .notif-panel-footer {
        padding: 0.625rem 1rem;
        border-top: 1px solid #f1f5f9;
        display: flex;
        justify-content: flex-end;
    }
    .dark .notif-panel-footer { border-top-color: #334155; }
    .notif-mark-all-btn {
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #6366f1;
        background: none;
        border: none;
        cursor: pointer;
        padding: 0.25rem 0.5rem;
        border-radius: 0.35rem;
        transition: background 0.12s ease, color 0.12s ease;
        outline: none;
    }
    .notif-mark-all-btn:hover { background: #eef2ff; color: #4f46e5; }
    .dark .notif-mark-all-btn:hover { background: rgba(99,102,241,0.15); color: #818cf8; }
    .notif-mark-all-btn:disabled { opacity: 0.35; pointer-events: none; }
</style>

@auth
<div
    id="notif-fixed-widget"
    x-data="notificationsWidget()"
    x-init="fetchNotifications(); startAutoRefresh()"
    @destroy.window="destroy()">

    <div id="topbar-username">{{ Auth::user()->name }}</div>

    <div style="position: relative;" x-data="{ open: false }" @keydown.escape.window="open = false">
        <button
            type="button"
            class="notif-bell-btn"
            :aria-expanded="open.toString()"
            @click="open = !open">
            <svg class="notif-bell-icon" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>

            <span class="notif-badge"
                  x-show="unreadCount > 0"
                  x-text="unreadCount > 99 ? '99+' : unreadCount"></span>
        </button>

        <div class="notif-panel"
             x-show="open"
             x-transition
             @click.outside="open = false"
             style="display: none;">

            <div class="notif-panel-header">
                <span class="notif-panel-title">NOTIFICATIONS</span>
                <span class="notif-count-chip"
                      x-text="notifications.length + (notifications.length === 1 ? ' item' : ' items')"></span>
            </div>

            <div class="notif-list">
                <template x-if="notifications.length === 0">
                    <div class="notif-empty">
                        <span>You're all caught up!</span>
                    </div>
                </template>

                <template x-for="notification in notifications" :key="notification.id">
                    <div class="notif-item" @click="markAsRead(notification.id)">
                        <span class="notif-dot" :class="{ read: notification.is_read }"></span>

                        <div class="notif-item-body">
                            <div class="notif-item-title" x-text="notification.title || 'Notification'"></div>
                            <div class="notif-item-desc"  x-text="notification.description || ''"></div>
                            <div class="notif-item-time"  x-text="formatRelativeTime(notification.created_at)"></div>
                        </div>

                        <button class="notif-delete-btn" @click.stop="deleteNotification(notification.id)">✕</button>
                    </div>
                </template>
            </div>

            <div class="notif-panel-footer">
                <button class="notif-mark-all-btn"
                        :disabled="unreadCount === 0"
                        @click.prevent="markAllRead()">
                    MARK ALL AS READ
                </button>
            </div>
        </div>
    </div>
</div>
@endauth

<script>
function notificationsWidget() {
    return {
        notifications: [],
        unreadCount: 0,
        refreshInterval: null,

        async fetchNotifications(silent = false) {
            try {
                const res  = await this._post('/coop/fetch-notification');
                const data = await res.json();

                this.notifications = data.notifications || [];
                this.unreadCount   = data.unread ?? this.notifications.filter(n => !n.is_read).length;
            } catch (e) {
                if (!silent) console.error('[Notifications] Fetch error:', e);
            }
        },

        async deleteNotification(id) {
            const backup = [...this.notifications];
            this.notifications = this.notifications.filter(n => n.id !== id);
            this._syncUnreadCount();

            try {
                await this._post('/coop/delete-notification', { notif_id: id });
            } catch (e) {
                this.notifications = backup;
                this._syncUnreadCount();
                console.error('[Notifications] Delete failed:', e);
            }
        },

        async markAsRead(id) {
            const notif = this.notifications.find(n => n.id === id);
            if (!notif || notif.is_read) return;

            notif.is_read = true;
            this._syncUnreadCount();

            try {
                await this._post('/coop/mark-notification-read', { notif_id: id });
            } catch (e) {
                notif.is_read = false;
                this._syncUnreadCount();
                console.error('[Notifications] Mark read failed:', e);
            }
        },

        async markAllRead() {
            const unread = this.notifications.filter(n => !n.is_read);
            if (!unread.length) return;

            unread.forEach(n => n.is_read = true);
            this._syncUnreadCount();

            try {
                await this._post('/coop/mark-all-notifications-read');
            } catch (e) {
                unread.forEach(n => n.is_read = false);
                this._syncUnreadCount();
                console.error('[Notifications] Mark all read failed:', e);
            }
        },

        _syncUnreadCount() {
            this.unreadCount = this.notifications.filter(n => !n.is_read).length;
        },
    
        _post(url, payload = {}) {
            return fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? ''
                },
                body: JSON.stringify(payload)
            });
        },

        formatRelativeTime(rawDate) {
            const date    = new Date(rawDate);
            const diffMs  = Date.now() - date.getTime();
            const diffMin = Math.floor(diffMs / 60000);

            if (diffMin < 1)  return 'Just now';
            if (diffMin < 60) return `${diffMin}m ago`;
            const diffHr = Math.floor(diffMin / 60);
            if (diffHr < 24)  return `${diffHr}h ago`;
            const diffDay = Math.floor(diffHr / 24);
            if (diffDay < 7)  return `${diffDay}d ago`;
            return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
        },

        startAutoRefresh() {
            if (this.refreshInterval) clearInterval(this.refreshInterval);
            this.refreshInterval = setInterval(() => {
                this.fetchNotifications(true);
            }, 40000);
        },

        destroy() {
            if (this.refreshInterval) clearInterval(this.refreshInterval);
        }
    };
}
</script>