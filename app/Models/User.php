<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasAvatar
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $primaryKey = 'user_id';

    public $incrementing = true;

    public function getRouteKeyName(): string
    {
        return 'encoded_id';
    }

    public function getEncodedIdAttribute(): string
    {
        return Crypt::encryptString($this->user_id);
    }

    public function resolveRouteBinding($value, $field = null): ?self
    {
        try {
            $decoded = Crypt::decryptString($value);
        } catch (DecryptException) {
            return null;
        }

        return self::where('user_id', $decoded)->first();
    }

    public function getFilamentRecordKey(): int|string
    {
        return $this->encoded_id;
    }

    protected $fillable = [
        'image_path',
        'username',
        'password',
        'profile_id',
        'coop_id',
        'avatar',
        'is_active',
        'pin',
        'temp_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];

    }

    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profile_id', 'profile_id');
    }

    public function getFilamentName(): string
    {
        return $this->profile?->full_name ?? $this->username;
    }

    public function getNameAttribute(): string
    {
        $profileName = $this->profile?->full_name;

        if (is_string($profileName) && trim($profileName) !== '') {
            return $profileName;
        }

        if (is_string($this->username) && trim($this->username) !== '') {
            return $this->username;
        }

        $key = $this->getKey();

        return $key ? ('User #'.$key) : 'User';
    }

    // public function getFilamentAvatarUrl(): ?string
    // {
    //     return $this->avatar
    //         ? Storage::disk('public')->url($this->avatar)
    //         : null;
    // }
    public function getFilamentAvatarUrl(): ?string
    {
        if (! empty($this->profile?->image_path)) {
            return Storage::url($this->profile->image_path);
        }

        if (! empty($this->avatar)) {
            return Str::startsWith($this->avatar, ['http://', 'https://'])
                ? $this->avatar
                : Storage::url($this->avatar);
        }

        return null;
    }

    public function staffDetail()
    {
        return $this->hasOne(StaffDetail::class, 'profile_id', 'profile_id');
    }

    public function branchId(): ?int
    {
        return $this->staffDetail?->branch_id;
    }

    public function roleName(): ?string
    {
        return $this->profile?->role?->name;
    }

    public function isStaff(): bool
    {
        return $this->staffDetail !== null;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('Admin');
    }

    public function isMember(): bool
    {
        return $this->hasRole('Member');
    }

    public function isBranchScoped(): bool
    {
        return ! $this->isMember()
            && ! $this->hasAnyRole(['Admin', 'super_admin', 'Librarian'])
            && $this->branchId() !== null;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function canAccessBackOffice(): bool
    {
        return ! $this->isMember();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->coop_id = self::generateCoopId();
        });
    }

    public static function generateCoopId(): string
    {
        $prefix = 'COOP';
        $year = now()->format('Y');

        $last = DB::table('users')
            ->where('coop_id', 'like', "{$prefix}-{$year}-%")
            ->orderByDesc('coop_id')
            ->value('coop_id');

        $sequence = $last
            ? (int) str($last)->afterLast('-')->value() + 1
            : 1;

        return sprintf('%s-%s-%03d', $prefix, $year, $sequence);
    }
}
