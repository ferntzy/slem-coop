<?php

namespace App\Filament\Widgets;

use App\Models\Profile;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class MemberGreetingWidget extends Widget
{
    protected string $view = 'filament.widgets.member-greeting-widget';

    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::user()->isMember();
    }

    public function getFullName(): string
    {
        $user = Auth::user();
        $profile = Profile::where('profile_id', $user->profile_id)->first();

        return $profile?->full_name ?? $user->username;
    }

    public function getTimeGreeting(): string
    {
        $hour = now()->hour;

        return match (true) {
            $hour >= 5 && $hour < 12 => 'Good morning ☀️',
            $hour >= 12 && $hour < 17 => 'Good afternoon 🌤️',
            $hour >= 17 && $hour < 21 => 'Good evening 🌇',
            default => 'Good night 🌙',
        };
    }

    public function getInitials(): string
    {
        $name = $this->getFullName();
        $parts = explode(' ', trim($name));

        if (count($parts) >= 2) {
            return strtoupper(substr($parts[0], 0, 1).substr(end($parts), 0, 1));
        }

        return strtoupper(substr($name, 0, 2));
    }

    public function getLoanApplicationUrl(): string
    {
        return route('filament.admin.resources.loan-applications.create');
    }
}
