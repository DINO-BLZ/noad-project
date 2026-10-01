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
            $table->string('drop_opened_notification_status', 16)
                ->default('pending')
                ->after('drop_opened_notified_at')
                ->index();
        });

        DB::table('drop_whitelists')
            ->whereNotNull('drop_opened_notified_at')
            ->update(['drop_opened_notification_status' => 'sent']);
    }

    public function down(): void
    {
        Schema::table('drop_whitelists', function (Blueprint $table) {
            $table->dropIndex(['drop_opened_notification_status']);
            $table->dropColumn('drop_opened_notification_status');
        });
    }
};
