<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plandate extends Model
{
    use HasFactory;
    protected $fillable = ['date', 'score','user_id','plan_id','content_number'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
