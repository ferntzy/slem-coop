<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Profile;
use App\Models\User;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
// use Illuminate\Database\Eloquent\Builder;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account Details')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([

                            FileUpload::make('avatar')
                                ->label('Profile Picture')
                                ->image()
                                ->imageEditor()
                                ->imageEditorAspectRatios(['1:1'])
                                ->disk('public')
                                ->directory('avatars')
                                ->imageCropAspectRatio('1:1')
                                ->imageResizeTargetWidth(300)
                                ->imageResizeTargetHeight(300)
                                ->imagePreviewHeight('250')
                                ->columnSpan(1)
                                ->nullable(),

                            Grid::make(1)->schema([

                                TextInput::make('username')
                                    ->maxLength(45)
                                    ->required()
                                    ->columnSpan(1),

                                TextInput::make('password')
                                    ->label('Create Password')
                                    ->password()
                                    ->revealable()
                                    ->maxLength(255)
                                    ->dehydrated(fn (?string $state) => filled($state))
                                    ->required(fn (string $operation) => $operation === 'create'),

                                TextInput::make('password_confirmation')
                                    ->label('Confirm Password')
                                    ->password()
                                    ->revealable()
                                    ->same('password')
                                    ->dehydrated(false)
                                    ->required(fn (string $operation) => $operation === 'create'),

                                Select::make('profile_id')
                                    ->required()
                                    ->label('Profile')
                                    ->options(function ($record) {
                                        $usedProfileIds = User::whereNotNull('profile_id')
                                            ->when(
                                                $record?->profile_id,
                                                fn ($q) => $q->where('profile_id', '!=', $record->profile_id)
                                            )
                                            ->pluck('profile_id')
                                            ->toArray();

                                        return Profile::whereNotIn('profile_id', $usedProfileIds)
                                            ->get()
                                            ->pluck('full_name', 'profile_id');
                                    })
                                    ->searchable(),

                                Checkbox::make('is_active')
                                    ->label('Active')
                                    ->accepted(),

                            ])->columnSpan(1),

                        ]),
                    ]),
            ]);
    }
}
