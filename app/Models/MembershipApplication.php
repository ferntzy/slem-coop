<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MembershipApplication extends Model
{
    use HasFactory;

    protected $table = 'membership_applications';

    protected $primaryKey = 'id';

    protected $fillable = [
        'profile_id',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'mobile_number',
        'birthdate',
        'sex',
        'civil_status',
        'address',
        'membership_type_id',
        'application_date',
        'status',
        'approved_at',
        'rejected_at',
        'id_document',
        'id_document_front',
        'id_document_back',
        'proof_of_income',
        'other_documents',
        'remarks',
        'created_by',
        'updated_by',

        'id_type',
        'id_number',
        'house_no',
        'street_barangay',
        'municipality',
        'province',
        'zip_code',
        'occupation',
        'employer_name',
        'monthly_income_range',
        'source_of_income',
        'monthly_income',
        'years_in_business',
        'emergency_full_name',
        'emergency_phone',
        'emergency_relationship',
        'dependents_count',
        'children_in_school_count',

        'orientation_zoom_attended',
        'orientation_video_completed',
        'orientation_assessment_passed',
        'orientation_certificate_generated',

        'signature_path',
        'orientation_score',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'application_date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'other_documents' => 'array',
    ];

    // Relationships
    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profile_id', 'profile_id');
    }

    public function membershipType()
    {
        return $this->belongsTo(MembershipType::class, 'membership_type_id', 'membership_type_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by', 'user_id');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            if (! $model->created_by) {
                $model->created_by = auth()->user()?->user_id ?? 1;
            }
        });

        static::updating(function (self $model) {
            if (! $model->updated_by) {
                $model->updated_by = auth()->user()?->user_id ?? 1;
            }
        });
    }

    public function save(array $options = [])
    {
        if ($this->isDirty('status') && $this->status === 'approved' && ! $this->profile_id) {

            return DB::transaction(function () use ($options) {
                $role = Role::where('name', 'Member')->firstOrFail();

                $profile = Profile::create([
                    'first_name' => $this->first_name,
                    'middle_name' => $this->middle_name,
                    'last_name' => $this->last_name,
                    'email' => $this->email,
                    'mobile_number' => $this->mobile_number,
                    'birthdate' => $this->birthdate,
                    'sex' => $this->sex,
                    'address' => $this->address,
                    'roles_id' => $role->id,
                ]);

                $this->profile_id = $profile->profile_id;
                $this->approved_at = Carbon::now();

                return parent::save($options);
            });
        }

        return parent::save($options);
    }

    public function orientationAssessments()
    {
        return $this->hasMany(OrientationAssessment::class);
    }

    public function latestAssessment()
    {
        return $this->hasOne(OrientationAssessment::class)->latestOfMany();
    }

    public function hasPassedOrientation(): bool
    {
        return $this->orientationAssessments()->where('passed', true)->exists();
    }

    public function canRetakeOrientation(): bool
    {
        $orientation = Orientation::getActive();
        if (! $orientation || ! $orientation->allow_retakes) {
            return false;
        }
        if (is_null($orientation->max_attempts)) {
            return true;
        } // unlimited

        $attempts = $this->orientationAssessments()
            ->where('orientation_id', $orientation->id)
            ->count();

        return $attempts < $orientation->max_attempts;
    }
}
