<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanType extends Model
{
    protected $table = 'loan_types';

    protected $primaryKey = 'loan_type_id';

    protected $fillable = [
        'name',
        'description',
        'max_interest_rate',
        'max_term_months',
        'max_amount',
        'min_amount',
        'amount_calculation_type',
        'amount_multiplier',
        'requires_collateral',
        'collateral_threshold',
        'is_active',
    ];

    protected $casts = [
        'requires_collateral' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function requirements()
    {
        return $this->hasMany(LoanTypeRequirement::class, 'loan_type_id', 'loan_type_id')
            ->orderBy('sort_order');
    }
}
