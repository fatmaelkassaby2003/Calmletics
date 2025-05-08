<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComPre extends Model
{
    use HasFactory;
    protected $table = 'compres';

    protected $fillable = ['id', 'name', 'level', 'code', 'plan_id', 'user_id','community_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function users()
    {
        return $this->hasMany(User::class, 'com_pre_id', 'id'); 
    }
    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }
}
