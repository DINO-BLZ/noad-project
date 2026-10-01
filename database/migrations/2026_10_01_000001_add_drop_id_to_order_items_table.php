<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('drop_id')
                ->nullable()
                ->after('order_id')
                ->constrained()
                ->restrictOnDelete();
        });

        $matches = DB::table('order_items')
            ->join('variants', 'variants.id', '=', 'order_items.variant_id')
            ->join('drop_product', 'drop_product.product_id', '=', 'variants.product_id')
            ->join('drops', 'drops.id', '=', 'drop_product.drop_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereColumn('orders.created_at', '>=', 'drops.start_date')
            ->whereColumn('orders.created_at', '<=', 'drops.end_date')
            ->select('order_items.id as order_item_id', 'drops.id as drop_id')
            ->get()
            ->groupBy('order_item_id');

        foreach ($matches as $orderItemId => $dropMatches) {
            if ($dropMatches->count() !== 1) {
                continue;
            }

            DB::table('order_items')
                ->where('id', $orderItemId)
                ->update(['drop_id' => $dropMatches->first()->drop_id]);
        }
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('drop_id');
        });
    }
};
