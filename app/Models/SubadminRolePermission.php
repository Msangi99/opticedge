<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubadminRolePermission extends Model
{
    protected $fillable = [
        'subadmin_role_id',
        'module',
        'action',
    ];

    public function role()
    {
        return $this->belongsTo(SubadminRole::class, 'subadmin_role_id');
    }
}
