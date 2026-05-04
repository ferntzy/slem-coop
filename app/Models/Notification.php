<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Notification extends Model
{
    use SoftDeletes;

    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'profile_id',
        'title',
        'description',
        'status',
        'notifiable_type',
        'notifiable_id',
        'is_read',
    ];

    protected $casts = [
        'status' => 'string',
        'is_read' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the URL to redirect to when the notification is clicked.
     * Supports LoanApplication, MembershipApplication, MemberDetail, and other resources.
     */
    public function getRedirectUrl(): ?string
    {
        if (! $this->notifiable_type || ! $this->notifiable_id) {
            return null;
        }

        $type = $this->notifiable_type;
        $id = $this->notifiable_id;

        try {
            return match ($type) {
                'loan_application' => route('filament.admin.resources.loan-applications.view', ['record' => $id]),
                'membership_application' => route('filament.admin.resources.membership-applications.view', ['record' => $id]),
                'member_detail' => route('filament.admin.resources.member-details.view', ['record' => $id]),
                'member' => route('filament.admin.resources.member-details.view', ['record' => $id]),
                'profile' => route('filament.admin.resources.profiles.view', ['record' => $id]),
                default => null,
            };
        } catch (\Exception $e) {
            Log::warning(
                "Notification::getRedirectUrl failed for type={$type}, id={$id}: {$e->getMessage()}"
            );

            return null;
        }
    }
}
