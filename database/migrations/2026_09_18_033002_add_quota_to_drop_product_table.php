<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drop_product', function (Blueprint $table): void {
            $table->unsignedInteger('quota')
                ->nullable()
                ->after('product_id');
        });
    }

    public function down(): void
    {
        Schema::table('drop_product', function (Blueprint $table): void {
            $table->dropColumn('quota');
        });
    }
};