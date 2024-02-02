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
        Schema::create('event_regs', function (Blueprint $table) {
            $table->id();
            $table->string('event_id');
            $table->string('diocese_id');
            $table->string('proof');
            $table->string('verif');
            $table->timestamps();

            // For queries based on event_id
            $table->index('event_id');
            
            // For queries based on diocese_id
            $table->index('diocese_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_regs');
    }
};
