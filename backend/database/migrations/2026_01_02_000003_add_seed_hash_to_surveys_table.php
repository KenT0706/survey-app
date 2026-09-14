<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            // Fingerprint of a seeded survey's question set, so seeders can
            // detect ANY content change (reworded text, edited options —
            // not just a different question count) and know to rebuild.
            $table->string('seed_hash')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->dropColumn('seed_hash');
        });
    }
};
