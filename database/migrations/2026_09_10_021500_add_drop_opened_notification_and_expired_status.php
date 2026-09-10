<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drop_whitelists', function (Blueprint $table) {
            $table->timestamp('drop_opened_notified_at')->nullable()->after('status');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE drop_whitelists MODIFY status ENUM('pending', 'approved', 'rejected', 'expired') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE drop_whitelists MODIFY status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
        }

        Schema::table('drop_whitelists', function (Blueprint $table) {
            $table->dropColumn('drop_opened_notified_at');
        });
    }
};
