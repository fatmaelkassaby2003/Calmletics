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
            $table->text('content1');
            $table->text('content2');
            $table->text('content3');
            $table->text('content4');
            $table->text('content5');
            $table->text('content6');
            $table->text('content7');
            $table->text('content8');
            $table->text('content9');
            $table->text('content10');
            $table->text('content11');
            $table->text('content12');
            $table->text('content13');
            $table->text('content14');
            $table->text('content15');
            $table->text('content16');
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
