<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('diocese_basic_data', function (Blueprint $table) {
            $table->string('diocese_id')->primary();
            $table->string('name');
            $table->string('phn');
            $table->string('verif');
            $table->timestamps();

            // For queries based on verif
            $table->index('verif');
        });
        // Add full-text index on multiple columns
        DB::statement('ALTER TABLE diocese_basic_data ADD FULLTEXT INDEX dbi_fulltext (name, phn)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diocese_basic_data');
    }
};
