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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('content1')->nullable();
            $table->text('content2')->nullable();
            $table->text('content3')->nullable();
            $table->text('content4')->nullable();
            $table->text('content5')->nullable();
            $table->text('content6')->nullable();
            $table->text('content7')->nullable();
            $table->text('content8')->nullable();
            $table->text('content9')->nullable();
            $table->text('content10')->nullable();
            $table->text('content11')->nullable();
            $table->text('content12')->nullable();
            $table->text('content13')->nullable();
            $table->text('content14')->nullable();
            $table->text('content15')->nullable();
            $table->text('content16')->nullable();
            $table->text('content17')->nullable();
            $table->text('content18')->nullable();
            $table->text('content19')->nullable();
            $table->text('content20')->nullable();
            $table->text('content21')->nullable();
            $table->text('content22')->nullable();
            $table->text('content23')->nullable();
            $table->text('content24')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
