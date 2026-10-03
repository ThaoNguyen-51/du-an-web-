<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('virtual_tracking_code', 32)->nullable()->unique();
        });

        DB::table('orders')->select(['id', 'created_at'])->orderBy('id')->chunkById(500, function ($orders): void {
            foreach ($orders as $order) {
                $date = $order->created_at
                    ? Carbon::parse($order->created_at)->format('Ymd')
                    : now()->format('Ymd');
                $code = sprintf('HC-%s-%08d', $date, $order->id);

                DB::table('orders')->where('id', $order->id)->update([
                    'virtual_tracking_code' => $code,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['virtual_tracking_code']);
            $table->dropColumn('virtual_tracking_code');
        });
    }
};
