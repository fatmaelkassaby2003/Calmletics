<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'Age',
        'Years_of_Excersie_Experince',
        'Weekly_Anxiety',
        'Daily_App_Usage',
        'Comfort_in_Social_Situations',
        'Competition_Level',
        'anxiety_level',
        'gender',
        'Current_Status',
        'Feeling_Anxious',
        'Preferred_Anxiety_Treatment',
        'Handling_Anxiety_Situations',
        'General_Mood',
        'Preferred_Content',
        'Online_Interaction_Over_Offline',
        'user_id',
    ];

    public function users()
    {
        return $this->belongsTo(User::class);    
    }
}
