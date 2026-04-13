<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanApplicationDocument extends Model
{
     protected $primaryKey = 'loan_application_document_id';

    protected $fillable = [
        'loan_application_id',
        'code',
        'document_type',
        'is_generated',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'uploaded_by_user_id',
    ];

    protected $casts = [
        'is_generated' => 'boolean',
        'file_size' => 'integer',
    ];

    public function application()
    {
        return $this->belongsTo(LoanApplication::class, 'loan_application_id', 'loan_application_id');
    }
}
