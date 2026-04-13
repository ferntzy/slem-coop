<?php

namespace App\Filament\Resources\PaymentAllocationLogics\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentAllocationLogicForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ── Section 1: Priority ───────────────────────────────────────────
                Section::make('Payment Allocation Priority')
                    ->description('Order in which funds are applied when a payment is received.')
                    ->schema([

                        Repeater::make('allocationRules')
                            ->label('Priority Order')
                            ->relationship('allocationRules')
                            ->orderColumn('priority')
                            ->schema([

                                Select::make('component')
                                    ->label('Component')
                                    ->options([
                                        'interest'  => 'Interest',
                                        'principal' => 'Principal',
                                        'penalty'   => 'Penalty',
                                    ])
                                    ->required()
                                    ->native(false)
                                    ->distinct(),

                            ])
                            ->defaultItems(3)
                            ->reorderable(false)
                            ->deletable(false)
                            ->addable(false)
                            ->minItems(3)
                            ->maxItems(3)
                            ->columnSpanFull(),

                    ]),

                // ── Section 2: Payment Behavior ───────────────────────────────────
                Section::make('Payment Behavior')
                    ->description('Control what types of payments the system will accept.')
                    ->schema([

                        Toggle::make('allow_partial')
                            ->label('Allow Partial Payments')
                            ->helperText('Accept amounts less than the amount due.')
                            ->default(true),

                        Toggle::make('allow_advance')
                            ->label('Allow Advance Payments')
                            ->helperText('Accept payments made before the due date.')
                            ->default(true),

                        Toggle::make('allow_overpayment')
                            ->label('Allow Overpayment')
                            ->helperText('Carry excess credit forward to the next due date.')
                            ->default(true),

                        Toggle::make('auto_apply')
                            ->label('Auto Apply Payments')
                            ->helperText('Automatically match payments to loans using rules.')
                            ->default(false),

                    ])
                    ->columns(2),

                // ── Section 3: Void Payments ──────────────────────────────────────
                Section::make('Void Payments')
                    ->description('Control whether posted payments can be reversed.')
                    ->icon('heroicon-o-x-circle')
                    ->schema([

                        Toggle::make('allow_void')
                            ->label('Allow Void Payments')
                            ->helperText('Staff can cancel a posted payment with an explanation.')
                            ->default(true)
                            ->live(),

                        Toggle::make('require_void_reason')
                            ->label('Require Reason for Void')
                            ->helperText('A written reason must be provided before voiding.')
                            ->default(true)
                            ->disabled(fn ($get) => ! $get('allow_void'))
                            ->dehydrated(fn ($get) => (bool) $get('allow_void')),

                    ])
                    ->columns(2),

                // ── Section 4: Edit Payments ──────────────────────────────────────
                Section::make('Edit Payments')
                    ->description('Control whether posted payments can be corrected.')
                    ->icon('heroicon-o-pencil-square')
                    ->schema([

                        Toggle::make('allow_edit')
                            ->label('Allow Edit Payments')
                            ->helperText('Staff can correct a posted payment\'s details.')
                            ->default(true)
                            ->live(),

                        Toggle::make('require_edit_reason')
                            ->label('Require Reason for Edit')
                            ->helperText('A written reason must be provided before editing.')
                            ->default(true)
                            ->disabled(fn ($get) => ! $get('allow_edit'))
                            ->dehydrated(fn ($get) => (bool) $get('allow_edit')),

                    ])
                    ->columns(2),

                // ── Section 5: Audit Log (read-only) ─────────────────────────────
                Section::make('Void / Edit Audit Log')
                    ->description('Recent void and edit actions recorded on loan payments.')
                    ->icon('heroicon-o-shield-check')
                    ->collapsed()
                    ->columnSpanFull()
                    ->schema([

                        Placeholder::make('audit_log_table')
                            ->label('')
                            ->content(function ($record): HtmlString {

                                if (! $record) {
                                    return new HtmlString(
                                        '<p style="font-size:.8rem;color:#9ca3af">Save the record first to view audit logs.</p>'
                                    );
                                }

                                try {
                                    $logs = \DB::table('loan_payment_audit_logs as al')
                                        ->leftJoin('loan_payments as lp', 'lp.loan_payment_id', '=', 'al.loan_payment_id')
                                        ->leftJoin('users as u', 'u.user_id', '=', 'al.user_id')
                                        ->leftJoin('profiles as p', 'p.profile_id', '=', 'u.profile_id')
                                        ->select(
                                            'al.id',
                                            'al.action',
                                            'al.reason',
                                            'al.created_at',
                                            'al.before',
                                            'al.after',
                                            'lp.loan_payment_id',
                                            \DB::raw("CONCAT(p.first_name, ' ', p.last_name) as user_name")
                                        )
                                        ->orderByDesc('al.created_at')
                                        ->limit(50)
                                        ->get();

                                } catch (\Throwable $e) {
                                    return new HtmlString(
                                        '<p style="font-size:.8rem;color:#ef4444">Error loading audit log: ' . e($e->getMessage()) . '</p>'
                                    );
                                }

                                if ($logs->isEmpty()) {
                                    return new HtmlString(
                                        '<p style="font-size:.8rem;color:var(--color-text-tertiary)">No void or edit actions recorded yet.</p>'
                                    );
                                }

                                $rows = $logs->map(function ($log) {

                                    $actionColor = $log->action === 'void' ? '#dc2626' : '#d97706';
                                    $actionBg    = $log->action === 'void' ? '#fef2f2' : '#fffbeb';
                                    $actionLabel = strtoupper($log->action);

                                    $diffHtml = '';
                                    try {
                                        $before  = json_decode($log->before ?? '{}', true) ?? [];
                                        $after   = json_decode($log->after  ?? '{}', true) ?? [];
                                        $allKeys = array_unique(array_merge(array_keys($before), array_keys($after)));
                                        $diffs   = [];

                                        foreach ($allKeys as $key) {
                                            $bVal = $before[$key] ?? '—';
                                            $aVal = $after[$key]  ?? '—';
                                            if ($bVal !== $aVal) {
                                                $diffs[] = "<span style='color:var(--color-text-secondary)'>{$key}:</span> "
                                                         . "<span style='color:#dc2626;text-decoration:line-through'>{$bVal}</span> "
                                                         . "<span style='color:var(--color-text-secondary)'>→</span> "
                                                         . "<span style='color:#059669'>{$aVal}</span>";
                                            }
                                        }

                                        if ($diffs) {
                                            $diffHtml = '<div style="margin-top:4px;font-size:.72rem;line-height:1.8">'
                                                      . implode('<br>', $diffs)
                                                      . '</div>';
                                        }
                                    } catch (\Throwable) {}

                                    $reason = $log->reason
                                        ? '<div style="font-size:.75rem;color:var(--color-text-primary);margin-top:3px">
                                               <span style="color:var(--color-text-secondary)">Reason:</span> ' . e($log->reason) . '
                                           </div>'
                                        : '';

                                    $date   = \Carbon\Carbon::parse($log->created_at)->format('M d, Y h:i A');
                                    $user   = e($log->user_name ?? 'Unknown');
                                    $payRef = $log->loan_payment_id ? '#' . $log->loan_payment_id : '—';

                                    return "
                                        <tr style='border-bottom:0.5px solid var(--color-border-tertiary)'>
                                            <td style='padding:.6rem .75rem;vertical-align:top;white-space:nowrap'>
                                                <span style='background:{$actionBg};color:{$actionColor};font-size:.65rem;
                                                    font-weight:600;padding:2px 8px;border-radius:999px;letter-spacing:.06em'>
                                                    {$actionLabel}
                                                </span>
                                            </td>
                                            <td style='padding:.6rem .75rem;vertical-align:top;font-size:.8rem;color:var(--color-text-primary);white-space:nowrap'>
                                                {$payRef}
                                            </td>
                                            <td style='padding:.6rem .75rem;vertical-align:top;font-size:.8rem;color:var(--color-text-primary)'>
                                                {$reason}
                                                {$diffHtml}
                                            </td>
                                            <td style='padding:.6rem .75rem;vertical-align:top;font-size:.75rem;color:var(--color-text-secondary);white-space:nowrap'>
                                                {$user}
                                            </td>
                                            <td style='padding:.6rem .75rem;vertical-align:top;font-size:.75rem;color:var(--color-text-secondary);white-space:nowrap'>
                                                {$date}
                                            </td>
                                        </tr>";
                                })->implode('');

                                return new HtmlString("
                                    <div style='overflow-x:auto;border:0.5px solid var(--color-border-tertiary);border-radius:.75rem;overflow:hidden'>
                                        <table style='width:100%;border-collapse:collapse;font-family:inherit'>
                                            <thead>
                                                <tr style='background:var(--color-background-secondary);border-bottom:0.5px solid var(--color-border-tertiary)'>
                                                    <th style='padding:.55rem .75rem;text-align:left;font-size:.65rem;font-weight:500;letter-spacing:.08em;text-transform:uppercase;color:var(--color-text-secondary)'>Action</th>
                                                    <th style='padding:.55rem .75rem;text-align:left;font-size:.65rem;font-weight:500;letter-spacing:.08em;text-transform:uppercase;color:var(--color-text-secondary)'>Payment #</th>
                                                    <th style='padding:.55rem .75rem;text-align:left;font-size:.65rem;font-weight:500;letter-spacing:.08em;text-transform:uppercase;color:var(--color-text-secondary)'>Reason / Changes</th>
                                                    <th style='padding:.55rem .75rem;text-align:left;font-size:.65rem;font-weight:500;letter-spacing:.08em;text-transform:uppercase;color:var(--color-text-secondary)'>By</th>
                                                    <th style='padding:.55rem .75rem;text-align:left;font-size:.65rem;font-weight:500;letter-spacing:.08em;text-transform:uppercase;color:var(--color-text-secondary)'>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>{$rows}</tbody>
                                        </table>
                                    </div>
                                    <p style='font-size:.7rem;color:var(--color-text-tertiary);margin-top:.5rem'>
                                        Showing latest 50 entries from <code>loan_payment_audit_logs</code>
                                    </p>
                                ");
                            })
                            ->columnSpanFull(),

                    ]),

            ]);
    }
}