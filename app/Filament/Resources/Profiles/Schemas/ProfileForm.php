<?php

namespace App\Filament\Resources\Profiles\Schemas;

use App\Models\Branch;
use App\Models\Role;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;

class ProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('first_name')
                    ->required()
                    ->maxLength(100),

                TextInput::make('middle_name')
                    ->maxLength(45),

                TextInput::make('last_name')
                    ->required()
                    ->maxLength(45),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),

                TextInput::make('mobile_number')
                    ->label('Mobile Number')
                    ->placeholder('09XXXXXXXXX')
                    ->maxLength(11)
                    ->rules(['nullable', 'regex:/^09[0-9]{9}$/'])
                    ->validationMessages([
                        'regex' => 'Mobile number must be a valid PH number starting with 09 and exactly 11 digits (e.g. 09123456789).',
                    ])
                    ->extraInputAttributes([
                        'inputmode' => 'numeric',
                        'pattern' => '09[0-9]{9}',
                        'x-on:keypress' => 'if(!/[0-9]/.test($event.key)) $event.preventDefault()',
                        'x-on:input' => '$event.target.value = $event.target.value.replace(/[^0-9]/g, "").slice(0, 11)',
                        'x-on:paste' => '$event.preventDefault()',
                    ]),

             DatePicker::make('birthdate')
                        ->label('Birthdate')
                        ->required()
                        ->native(false)
                        ->displayFormat('F d, Y')
                        ->placeholder('Select your birthdate')
                        ->prefixIcon('heroicon-o-calendar')
                        ->closeOnDateSelection()
                        ->defaultFocusedDate(now()->subYears(25))
                        ->maxDate(today()->subYears(18))
                        ->rules([
                            function () {
                                return function (string $attribute, mixed $value, \Closure $fail) {
                                    if (! $value) return;

                                    $birthdate = Carbon::parse($value);

                                    if ($birthdate->isToday() || $birthdate->isFuture()) {
                                        $fail('Birthdate cannot be today or a future date.');
                                        return;
                                    }

                                    $age = $birthdate->age;
                                    if ($age < 18) {
                                        $fail("You must be at least 18 years old. Current age: {$age} years.");
                                    }
                                };
                            },
                        ]),

                Select::make('sex')
                    ->options([
                        'Male' => 'Male',
                        'Female' => 'Female',
                    ]),

                Select::make('civil_status')
                    ->options([
                        'Single' => 'Single',
                        'Married' => 'Married',
                        'Widowed' => 'Widowed',
                        'Separated' => 'Separated',
                    ]),

                TextInput::make('tin')
                    ->label('TIN')
                    ->maxLength(45),

                TextInput::make('address')
                    ->maxLength(255),

                Select::make('roles_id')
                    ->label('Role')
                    ->relationship('role', 'name')
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->required(),

                Select::make('branch_id')
                    ->label('Branch')
                    ->options(fn (): array => Branch::where('is_active', true)->orderBy('name')->pluck('name', 'branch_id')->toArray())
                    ->default(fn (): ?int => auth()->user()?->branchId())
                    ->searchable()
                    ->preload()
                    ->visible(fn (callable $get): bool => in_array($get('roles_id'), self::branchScopedRoleIds(), true))
                    ->required(fn (callable $get): bool => in_array($get('roles_id'), self::branchScopedRoleIds(), true)),

                TextColumn::make('system_roles')
                    ->label('System Role')
                    ->badge(),
            ]);
    }

    protected static function branchScopedRoleIds(): array
    {
        return Role::whereIn('name', [
            'Manager',
            'Staff',
            'Cashier',
            'Account Officer',
            'Loan Officer',
            'Teller',
        ])->pluck('id')->toArray();
    }
}
