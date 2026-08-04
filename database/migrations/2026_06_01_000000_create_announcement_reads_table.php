<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tracks which announcement each individual employee has read.
 *
 * The original `announcements.is_read` column was a single global flag —
 * one employee opening an announcement would mark it "read" for every
 * other employee too. This pivot table makes read-state per-employee,
 * the way the Employee Portal UI (unread badge / "NEW" tag) already
 * assumes it works.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcement_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('employee_id');
            $table->timestamp('read_at')->useCurrent();
            $table->timestamps();

            $table->unique(['announcement_id', 'employee_id']);
            $table->index('employee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_reads');
    }
};
