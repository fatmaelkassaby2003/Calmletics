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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role');
            $table->float('score')->default(0);
            $table->string('code')->nullable()->unique();
            $table->unsignedBigInteger('com_free_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->foreign('com_free_id')->references('id')->on('com_free')->onDelete('set null'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        // $table->dropForeign(['com_free_id']); 
        // $table->dropColumn('com_free_id'); 
    }
};
