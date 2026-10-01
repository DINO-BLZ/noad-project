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
            $table->unsignedBigInteger('product_id')->nullable()->after('drop_id');
            $table->index(['drop_id', 'product_id']);
        });

        DB::table('order_items')
            ->join('variants', 'variants.id', '=', 'order_items.variant_id')
            ->select('order_items.id', 'variants.product_id')
            ->orderBy('order_items.id')
            ->chunkById(500, function ($items): void {
                foreach ($items as $item) {
                    DB::table('order_items')
                        ->where('id', $item->id)
                        ->update(['product_id' => $item->product_id]);
                }
            }, 'order_items.id', 'id');
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['drop_id', 'product_id']);
            $table->dropColumn('product_id');
        });
    }
};
