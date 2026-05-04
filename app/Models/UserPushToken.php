<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPushToken extends Model
{
    protected $table = 'user_push_tokens';

    protected $fillable = [
        'user_id',
        'push_token',
        'device_type',
        'is_active',
        'last_used_at',
        'device_name',
        'app_version',
        'os_version',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function touchLastUsed()
    {
        $this->last_used_at = now();

        return $this->save();
    }

    public function deactivate()
    {
        $this->is_active = false;

        return $this->save();
    }
}
