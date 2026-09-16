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
        Schema::create('exam_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subtest_id')->constrained('subtests')->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('ends_at');
            $table->timestamp('finished_at')->nullable();
            $table->enum('status', ['ongoing', 'finished'])->default('ongoing');
            $table->decimal('score', 5, 2)->nullable();
            $table->unsignedInteger('correct_count')->nullable();
            $table->unsignedInteger('tab_violation_count')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'subtest_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_sessions');
    }
};
