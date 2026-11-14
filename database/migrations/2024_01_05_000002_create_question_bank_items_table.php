<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('question_bank_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_bank_id')->constrained('question_banks')->cascadeOnDelete();
            $table->string('image'); 
            $table->string('title')->nullable(); 
            $table->text('notes')->nullable(); 
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

public function down(): void
    {
        Schema::dropIfExists('question_bank_items');
    }
};
