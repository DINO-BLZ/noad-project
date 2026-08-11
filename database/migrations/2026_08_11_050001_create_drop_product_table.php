<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drop_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['drop_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drop_product');
    }
};
