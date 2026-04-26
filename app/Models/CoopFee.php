<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoopFee extends Model
{
    use HasFactory;

    public const TYPE_SHARED_CAPITAL = 'shared_capital';

    public const TYPE_INSURANCE = 'insurance';

    public const TYPE_PROCESSING_FEE = 'processing_fee';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'coop_fee_type_id',
        'type',
        'name',
        'amount',
        'percentage',
        'is_percentage',
        'description',
        'status',
        'group',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'percentage' => 'decimal:2',
        'is_percentage' => 'boolean',
    ];

    protected $appends = ['display_value', 'type_label', 'status_label'];

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeGroupCoopFees($query)
    {
        return $query->where('group', 'Coop Fees');
    }

    public function feeType(): BelongsTo
    {
        return $this->belongsTo(CoopFeeType::class, 'coop_fee_type_id');
    }

    protected function displayValue(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->is_percentage
                ? $this->percentage.'%'
                : '₱'.number_format((float) $this->amount, 2)
        );
    }

    protected function typeLabel(): Attribute
    {
        $typeLabels = self::getFeeTypes();

        return Attribute::make(
            get: fn () => $typeLabels[$this->type] ?? ucwords(str_replace('_', ' ', (string) $this->type))
        );
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status === self::STATUS_ACTIVE ? 'Active' : 'Inactive'
        );
    }

    public static function getFeeTypes(): array
    {
        return [
            self::TYPE_SHARED_CAPITAL => 'Shared Capital',
            self::TYPE_INSURANCE => 'Insurance',
            self::TYPE_PROCESSING_FEE => 'Processing Fee',
        ];
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
        ];
    }

    protected static function booted()
    {
        static::creating(function (self $fee) {
            if (empty($fee->group)) {
                $fee->group = 'Coop Fees';
            }
        });
    }
}
