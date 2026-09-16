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
        // 1. Create user_answer_options pivot table if not exists
        if (!Schema::hasTable('user_answer_options')) {
            Schema::create('user_answer_options', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_answer_id')->constrained('user_answers')->cascadeOnDelete();
                $table->foreignId('option_id')->constrained('options')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['user_answer_id', 'option_id']);
            });
        }

        // 2. Migrate existing data from user_answers.option_id to user_answer_options
        if (Schema::hasColumn('user_answers', 'option_id')) {
            $existingAnswers = DB::table('user_answers')
                ->whereNotNull('option_id')
                ->get(['id', 'option_id', 'created_at', 'updated_at']);

            $rowsToInsert = [];
            foreach ($existingAnswers as $ans) {
                $rowsToInsert[] = [
                    'user_answer_id' => $ans->id,
                    'option_id' => $ans->option_id,
                    'created_at' => $ans->created_at ?? now(),
                    'updated_at' => $ans->updated_at ?? now(),
                ];
            }

            if (!empty($rowsToInsert)) {
                DB::table('user_answer_options')->insert($rowsToInsert);
            }

            // 3. Drop option_id column cleanly across SQLite and MySQL
            if (DB::getDriverName() === 'sqlite') {
                Schema::disableForeignKeyConstraints();

                Schema::create('user_answers_new', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('exam_session_id')->constrained('exam_sessions')->cascadeOnDelete();
                    $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
                    $table->boolean('is_doubt')->default(false);
                    $table->timestamps();

                    $table->unique(['exam_session_id', 'question_id']);
                });

                DB::statement('INSERT INTO user_answers_new (id, exam_session_id, question_id, is_doubt, created_at, updated_at) SELECT id, exam_session_id, question_id, is_doubt, created_at, updated_at FROM user_answers');

                Schema::drop('user_answers');
                DB::statement('ALTER TABLE user_answers_new RENAME TO user_answers');

                Schema::enableForeignKeyConstraints();
            } else {
                Schema::table('user_answers', function (Blueprint $table) {
                    $table->dropForeign(['option_id']);
                    $table->dropColumn('option_id');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('user_answers', 'option_id')) {
            Schema::table('user_answers', function (Blueprint $table) {
                $table->foreignId('option_id')->nullable()->after('question_id')->constrained('options')->cascadeOnDelete();
            });

            // Migrate data back
            $options = DB::table('user_answer_options')->get();
            foreach ($options as $opt) {
                DB::table('user_answers')
                    ->where('id', $opt->user_answer_id)
                    ->update(['option_id' => $opt->option_id]);
            }
        }

        Schema::dropIfExists('user_answer_options');
    }
};
