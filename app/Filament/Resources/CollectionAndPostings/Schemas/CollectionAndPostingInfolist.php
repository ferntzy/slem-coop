<?php

namespace App\Filament\Resources\CollectionAndPostings\Schemas;

use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class CollectionAndPostingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ── Row 1: Payment Summary ─────────────────────────────────
                Section::make('Payment Summary')
                    ->description('Official payment record details')
                    ->icon('heroicon-o-banknotes')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('reference_number')
                            ->label('OR Number')
                            ->fontFamily('mono')
                            ->weight('bold')
                            ->copyable()
                            ->copyMessage('OR Number copied!')
                            ->copyMessageDuration(1500)
                            ->formatStateUsing(fn ($state) => new HtmlString(
                                '<span style="
                                    display:inline-flex;align-items:center;gap:6px;
                                    background:linear-gradient(135deg,#f0fdf4,#dcfce7);
                                    border:1px solid #86efac;
                                    border-radius:8px;padding:4px 10px;
                                    font-family:monospace;font-size:.9rem;
                                    color:#15803d;letter-spacing:.04em;
                                ">'.e($state).'</span>'
                            ))
                            ->html(),

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'Posted' => 'success',
                                'Draft' => 'warning',
                                'Void' => 'danger',
                                default => 'gray',
                            }),

                        TextEntry::make('payment_method')
                            ->label('Payment Method')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'Cash' => 'success',
                                'Bank Transfer' => 'info',
                                'Bank Deposit' => 'primary',
                                'Check' => 'warning',
                                default => 'gray',
                            }),

                        TextEntry::make('amount_paid')
                            ->label('Amount Paid')
                            ->money('PHP')
                            ->weight('bold')
                            ->formatStateUsing(fn ($state) => new HtmlString(
                                '<span style="
                                    font-size:1.5rem;font-weight:800;
                                    background:linear-gradient(135deg,#059669,#10b981);
                                    -webkit-background-clip:text;
                                    -webkit-text-fill-color:transparent;
                                    background-clip:text;
                                    letter-spacing:-.01em;
                                ">₱'.number_format((float) $state, 2).'</span>'
                            ))
                            ->html(),

                        TextEntry::make('payment_date')
                            ->label('Payment Date')
                            ->date('F j, Y')
                            ->icon('heroicon-o-calendar-days'),

                        TextEntry::make('posted_by_user_id')
                            ->label('Posted By')
                            ->icon('heroicon-o-user-circle')
                            ->formatStateUsing(fn ($state) => User::find($state)?->name ?? 'N/A'
                            ),
                    ]),

                // ── Row 2: Loan & Member | Proof of Payment ────────────────
                Grid::make(2)
                    ->schema([

                        // Left — Loan & Member
                        Section::make('Loan & Member')
                            ->icon('heroicon-o-user-group')
                            ->schema([
                                TextEntry::make('member_name')
                                    ->label('Member Name')
                                    ->icon('heroicon-o-user')
                                    ->weight('medium'),

                                TextEntry::make('loan_number')
                                    ->label('Loan Account')
                                    ->icon('heroicon-o-document-text')
                                    ->fontFamily('mono')
                                    ->weight('bold')
                                    ->copyable()
                                    ->copyMessage('Copied!')
                                    ->badge()
                                    ->color('info'),

                                // ── Receipt download links (inline in the card) ──
                                TextEntry::make('id')
                                    ->label('Receipt')
                                    ->formatStateUsing(function ($state, $record) {
                                        $downloadUrl = route('receipt.download', $record);
                                        $printUrl = route('receipt.print', $record);

                                        $downloadBtn = $record->status === 'Posted'
                                            ? "
                                                <a href=\"{$downloadUrl}\" target=\"_blank\"
                                                   style=\"
                                                       display:inline-flex;align-items:center;gap:5px;
                                                       padding:5px 12px;border-radius:7px;font-size:.75rem;font-weight:600;
                                                       background:linear-gradient(135deg,#059669,#10b981);
                                                       color:#fff;text-decoration:none;
                                                       box-shadow:0 1px 4px rgba(5,150,105,.25);
                                                       transition:opacity .15s;
                                                   \"
                                                   onmouseover=\"this.style.opacity='.85'\"
                                                   onmouseout=\"this.style.opacity='1'\">
                                                    <svg xmlns='http://www.w3.org/2000/svg' style='width:13px;height:13px;' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='2'>
                                                        <path stroke-linecap='round' stroke-linejoin='round' d='M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4'/>
                                                    </svg>
                                                    Download
                                                </a>
                                            "
                                            : '';

                                        $voidLabel = $record->status === 'Void' ? '' : "
                                            <a href=\"{$printUrl}\" target=\"_blank\"
                                               style=\"
                                                   display:inline-flex;align-items:center;gap:5px;
                                                   padding:5px 12px;border-radius:7px;font-size:.75rem;font-weight:600;
                                                   background:#fff;color:#374151;text-decoration:none;
                                                   border:1px solid #d1d5db;
                                                   box-shadow:0 1px 3px rgba(0,0,0,.06);
                                                   transition:opacity .15s;
                                               \"
                                               onmouseover=\"this.style.opacity='.8'\"
                                               onmouseout=\"this.style.opacity='1'\">
                                                <svg xmlns='http://www.w3.org/2000/svg' style='width:13px;height:13px;' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='2'>
                                                    <path stroke-linecap='round' stroke-linejoin='round' d='M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z'/>
                                                </svg>
                                                Print
                                            </a>
                                        ";

                                        if (! $downloadBtn && ! $voidLabel) {
                                            return new HtmlString('
                                                <span style="font-size:.75rem;color:#9ca3af;">Not available (Voided)</span>
                                            ');
                                        }

                                        return new HtmlString("
                                            <div style='display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;'>
                                                {$downloadBtn}
                                                {$voidLabel}
                                            </div>
                                        ");
                                    })
                                    ->html(),
                            ]),

                        // Right — Proof of Payment
                        Section::make('Proof of Payment')
                            ->icon('heroicon-o-paper-clip')
                            ->schema([
                                TextEntry::make('document_type')
                                    ->label('Document Type')
                                    ->badge()
                                    ->color('primary')
                                    ->icon('heroicon-o-document'),

                                TextEntry::make('original_file_name')
                                    ->label('File Name')
                                    ->icon('heroicon-o-archive-box')
                                    ->placeholder('No file uploaded.')
                                    ->formatStateUsing(fn ($state) => $state ?? '—')
                                    ->fontFamily('mono'),

                                TextEntry::make('file_path')
                                    ->label('Preview')
                                    ->formatStateUsing(function ($state) {
                                        if (! $state) {
                                            return new HtmlString('
                                                <span style="
                                                    display:inline-flex;align-items:center;gap:6px;
                                                    font-size:.8rem;color:#9ca3af;
                                                    padding:6px 10px;border-radius:8px;
                                                    background:#f9fafb;border:1px dashed #e5e7eb;
                                                ">
                                                    <svg xmlns="http://www.w3.org/2000/svg" style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                    No file attached
                                                </span>
                                            ');
                                        }

                                        return new HtmlString('
                                            <span style="
                                                display:inline-flex;align-items:center;gap:6px;
                                                font-size:.8rem;color:#6b7280;
                                                padding:6px 10px;border-radius:8px;
                                                background:#f0fdf4;border:1px solid #bbf7d0;
                                            ">
                                                <svg xmlns="http://www.w3.org/2000/svg" style="width:14px;height:14px;color:#16a34a;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <span style="color:#15803d;font-weight:500;">File attached</span>
                                            </span>
                                        ');
                                    })
                                    ->html()
                                    ->hintActions([
                                        Action::make('preview_proof')
                                            ->label('View Proof')
                                            ->icon('heroicon-o-eye')
                                            ->color('primary')
                                            ->visible(fn ($record) => ! empty($record->file_path))
                                            ->modalHeading(fn ($record) => $record->original_file_name ?? 'Proof of Payment')
                                            ->modalContent(function ($record) {
                                                $path = storage_path('app/private/'.ltrim($record->file_path, '/'));
                                                $mime = $record->mime_type
                                                            ?? (file_exists($path) ? mime_content_type($path) : 'application/octet-stream');
                                                $filename = $record->original_file_name ?? basename($path);
                                                $uid = 'cp'.substr(md5($record->id.uniqid()), 0, 8);

                                                if (! file_exists($path)) {
                                                    return new HtmlString('
                                                        <div style="padding:3rem;text-align:center;">
                                                            <div style="width:56px;height:56px;background:#fef2f2;border-radius:50%;
                                                                        display:flex;align-items:center;justify-content:center;
                                                                        margin:0 auto 1rem;">
                                                                <svg xmlns="http://www.w3.org/2000/svg" style="width:28px;height:28px;color:#ef4444;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                                </svg>
                                                            </div>
                                                            <p style="font-size:.95rem;font-weight:600;color:#111827;margin:0 0 .25rem;">File not found</p>
                                                            <p style="font-size:.8rem;color:#9ca3af;margin:0;">'.e($filename).'</p>
                                                        </div>
                                                    ');
                                                }

                                                $base64 = base64_encode(file_get_contents($path));
                                                $src = "data:{$mime};base64,{$base64}";

                                                if ($mime === 'application/pdf') {
                                                    return new HtmlString('
                                                        <div style="padding:4px;">
                                                            <embed src="'.$src.'" type="application/pdf"
                                                                   style="width:100%;height:76vh;border-radius:10px;
                                                                          border:1px solid #e5e7eb;box-shadow:0 1px 3px rgba(0,0,0,.08);" />
                                                        </div>
                                                    ');
                                                }

                                                if (! str_starts_with($mime, 'image/')) {
                                                    return new HtmlString('
                                                        <div style="padding:3rem;text-align:center;">
                                                            <p style="font-size:.875rem;color:#6b7280;">Preview not available for this file type.</p>
                                                            <p style="font-size:.75rem;color:#9ca3af;margin-top:.25rem;">'.e($filename).'</p>
                                                        </div>
                                                    ');
                                                }

                                                return new HtmlString('
                                                    <div style="font-family:system-ui,sans-serif;padding:2px;">
                                                        <div style="
                                                            display:flex;align-items:center;justify-content:space-between;
                                                            margin-bottom:10px;padding:8px 12px;
                                                            background:linear-gradient(135deg,#f8fafc,#f1f5f9);
                                                            border-radius:10px;border:1px solid #e2e8f0;
                                                        ">
                                                            <div style="display:flex;align-items:center;gap:8px;">
                                                                <div style="
                                                                    width:8px;height:8px;border-radius:50%;
                                                                    background:linear-gradient(135deg,#10b981,#059669);
                                                                    box-shadow:0 0 6px rgba(16,185,129,.4);
                                                                "></div>
                                                                <span style="font-size:.72rem;color:#64748b;font-weight:500;letter-spacing:.02em;">
                                                                    SCROLL TO ZOOM &nbsp;·&nbsp; DRAG TO PAN
                                                                </span>
                                                            </div>
                                                            <div style="display:flex;gap:6px;align-items:center;">
                                                                <span onclick="window[\'cpV_'.$uid.'\'].zoom(0.3)"
                                                                      style="display:inline-flex;align-items:center;justify-content:center;
                                                                             width:32px;height:32px;background:white;
                                                                             border:1px solid #e2e8f0;border-radius:8px;
                                                                             font-size:18px;font-weight:300;cursor:pointer;
                                                                             box-shadow:0 1px 3px rgba(0,0,0,.06);
                                                                             color:#374151;user-select:none;transition:all .15s ease;"
                                                                      onmouseover="this.style.background=\'#f0fdf4\';this.style.borderColor=\'#86efac\';this.style.color=\'#16a34a\';"
                                                                      onmouseout="this.style.background=\'white\';this.style.borderColor=\'#e2e8f0\';this.style.color=\'#374151\';">+</span>
                                                                <span onclick="window[\'cpV_'.$uid.'\'].zoom(-0.3)"
                                                                      style="display:inline-flex;align-items:center;justify-content:center;
                                                                             width:32px;height:32px;background:white;
                                                                             border:1px solid #e2e8f0;border-radius:8px;
                                                                             font-size:18px;font-weight:300;cursor:pointer;
                                                                             box-shadow:0 1px 3px rgba(0,0,0,.06);
                                                                             color:#374151;user-select:none;transition:all .15s ease;"
                                                                      onmouseover="this.style.background=\'#fef2f2\';this.style.borderColor=\'#fca5a5\';this.style.color=\'#dc2626\';"
                                                                      onmouseout="this.style.background=\'white\';this.style.borderColor=\'#e2e8f0\';this.style.color=\'#374151\';">−</span>
                                                                <div style="width:1px;height:20px;background:#e2e8f0;margin:0 2px;"></div>
                                                                <span onclick="window[\'cpV_'.$uid.'\'].reset()"
                                                                      style="display:inline-flex;align-items:center;justify-content:center;
                                                                             height:32px;padding:0 12px;background:white;
                                                                             border:1px solid #e2e8f0;border-radius:8px;
                                                                             font-size:11px;font-weight:600;cursor:pointer;
                                                                             box-shadow:0 1px 3px rgba(0,0,0,.06);
                                                                             color:#475569;user-select:none;letter-spacing:.04em;transition:all .15s ease;"
                                                                      onmouseover="this.style.background=\'#f8fafc\';this.style.color=\'#0f172a\';"
                                                                      onmouseout="this.style.background=\'white\';this.style.color=\'#475569\';">RESET</span>
                                                            </div>
                                                        </div>

                                                        <div id="wrap_'.$uid.'"
                                                             style="overflow:hidden;
                                                                    background:repeating-conic-gradient(#f1f5f9 0% 25%, #e2e8f0 0% 50%) 0 0/20px 20px;
                                                                    border-radius:10px;height:64vh;cursor:grab;position:relative;
                                                                    border:1px solid #e2e8f0;box-shadow:inset 0 2px 8px rgba(0,0,0,.04);">
                                                            <img id="img_'.$uid.'"
                                                                 src="'.$src.'"
                                                                 draggable="false"
                                                                 style="position:absolute;top:50%;left:50%;
                                                                        max-width:92%;max-height:92%;object-fit:contain;
                                                                        transform-origin:center center;
                                                                        transform:translate(-50%,-50%) scale(1);
                                                                        user-select:none;pointer-events:none;
                                                                        border-radius:4px;box-shadow:0 4px 24px rgba(0,0,0,.12);" />
                                                        </div>

                                                        <div id="zoom_'.$uid.'"
                                                             style="text-align:center;margin-top:8px;
                                                                    font-size:.7rem;color:#94a3b8;
                                                                    font-weight:500;letter-spacing:.04em;">100%</div>
                                                    </div>

                                                    <script>
                                                    (function(){
                                                        var uid  = "'.$uid.'";
                                                        var st   = {scale:1, tx:0, ty:0};
                                                        var drag = {on:false, ox:0, oy:0, ltx:0, lty:0};

                                                        function apply(){
                                                            var img = document.getElementById("img_"+uid);
                                                            var ind = document.getElementById("zoom_"+uid);
                                                            if(!img) return;
                                                            img.style.transform =
                                                                "translate(calc(-50% + "+st.tx+"px), calc(-50% + "+st.ty+"px)) scale("+st.scale+")";
                                                            if(ind) ind.textContent = Math.round(st.scale*100)+"%";
                                                        }

                                                        window["cpV_"+uid] = {
                                                            zoom: function(d){ st.scale = Math.min(Math.max(st.scale+d,0.2),10); apply(); },
                                                            reset: function(){ st={scale:1,tx:0,ty:0}; apply(); }
                                                        };

                                                        function attach(){
                                                            var wrap = document.getElementById("wrap_"+uid);
                                                            if(!wrap){ setTimeout(attach,120); return; }
                                                            wrap.addEventListener("wheel",function(e){
                                                                e.preventDefault();
                                                                window["cpV_"+uid].zoom(e.deltaY<0?0.15:-0.15);
                                                            },{passive:false});
                                                            wrap.addEventListener("mousedown",function(e){
                                                                if(e.button!==0) return;
                                                                drag={on:true,ox:e.clientX,oy:e.clientY,ltx:st.tx,lty:st.ty};
                                                                wrap.style.cursor="grabbing"; e.preventDefault();
                                                            });
                                                            window.addEventListener("mousemove",function(e){
                                                                if(!drag.on) return;
                                                                st.tx=drag.ltx+(e.clientX-drag.ox);
                                                                st.ty=drag.lty+(e.clientY-drag.oy);
                                                                apply();
                                                            });
                                                            window.addEventListener("mouseup",function(){
                                                                if(!drag.on) return;
                                                                drag.on=false; wrap.style.cursor="grab";
                                                            });
                                                        }
                                                        setTimeout(attach,250);
                                                    })();
                                                    </script>
                                                ');
                                            })
                                            ->modalSubmitAction(false)
                                            ->modalCancelActionLabel('Close')
                                            ->modalWidth('4xl'),
                                    ]),
                            ]),
                    ]),

                // ── Row 3: Notes (collapsed) ───────────────────────────────
                Section::make('Notes')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->collapsed()
                    ->schema([
                        TextEntry::make('notes')
                            ->label('')
                            ->placeholder('No notes recorded.')
                            ->columnSpanFull(),
                    ]),

                // ── Row 4: Audit Trail (collapsed) ─────────────────────────
                Section::make('Audit Trail')
                    ->icon('heroicon-o-shield-check')
                    ->collapsed()
                    ->schema([
                        TextEntry::make('audit_trail')
                            ->label('')
                            ->columnSpanFull()
                            ->formatStateUsing(function ($state) {
                                if (! $state) {
                                    return new HtmlString('
                                        <div style="padding:1.5rem;text-align:center;">
                                            <p style="font-size:.875rem;color:#9ca3af;">No audit trail available.</p>
                                        </div>
                                    ');
                                }

                                $logs = is_string($state) ? json_decode($state, true) : $state;

                                if (empty($logs) || ! is_array($logs)) {
                                    return new HtmlString('<span style="font-size:.875rem;color:#9ca3af;">No entries.</span>');
                                }

                                $rows = collect($logs)->map(function ($log) {
                                    $action = e($log['action'] ?? '—');
                                    $by = e($log['by'] ?? '—');
                                    $at = isset($log['at'])
                                        ? Carbon::parse($log['at'])->format('M j, Y g:i A')
                                        : '—';
                                    $note = e($log['note'] ?? '');

                                    $actionBadge = match (strtolower($action)) {
                                        'posted' => 'background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;',
                                        'voided' => 'background:#fef2f2;color:#dc2626;border:1px solid #fecaca;',
                                        'edited' => 'background:#fffbeb;color:#d97706;border:1px solid #fde68a;',
                                        default => 'background:#f8fafc;color:#475569;border:1px solid #e2e8f0;',
                                    };

                                    return "
                                        <tr style='border-bottom:1px solid #f1f5f9;'>
                                            <td style='padding:10px 12px 10px 0;'>
                                                <span style='display:inline-block;padding:2px 8px;border-radius:6px;font-size:.75rem;font-weight:600;{$actionBadge}'>{$action}</span>
                                            </td>
                                            <td style='padding:10px 12px 10px 0;font-size:.8rem;color:#374151;font-weight:500;'>{$by}</td>
                                            <td style='padding:10px 12px 10px 0;font-size:.78rem;color:#6b7280;'>{$at}</td>
                                            <td style='padding:10px 0;font-size:.78rem;color:#9ca3af;font-style:italic;'>{$note}</td>
                                        </tr>";
                                })->implode('');

                                return new HtmlString("
                                    <div style='overflow-x:auto;'>
                                        <table style='width:100%;border-collapse:collapse;'>
                                            <thead>
                                                <tr style='border-bottom:2px solid #e2e8f0;'>
                                                    <th style='padding:8px 12px 8px 0;text-align:left;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;'>Action</th>
                                                    <th style='padding:8px 12px 8px 0;text-align:left;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;'>By</th>
                                                    <th style='padding:8px 12px 8px 0;text-align:left;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;'>When</th>
                                                    <th style='padding:8px 0;text-align:left;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;'>Note</th>
                                                </tr>
                                            </thead>
                                            <tbody>{$rows}</tbody>
                                        </table>
                                    </div>
                                ");
                            })
                            ->html(),

                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->icon('heroicon-o-clock')
                            ->dateTime('F j, Y g:i A'),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->icon('heroicon-o-arrow-path')
                            ->dateTime('F j, Y g:i A')
                            ->since(),
                    ]),

            ]);
    }
}
