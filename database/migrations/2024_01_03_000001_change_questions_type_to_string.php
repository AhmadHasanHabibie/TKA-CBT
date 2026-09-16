<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::disableForeignKeyConstraints();

            Schema::create('questions_temp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('subtest_id')->constrained('subtests')->cascadeOnDelete();
                $table->integer('number');
                $table->string('type', 20)->default('single');
                $table->text('text');
                $table->text('explanation')->nullable();
                $table->timestamps();
            });

            DB::statement('INSERT INTO questions_temp (id, subtest_id, number, type, text, explanation, created_at, updated_at) SELECT id, subtest_id, number, COALESCE(type, "single"), text, explanation, created_at, updated_at FROM questions');

            Schema::drop('questions');
            DB::statement('ALTER TABLE questions_temp RENAME TO questions');

            Schema::enableForeignKeyConstraints();
        } else {
            DB::statement("ALTER TABLE questions MODIFY COLUMN type VARCHAR(20) NOT NULL DEFAULT 'single'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // Keep as string in SQLite to prevent rollback corruption
        } else {
            DB::statement("ALTER TABLE questions MODIFY COLUMN type ENUM('single', 'multiple') NOT NULL DEFAULT 'single'");
        }
    }
};
