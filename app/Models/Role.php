<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    // Use default primary key `id` created by the permissions migration
    protected $fillable = ['name'];

    public function profiles()
    {
        return $this->hasMany(Profile::class, 'roles_id', 'id');
    }
}
