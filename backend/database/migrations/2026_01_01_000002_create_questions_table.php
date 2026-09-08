<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
            // text/textarea = open-ended. single_choice/multiple_choice/rating = closed-ended.
            $table->enum('type', ['text', 'textarea', 'single_choice', 'multiple_choice', 'rating']);
            $table->string('question_text');
            $table->boolean('is_required')->default(true);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
