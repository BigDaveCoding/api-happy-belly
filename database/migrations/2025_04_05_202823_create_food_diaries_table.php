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
        Schema::create('food_diaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->text('entry');
            $table->string('meal_type');
            $table->date('entry_date');
            $table->time('entry_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_diaries');
    }
};
