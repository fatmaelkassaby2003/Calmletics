<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunityChat extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'com_free_id', 'com_pre_id', 'message'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comfree()
    {
        return $this->belongsTo(ComFree::class, 'com_free_id');
    }

    public function compre()
    {
        return $this->belongsTo(ComPre::class, 'com_pre_id');
    }
}