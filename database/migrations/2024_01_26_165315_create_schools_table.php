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
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('diocese_id');
            $table->string('name');
            $table->string('type');
            $table->text('lea');
            $table->string('addr');
            $table->string('email');
            $table->string('phone');
            $table->string('p_name');
            $table->string('p_email');
            $table->string('p_phone');
            $table->timestamps();

            // For queries based on diocese_id
            $table->index('diocese_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
