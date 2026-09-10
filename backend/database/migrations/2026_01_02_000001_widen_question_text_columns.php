<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Some trilingual question/option text exceeds Postgres's default
        // varchar(255) limit — SQLite has no real type enforcement, so this
        // only matters on pgsql. Raw SQL avoids needing doctrine/dbal just
        // for a column-type change.
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE questions ALTER COLUMN question_text TYPE TEXT');
            DB::statement('ALTER TABLE question_options ALTER COLUMN option_text TYPE TEXT');
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE questions ALTER COLUMN question_text TYPE VARCHAR(255)');
            DB::statement('ALTER TABLE question_options ALTER COLUMN option_text TYPE VARCHAR(255)');
        }
    }
};
