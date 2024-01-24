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
        Schema::create('secretary_data', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('fname');
            $table->string('mname');
            $table->string('lname');
            $table->string('sex');
            $table->string('phn');
            $table->text('addr');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('secretary_data');
    }
};
