<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['chat_messages', 'order_messages'] as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'read_at')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->timestamp('read_at')->nullable()->index();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['chat_messages', 'order_messages'] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'read_at')) {
                Schema::table($tableName, fn (Blueprint $table) => $table->dropColumn('read_at'));
            }
        }
    }
};
