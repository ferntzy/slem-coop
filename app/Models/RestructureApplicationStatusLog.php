<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestructureApplicationStatusLog extends Model
{
    protected $fillable = [
        'restructure_application_id',
        'from_status',
        'to_status',
        'changed_by_user_id',
        'reason',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function restructureApplication()
    {
        return $this->belongsTo(RestructureApplication::class, 'restructure_application_id', 'restructure_application_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
