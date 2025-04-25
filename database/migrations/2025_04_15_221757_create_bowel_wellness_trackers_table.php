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
        Schema::create('bowel_wellness_trackers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->ondelete('cascade');
            $table->date('date');
            $table->time('time');
            $table->tinyInteger('stool_type')->unsigned();
            $table->tinyInteger('urgency')->unsigned()->nullable();
            $table->tinyInteger('pain')->unsigned()->nullable();
            $table->boolean('blood')->nullable();
            $table->integer('blood_amount')->nullable();
            $table->tinyInteger('stress_level')->nullable();
            $table->tinyInteger('hydration_level')->nullable();
            $table->boolean('recent_meal')->nullable()->comment('for if user has had meal within last 2 hours');
            $table->string('color')->nullable()->comment('helpful info for diagnosing');
            $table->text('additional_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bowel_wellness_trackers');
    }
};
