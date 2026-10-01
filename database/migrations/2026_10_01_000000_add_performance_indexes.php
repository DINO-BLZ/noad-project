<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index('updated_at');
        });

        Schema::table('drops', function (Blueprint $table) {
            $table->index(['start_date', 'end_date']);
        });

        Schema::table('drop_whitelists', function (Blueprint $table) {
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['updated_at']);
        });

        Schema::table('drops', function (Blueprint $table) {
            $table->dropIndex(['start_date', 'end_date']);
        });

        Schema::table('drop_whitelists', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });
    }
};
