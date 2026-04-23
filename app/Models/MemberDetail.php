<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MemberDetail extends Model
{
    // protected $primaryKey = 'member_id';

    protected $fillable = [
        'profile_id',
        'membership_type_id',
        'branch_id',
        'signature_path',
        'member_no',
        'employment_info',
        'monthly_income',
        'occupation',
        'employer_name',
        'monthly_income_range',
        'source_of_income',
        'id_type',
        'id_number',
        'emergency_full_name',
        'emergency_phone',
        'emergency_relationship',
        'dependents_count',
        'children_in_school_count',
        'house_no',
        'street_barangay',
        'municipality',
        'province',
        'zip_code',
        'years_in_business',
        'status',
        'share_capital_balance',
        'regular_at',
        'years_in_coop',
    ];

    protected $casts = [
        'share_capital_balance' => 'decimal:2',
        'regular_at' => 'datetime',
    ];

    public function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->profile?->full_name ?? 'Unknown Member'
        );
    }

    public function spouse()
    {
        return $this->hasOne(MemberSpouse::class, 'member_detail_id');
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profile_id', 'profile_id');
    }

    public function membershipType()
    {
        return $this->belongsTo(MembershipType::class, 'membership_type_id', 'membership_type_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'branch_id');
    }

    public function isRegular(): bool
    {
        return ! is_null($this->regular_at);
    }

    public function membershipStatus(): string
    {
        return $this->isRegular() ? 'Regular' : 'Associate';
    }

    public function loanApplications(): HasMany
    {
        return $this->hasMany(LoanApplication::class, 'member_id', 'id');
    }

    public function coMakers()
    {
        return $this->hasMany(MemberCoMaker::class, 'member_detail_id');
    }

    public function sharedCapitalTransactions(): HasMany
    {
        return $this->hasMany(ShareCapitalTransaction::class, 'profile_id', 'profile_id');
    }

    public function savingsAccountTransactions(): HasMany
    {
        return $this->hasmany(SavingsAccountTransaction::class, 'profile_id', 'profile_id');
    }

    public function savingsType(): HasMany
    {
        return $this->hasMany(SavingsType::class, 'savings_type_id', 'savings_type_id');
    }
}
