<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return; // SQLite ne supporte pas l'ajout de CHECK via ALTER TABLE
        }

        DB::statement('ALTER TABLE products ADD CONSTRAINT chk_products_price_positive CHECK (price >= 0)');
        DB::statement('ALTER TABLE variants ADD CONSTRAINT chk_variants_stock_positive CHECK (stock >= 0)');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE products DROP CHECK chk_products_price_positive');
        DB::statement('ALTER TABLE variants DROP CHECK chk_variants_stock_positive');
    }
};