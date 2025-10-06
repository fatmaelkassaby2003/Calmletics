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
        Schema::table('doneplans', function (Blueprint $table) {
            $table->dropColumn('feeling');
        });
    }
    
    public function down()
    {
        Schema::table('table_name', function (Blueprint $table) {
            $table->string('feeling'); // نوع العمود الأصلي
        });
    }
    
};
