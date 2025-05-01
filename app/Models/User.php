<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function comFree()
    {
        return $this->belongsTo(ComFree::class);
    }

    public function planDates()
    {
        return $this->belongsTo(planDate::class);
    }

    public function answers() {
        return $this->hasOne(Answer::class);
    } 

    public function comPre()
    {
        return $this->belongsTo(ComPre::class, 'com_pre_id', 'id');
    }

    public function sessions()
    {
        return $this->belongsToMany(Session::class)->withPivot('created_at');
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    public function plan()
    {
        return $this->belongsTo(plan::class, 'plan_id', 'id');
    }

}
