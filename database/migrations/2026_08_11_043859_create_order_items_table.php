<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            // Keep variant_id nullable and null on delete to preserve order history
            $table->foreignId('variant_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            // Snapshot fields to keep historical info even if variant/product removed
            $table->string('variant_sku')->nullable();
            $table->string('variant_size')->nullable();
            $table->string('variant_color')->nullable();
            $table->string('product_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
