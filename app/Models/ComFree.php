<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComFree extends Model
{
    use HasFactory;
    protected $fillable = ['id', 'name', 'level', 'plan_id'];
    protected $table = 'comfrees';
        public function users()
    {
        return $this->hasMany(User::class); 
    }
    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }
}
