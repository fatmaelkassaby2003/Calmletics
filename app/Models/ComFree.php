<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComFree extends Model
{
    use HasFactory;

    public function users()
    {
        return $this->hasMany(User::class, 'com_free_id'); 
    }
}
