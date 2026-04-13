<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanTypeRequirement extends Model
{
     protected $table = 'loan_type_requirements';
    protected $primaryKey = 'loan_type_requirement_id';

    protected $fillable = [
        'loan_type_id',
        'code',
        'label',
        'is_required',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function loanType()
    {
        return $this->belongsTo(LoanType::class, 'loan_type_id', 'loan_type_id');
    }
}
