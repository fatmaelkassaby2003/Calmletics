<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'plan_id','content','type','task','practical','type_ai'];



    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
