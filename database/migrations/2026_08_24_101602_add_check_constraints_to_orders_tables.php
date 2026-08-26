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

        DB::statement('ALTER TABLE orders ADD CONSTRAINT chk_orders_total_positive CHECK (total >= 0)');
        DB::statement('ALTER TABLE order_items ADD CONSTRAINT chk_order_items_quantity_positive CHECK (quantity > 0)');
        DB::statement('ALTER TABLE order_items ADD CONSTRAINT chk_order_items_price_positive CHECK (price >= 0)');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE orders DROP CHECK chk_orders_total_positive');
        DB::statement('ALTER TABLE order_items DROP CHECK chk_order_items_quantity_positive');
        DB::statement('ALTER TABLE order_items DROP CHECK chk_order_items_price_positive');
    }
};
