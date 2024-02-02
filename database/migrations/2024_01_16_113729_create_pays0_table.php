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
        Schema::create('pays0', function (Blueprint $table) {
            $table->id();
            $table->string('diocese_id');
            $table->string('ref');
            $table->string('name');
            $table->string('time');
            $table->string('year');
            $table->integer('amt');
            $table->timestamps();

            // Index on 'amt' to make summing faster
            $table->index('amt');
            // For queries based on diocese_id
            $table->index('diocese_id');
        });

        // Add full-text index on multiple columns
        DB::statement('ALTER TABLE pays0 ADD FULLTEXT INDEX pays0_fulltext (name, ref, diocese_id)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pays0');
    }
};
