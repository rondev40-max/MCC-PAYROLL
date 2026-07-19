<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('respondent_role');
            $table->json('usability_scores');
            $table->json('efficiency_scores');
            $table->json('satisfaction_scores');
            $table->json('feedback')->nullable();

            $table->decimal('avg_usability', 4, 2)->default(0);
            $table->decimal('avg_efficiency', 4, 2)->default(0);
            $table->decimal('avg_satisfaction', 4, 2)->default(0);
            $table->decimal('overall_avg', 4, 2)->default(0);

            $table->timestamps();
            
            // Foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            
            // Prevent duplicate submissions per user
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};

