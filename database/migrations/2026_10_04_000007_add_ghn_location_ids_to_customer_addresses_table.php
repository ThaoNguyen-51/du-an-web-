<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->unsignedInteger('province_id')->nullable()->after('province');
            $table->unsignedInteger('district_id')->nullable()->after('district');
            $table->string('ward_code', 20)->nullable()->after('ward');
        });
    }

    public function down(): void
    {
        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->dropColumn(['province_id', 'district_id', 'ward_code']);
        });
    }
};
