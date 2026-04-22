<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubadminRole extends Model
{
    protected $fillable = [
        'name',
        'system_key',
        'description',
    ];

    public function permissions()
    {
        return $this->hasMany(SubadminRolePermission::class);
    }

    public function users()
    {
        return $this->hasMany(User::class, 'subadmin_role_id');
    }
}
