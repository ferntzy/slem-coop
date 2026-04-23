<x-filament-widgets::widget>
    <div
        style="
            position: relative;
            overflow: hidden;
            border-radius: 16px;
            padding: 24px 28px;
            background: linear-gradient(135deg, #064e3b 0%, #0f766e 52%, #0284c7 100%);
            box-shadow: 0 14px 30px rgba(15, 118, 110, 0.18);
        "
    >
        <div style="
            position: absolute; top: -42px; right: -42px;
            width: 180px; height: 180px; border-radius: 50%;
            background: rgba(255,255,255,0.08); pointer-events: none;
        "></div>
        <div style="
            position: absolute; bottom: -36px; left: 18%;
            width: 140px; height: 140px; border-radius: 50%;
            background: rgba(255,255,255,0.06); pointer-events: none;
        "></div>

        <div style="position: relative; display: flex; justify-content: space-between; gap: 20px; flex-wrap: wrap; align-items: flex-start;">
            <div style="min-width: 260px; max-width: 720px;">
                <div style="
                    display: inline-flex; align-items: center; gap: 8px;
                    background: rgba(255,255,255,0.14);
                    border: 1px solid rgba(255,255,255,0.22);
                    border-radius: 999px;
                    padding: 6px 14px;
                    margin-bottom: 12px;
                ">
                    <span style="font-size: 12px; font-weight: 700; color: rgba(255,255,255,0.92); letter-spacing: 0.35px; text-transform: uppercase;">
                        Savings Balance
                    </span>
                </div>

                <h2 style="margin: 0 0 6px 0; font-size: 28px; line-height: 1.1; font-weight: 800; color: #ffffff; letter-spacing: -0.5px;">
                    {{ $this->getFormattedBalance() }}
                </h2>

                <p style="margin: 0; font-size: 13px; line-height: 1.7; color: rgba(255,255,255,0.75); max-width: 680px;">
                    {{ $this->getSavingsAccountLabel() }}
                    <span style="display:inline-block; margin: 0 8px;">&bull;</span>
                    Balances update as posted savings transactions are recorded.
                </p>

                <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-top: 18px;">
                    <div style="
                        background: rgba(255,255,255,0.12);
                        border: 1px solid rgba(255,255,255,0.18);
                        border-radius: 14px;
                        padding: 12px 14px;
                        min-width: 160px;
                    ">
                        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: rgba(255,255,255,0.68);">
                            Savings Accounts
                        </div>
                        <div style="margin-top: 4px; font-size: 20px; font-weight: 900; color: #ffffff;">
                            {{ number_format($this->getSavingsAccountCount()) }}
                        </div>
                    </div>

                    <div style="
                        background: rgba(255,255,255,0.12);
                        border: 1px solid rgba(255,255,255,0.18);
                        border-radius: 14px;
                        padding: 12px 14px;
                        min-width: 190px;
                    ">
                        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: rgba(255,255,255,0.68);">
                            Quick Access
                        </div>
                        <div style="margin-top: 4px; font-size: 15px; font-weight: 800; color: #ffffff;">
                            Open your savings page
                        </div>
                    </div>
                </div>
            </div>

            <div style="flex-shrink: 0; display: flex; flex-direction: column; align-items: flex-end; gap: 10px;">
                <a
                    href="{{ $this->getSavingsPageUrl() }}"
                    style="
                        display: inline-flex; align-items: center; gap: 8px;
                        background: #ffffff;
                        color: #0f766e;
                        border-radius: 10px;
                        padding: 10px 18px;
                        font-size: 14px;
                        font-weight: 700;
                        letter-spacing: 0.2px;
                        text-decoration: none;
                        white-space: nowrap;
                        box-shadow: 0 2px 12px rgba(0,0,0,0.14);
                    "
                    onmouseover="this.style.opacity='0.9'"
                    onmouseout="this.style.opacity='1'"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    View Savings
                </a>

                <div style="
                    display: inline-flex; align-items: center; gap: 8px;
                    background: rgba(255,255,255,0.14);
                    border: 1px solid rgba(255,255,255,0.22);
                    border-radius: 12px;
                    padding: 6px 14px;
                ">
                    <span style="font-size: 14px;">🌿</span>
                    <span style="
                        font-size: 12px;
                        font-weight: 600;
                        color: rgba(255,255,255,0.9);
                        letter-spacing: 0.3px;
                        white-space: nowrap;
                    ">Member Portal</span>
                </div>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>