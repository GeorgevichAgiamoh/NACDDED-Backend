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
        Schema::create('pays9', function (Blueprint $table) {
            $table->id();
            $table->string('diocese_id');
            $table->string('ref');
            $table->string('type');
            $table->string('name');
            $table->string('time');
            $table->string('proof');
            $table->integer('amt');
            $table->string('meta');
            $table->timestamps();

            // Index on 'amt' to make summing faster
            $table->index('amt');
            // For queries based on diocese_id
            $table->index('diocese_id');
        });
        DB::statement('ALTER TABLE pays9 ADD FULLTEXT INDEX pays9_fulltext (name, ref, diocese_id)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pays9');
    }
};
