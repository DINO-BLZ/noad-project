<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE drops ADD CONSTRAINT chk_drops_end_after_start CHECK (end_date > start_date)');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE drops DROP CHECK chk_drops_end_after_start');
    }
};
