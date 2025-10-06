<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'code',
        'role',
        'score',
        'flag',
        'image',
        'com_free_id',
        'com_pre_id',
        'plan_id',
        'percentage'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Automatically update plan_id based on com_pre_id or com_free_id.
     */
    protected static function booted()
    {
        static::saving(function ($user) {
            if ($user->com_pre_id) {
                $comPre = \App\Models\ComPre::find($user->com_pre_id);
                if ($comPre) {
                    $user->plan_id = $comPre->plan_id;
                }
            } elseif ($user->com_free_id) {
                $comFree = \App\Models\ComFree::find($user->com_free_id);
                if ($comFree) {
                    $user->plan_id = $comFree->plan_id;
                }
            }
        });
    }

    // Relationships
    public function comPre()
    {
        return $this->belongsTo(ComPre::class, 'com_pre_id', 'id');
    }

    public function comFree()
    {
        return $this->belongsTo(ComFree::class, 'com_free_id', 'id');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'id');
    }

    public function planDates()
    {
        return $this->belongsTo(planDate::class);
    }

    public function answers()
    {
        return $this->hasOne(Answer::class);
    }

    public function sessions()
    {
        return $this->belongsToMany(Session::class)->withPivot('created_at');
    }

    // JWT
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
