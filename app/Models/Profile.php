<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Spatie\Permission\Traits\HasRoles;

class Profile extends Model
{
    public function getRouteKeyName(): string
    {
        return 'encoded_id';
    }

    public function getEncodedIdAttribute(): string
    {
        return Crypt::encryptString($this->profile_id);
    }

    public function resolveRouteBinding($value, $field = null): ?self
    {
        $decoded = Crypt::decryptString($value);

        return self::where('profile_id', $decoded)->first();
    }

    public function getFilamentRecordKey(): int|string
    {
        return $this->encoded_id;
    }

    use HasRoles;

    protected $primaryKey = 'profile_id';

    protected $keyType = 'int';

    public $incrementing = true;

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'mobile_number',
        'birthdate',
        'sex',
        'civil_status',
        'tin',
        'address',
        'branch_id',
        'roles_id',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'profile_id', 'profile_id');
    }

    public function savingsAccountTransaction()
    {
        return $this->hasMany(SavingsAccountTransaction::class, 'profile_id', 'profile_id');
    }

    public function memberDetail()
    {
        return $this->hasOne(MemberDetail::class, 'profile_id', 'profile_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'branch_id');
    }

    public function membershipApplications()
    {
        return $this->hasMany(MembershipApplication::class, 'profile_id', 'profile_id');
    }

    public function staffDetail()
    {
        return $this->hasOne(StaffDetail::class, 'profile_id', 'profile_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'roles_id', 'id');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }

    public function shareCapitalTransactions()
    {
        return $this->hasMany(ShareCapitalTransaction::class, 'profile_id', 'profile_id');
    }

    public function scopeApproved($query)
    {
        return $query->whereHas('membershipApplications', function ($subQuery) {
            $subQuery->where('status', 'approved');
        })->orWhereDoesntHave('membershipApplications');
    }
}
