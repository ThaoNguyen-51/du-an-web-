<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('air_conditioners', function (Blueprint $table) {
            $table->string('origin')->nullable()->default('Thái Lan'); // Xuất xứ
            $table->string('warranty')->nullable()->default('3 năm'); // Bảo hành
            $table->string('room_size')->nullable()->default('Dưới 15 m2'); // Phạm vi làm lạnh
            $table->string('inverter_type')->nullable()->default('Không Inverter'); // Công nghệ Inverter
            $table->string('type')->nullable()->default('1 chiều'); // Loại máy (1 chiều / 2 chiều)
            $table->string('power_consumption')->nullable()->default('833 W'); // Tiêu thụ điện
            $table->string('energy_rating')->nullable()->default('1 sao / CSPF: 3.29'); // Nhãn năng lượng
            $table->string('cooling_feature')->nullable()->default('Làm lạnh nhanh Turbo'); // Làm lạnh nhanh
            $table->string('antibacterial_feature')->nullable()->default('Tự làm sạch ECO CLEAN'); // Kháng khuẩn khử mùi
        });
    }

    public function down(): void {
        Schema::table('air_conditioners', function (Blueprint $table) {
            $table->dropColumn([
                'origin', 'warranty', 'room_size', 'inverter_type', 
                'type', 'power_consumption', 'energy_rating', 
                'cooling_feature', 'antibacterial_feature'
            ]);
        });
    }
};