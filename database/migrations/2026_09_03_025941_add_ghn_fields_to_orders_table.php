<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'ghn_total_fee')) {
                $table->integer('ghn_total_fee')->default(0)->after('total_amount');
            }

            if (!Schema::hasColumn('orders', 'to_district_id')) {
                $table->string('to_district_id')->nullable()->after('ghn_total_fee');
            }

            if (!Schema::hasColumn('orders', 'to_ward_code')) {
                $table->string('to_ward_code')->nullable()->after('to_district_id');
            }

            if (!Schema::hasColumn('orders', 'shipping_status')) {
                $table->string('shipping_status')->default('not_shipped')->after('to_ward_code');
            }

            if (!Schema::hasColumn('orders', 'ghn_order_code')) {
                $table->string('ghn_order_code')->nullable()->after('shipping_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columns = ['ghn_total_fee', 'to_district_id', 'to_ward_code', 'shipping_status', 'ghn_order_code'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};