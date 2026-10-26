<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('commune', 100)->nullable()->after('wilaya');
            $table->string('delivery_type', 20)->default('home')->after('commune'); // 'home' ou 'desk'
            $table->decimal('shipping_cost', 10, 2)->default(0)->after('delivery_type');
            $table->string('tracking_number', 100)->nullable()->after('checkout_token');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['commune', 'delivery_type', 'shipping_cost', 'tracking_number']);
        });
    }
};
