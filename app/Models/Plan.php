<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'level',];

    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

    public function comfree()
    {
        return $this->hasOne(ComFree::class ); 
    }

    public function compre()
    {
        return $this->hasOne(compre::class  ); 
    }
}
