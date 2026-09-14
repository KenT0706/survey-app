<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // Optional grouping label shown as a heading above the question,
            // e.g. "Part 1 — About You" or "Part 3 — Stability".
            $table->text('section')->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('section');
        });
    }
};
