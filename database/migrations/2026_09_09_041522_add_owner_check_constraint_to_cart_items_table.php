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

        DB::statement('
            ALTER TABLE cart_items
            ADD CONSTRAINT chk_cart_items_owner CHECK (
                (user_id IS NOT NULL AND session_id IS NULL)
                OR
                (user_id IS NULL AND session_id IS NOT NULL)
            )
        ');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE cart_items DROP CONSTRAINT chk_cart_items_owner');
    }
};