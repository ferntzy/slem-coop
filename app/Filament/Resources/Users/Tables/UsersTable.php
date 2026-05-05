<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\Profile;
use DB;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB as FacadesDB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // Avatar: use image_path from users table, fallback to initials from profile
                ViewColumn::make('avatar_display')
                    ->label('Avatar')
                    ->view('filament.tables.columns.avatar-with-initials')
                    ->state(function ($record) {
                        $imagePath = $record->image_path ?? null;

                        // Build full URL if image_path exists on the user
                        $avatarUrl = null;
                        if ($imagePath) {
                            // Handle both absolute URLs and relative paths
                            $avatarUrl = Str::startsWith($imagePath, ['http://', 'https://'])
                                ? $imagePath
                                : url($imagePath); // image_path is stored as "images/avatar_x.jpg"
                        }

                        // Fallback initials from profile
                        $firstName = $record->profile?->first_name ?? '';
                        $lastName  = $record->profile?->last_name  ?? '';
                        $initials  = strtoupper(
                            substr($firstName, 0, 1) . substr($lastName, 0, 1)
                        ) ?: '?';

                        return [
                            'url'      => $avatarUrl,
                            'initials' => $initials,
                        ];
                    }),

                TextColumn::make('coop_id')
                    ->label('Coop ID')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('username')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('profile.full_name')
                    ->label('Full Name')
                    ->searchable()
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy(
                            Profile::select(FacadesDB::raw("CONCAT(first_name, ' ', middle_name, ' ', last_name)"))
                                ->whereColumn('profiles.profile_id', 'users.profile_id')
                                ->limit(1),
                            $direction
                        );
                    }),

                ImageColumn::make('qr_code')
                    ->label('QR Code')
                    ->disk('public')
                    ->width(80)
                    ->height(80)
                    ->url(fn ($record) => $record->qr_code ? Storage::url($record->qr_code) : null),

                CheckboxColumn::make('is_active')
                    ->label('Active')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('enlarge_qr')
                    ->label('Print QR')
                    ->icon('heroicon-o-magnifying-glass-plus')
                    ->color('gray')
                    ->modalHeading(fn ($record) => $record->username . ' — QR Code')
                    ->modalContent(function ($record): HtmlString {
                        if (! $record->qr_code) {
                            return new HtmlString('
                                <div style="text-align:center; padding:2rem; color:#94a3b8;">
                                    No QR Code available.
                                </div>
                            ');
                        }

                        $url = Storage::url($record->qr_code);

                        return new HtmlString("
                            <div style='display:flex; flex-direction:column; align-items:center; gap:1rem; padding:1.5rem;'>
                                <img src='{$url}'
                                     width='300'
                                     height='300'
                                     style='border-radius:12px; border:2px solid #e2e8f0; padding:12px; box-shadow:0 4px 24px rgba(0,0,0,.1);' />
                                <a href='{$url}'
                                   download='qrcode_{$record->username}.svg'
                                   style='background:#0d9488; color:#fff; padding:.5rem 1.25rem; border-radius:99px; font-size:.85rem; text-decoration:none; font-weight:600;'>
                                    Download QR Code
                                </a>
                            </div>
                        ");
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}