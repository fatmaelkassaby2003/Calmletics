<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->string('Age');
            $table->string('Years_of_Excersie_Experince');
            $table->string('Weekly_Anxiety');
            $table->string('Daily_App_Usage');
            $table->string('Comfort_in_Social_Situations');
            $table->string('Competition_Level');
            $table->string('anxiety_level')->nullable();
            $table->string('gender');
            $table->string('Current_Status');
            $table->string('Feeling_Anxious');
            $table->string('Preferred_Anxiety_Treatment');
            $table->string('Handling_Anxiety_Situations');
            $table->string('General_Mood');
            $table->string('Preferred_Content');
            $table->string('Online_Interaction_Over_Offline');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
