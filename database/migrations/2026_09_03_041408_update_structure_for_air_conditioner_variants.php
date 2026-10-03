<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Cập nhật bảng phân loại (variants)
        Schema::table('air_conditioner_variants', function (Blueprint $table) {
            $table->integer('weight')->default(25000)->comment('Cân nặng tính bằng Gram cho GHN')->after('price');
            $table->json('specifications')->nullable()->comment('Chứa toàn bộ thông số kỹ thuật chi tiết')->after('weight');
        });

        // 2. Dọn dẹp bớt các cột thông số cũ ở bảng sản phẩm chính (nếu có)
        Schema::table('air_conditioners', function (Blueprint $table) {
            $columnsToDrop = [];
            
            // Kiểm tra và xóa các cột cũ đã chuyển sang variant
            if (Schema::hasColumn('air_conditioners', 'weight')) $columnsToDrop[] = 'weight';
            if (Schema::hasColumn('air_conditioners', 'room_size')) $columnsToDrop[] = 'room_size';
            if (Schema::hasColumn('air_conditioners', 'inverter_type')) $columnsToDrop[] = 'inverter_type';
            if (Schema::hasColumn('air_conditioners', 'power_consumption')) $columnsToDrop[] = 'power_consumption';
            if (Schema::hasColumn('air_conditioners', 'energy_rating')) $columnsToDrop[] = 'energy_rating';
            if (Schema::hasColumn('air_conditioners', 'cooling_feature')) $columnsToDrop[] = 'cooling_feature';
            if (Schema::hasColumn('air_conditioners', 'antibacterial_feature')) $columnsToDrop[] = 'antibacterial_feature';

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    public function down(): void
    {
        Schema::table('air_conditioner_variants', function (Blueprint $table) {
            $table->dropColumn(['weight', 'specifications']);
        });
    }
};