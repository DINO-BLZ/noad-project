<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('variants', function (Blueprint $table) {
            $table->string('sku')->nullable()->unique()->after('id');
            $table->string('color')->nullable()->after('size');

            // Empêche les doublons taille+couleur pour un même produit
            $table->unique(['product_id', 'size', 'color'], 'variants_product_size_color_unique');

            // Index pour accélérer les requêtes de stock faible / recherche
            $table->index('stock');
        });
    }

    public function down(): void
    {
        Schema::table('variants', function (Blueprint $table) {
            $table->dropUnique('variants_product_size_color_unique');
            $table->dropIndex(['stock']);
            $table->dropColumn(['sku', 'color']);
        });
    }
};