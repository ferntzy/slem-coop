<x-filament-widgets::widget>
    <div
        style="
            position: relative;
            overflow: hidden;
            border-radius: 16px;
            padding: 24px 28px;
            background: linear-gradient(135deg, #0f766e 0%, #0d9488 50%, #0369a1 100%);
        "
    >
        <div style="
            position: absolute; top: -40px; right: -40px;
            width: 180px; height: 180px; border-radius: 50%;
            background: rgba(255,255,255,0.08); pointer-events: none;
        "></div>
        <div style="
            position: absolute; bottom: -30px; right: 80px;
            width: 120px; height: 120px; border-radius: 50%;
            background: rgba(255,255,255,0.06); pointer-events: none;
        "></div>

        <div style="display: flex; align-items: center; gap: 20px; position: relative;">
            <div style="
                width: 64px; height: 64px; border-radius: 50%; flex-shrink: 0;
                background: rgba(255,255,255,0.18);
                border: 2px solid rgba(255,255,255,0.35);
                display: flex; align-items: center; justify-content: center;
                font-size: 22px; font-weight: 700; color: #ffffff;
                letter-spacing: 1px;
            ">
                {{ $this->getInitials() }}
            </div>

            <div style="flex: 1; min-width: 0;">
                <p style="
                    margin: 0 0 2px 0;
                    font-size: 13px;
                    font-weight: 500;
                    color: rgba(255,255,255,0.72);
                    letter-spacing: 0.3px;
                ">
                    {{ $this->getTimeGreeting() }}
                </p>
                <h2 style="
                    margin: 0 0 6px 0;
                    font-size: 26px;
                    font-weight: 700;
                    color: #ffffff;
                    letter-spacing: -0.5px;
                    line-height: 1.2;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                ">
                    {{ $this->getFullName() }}
                </h2>
                <p style="
                    margin: 0;
                    font-size: 12.5px;
                    color: rgba(255,255,255,0.6);
                    letter-spacing: 0.2px;
                ">
                    {{ now()->format('l, F j, Y') }}
                    &nbsp;&bull;&nbsp;
                    {{ now()->timezone(config('app.timezone'))->format('g:i A') }}
                </p>
            </div>

            <div style="flex-shrink: 0; display: flex; flex-direction: column; align-items: flex-end; gap: 10px;">
                <a
                    href="{{ $this->getLoanQueueUrl() }}"
                    style="
                        display: inline-flex; align-items: center; gap: 8px;
                        background: #ffffff;
                        color: #0f766e;
                        border-radius: 10px;
                        padding: 10px 20px;
                        font-size: 14px;
                        font-weight: 700;
                        letter-spacing: 0.2px;
                        text-decoration: none;
                        white-space: nowrap;
                        box-shadow: 0 2px 12px rgba(0,0,0,0.15);
                    "
                    onmouseover="this.style.opacity='0.88'"
                    onmouseout="this.style.opacity='1'"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 6h18M3 12h18M3 18h18"/>
                    </svg>
                    Review Loan Applications
                </a>

                <div style="
                    display: inline-flex; align-items: center; gap: 8px;
                    background: rgba(255,255,255,0.14);
                    border: 1px solid rgba(255,255,255,0.22);
                    border-radius: 12px;
                    padding: 6px 14px;
                ">
                    <span style="
                        font-size: 12px;
                        font-weight: 600;
                        color: rgba(255,255,255,0.9);
                        letter-spacing: 0.3px;
                        white-space: nowrap;
                    ">Loan Officer Portal</span>
                </div>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
