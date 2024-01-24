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
        Schema::create('diocese_basic_data', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('name');
            $table->string('phn');
            $table->string('pwd');
            $table->string('verif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diocese_basic_data');
    }
};
