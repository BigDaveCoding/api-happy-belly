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
        Schema::create('dietary_restrictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id');
            $table->boolean('is_vegetarian');
            $table->boolean('is_vegan');
            $table->boolean('is_gluten_free');
            $table->boolean('is_dairy_free');
            $table->boolean('is_low_fodmap');
            $table->boolean('is_ostomy_friendly');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dietary_restrictions');
    }
};
