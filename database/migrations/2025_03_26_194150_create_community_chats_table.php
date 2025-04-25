<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('community_chats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); 
            $table->unsignedBigInteger('com_free_id')->nullable();
            $table->unsignedBigInteger('com_pre_id')->nullable();
            $table->text('message');
            $table->timestamps();
    
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('com_pre_id')->references('id')->on('compres')->onDelete('set null'); 
            $table->foreign('com_free_id')->references('id')->on('comfrees')->onDelete('set null');       
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('community_chats');
    }
};
