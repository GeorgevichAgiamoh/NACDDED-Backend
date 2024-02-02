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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->bigInteger('time');
            $table->text('venue');
            $table->string('fee');
            $table->bigInteger('start');
            $table->bigInteger('end');
            $table->text('theme');
            $table->text('speakers');
            $table->timestamps();

            // For queries based on time
            $table->index('time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
