<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComPre extends Model
{
    use HasFactory;
    protected $table = 'compres';

    public function users()
    {
        return $this->hasMany(User::class, 'com_pre_id', 'id'); 
    }
    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }
}
