<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'business_name',
        'phone',
        'how_did_you_hear',
        'referred_by',
    ];

    /** Friend/referrer who made this user (dealer) join – gets commission on first dealer purchase */
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    /** Dealers referred by this user (when this user is the "seller" who gets commission) */
    public function referredDealers()
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }
}
