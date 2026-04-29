<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MemberPasswordPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationLabel = 'Change Password';

    protected static ?string $title = 'Change Password';

    protected static ?string $slug = 'change-password';

    protected static string|\UnitEnum|null $navigationGroup = 'Member Account';

    protected static ?int $navigationSort = 999;

    protected string $view = 'filament.pages.member-password-page';

    public array $data = [];

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Update your password')
                ->description('Use the temporary password from your approval email, then replace it with a new one here.')
                ->schema([
                    TextInput::make('current_password')
                        ->label('Current Password')
                        ->password()
                        ->required()
                        ->revealable(),

                    TextInput::make('password')
                        ->label('New Password')
                        ->password()
                        ->required()
                        ->revealable(),

                    TextInput::make('password_confirmation')
                        ->label('Confirm New Password')
                        ->password()
                        ->required()
                        ->revealable(),
                ])
                ->columns(1),
        ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Change Password')
                ->icon('heroicon-o-key')
                ->color('primary')
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $data = Validator::make($this->form->getState(), [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ])->validate();

        $user = Auth::user();

        $user->forceFill([
            'password' => $data['password'],
            'must_change_password' => false,
        ])->save();

        $this->form->fill([
            'current_password' => '',
            'password' => '',
            'password_confirmation' => '',
        ]);

        Notification::make()
            ->title('Password updated')
            ->body('Your new password is now active.')
            ->success()
            ->send();
    }
}
