<?php

namespace App\Filament\Widgets;

use App\Models\Profile;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class LoanOfficerGreetingWidget extends Widget
{
    protected string $view = 'filament.widgets.loan-officer-greeting-widget';

    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = Auth::user();

        if (! $user || $user->isMember() || $user->isAdminOrSuperAdmin()) {
            return false;
        }

        return static::isLoanOfficerUser($user);
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
            $hour >= 5 && $hour < 12 => 'Good morning',
            $hour >= 12 && $hour < 17 => 'Good afternoon',
            $hour >= 17 && $hour < 21 => 'Good evening',
            default => 'Good night',
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

    public function getLoanQueueUrl(): string
    {
        return route('filament.admin.resources.loan-applications.index');
    }

    protected static function isLoanOfficerUser(User $user): bool
    {
        return $user->hasAnyRole([
            'loan_officer',
            'Loan Officer',
            'hq_loan_officer',
            'HQ Loan Officer',
            'loan_manager',
            'Loan Manager',
            'credit_committee',
            'Credit Committee',
        ]);
    }
}
