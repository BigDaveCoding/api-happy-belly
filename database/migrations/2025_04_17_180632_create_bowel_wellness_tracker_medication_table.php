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
        Schema::create('bowel_wellness_tracker_medication', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bowel_wellness_tracker_id');
            $table->unsignedBigInteger('medication_id');

            $table->foreign('bowel_wellness_tracker_id', 'bwt_med_bwt_id_foreign')
                ->references('id')->on('bowel_wellness_trackers')
                ->onDelete('cascade');

            $table->foreign('medication_id')
                ->references('id')->on('medications')
                ->onDelete('cascade');

            $table->boolean('prescribed')->nullable();
            $table->time('taken_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bowel_wellness_tracker_medication');
    }
};
