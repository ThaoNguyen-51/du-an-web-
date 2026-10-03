<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('orders')->select(['id', 'virtual_tracking_code'])->orderBy('id')->chunkById(500, function ($orders): void {
            foreach ($orders as $order) {
                if (preg_match('/^[A-Z0-9]{6}$/', (string) $order->virtual_tracking_code)) {
                    continue;
                }

                do {
                    $code = Str::upper(Str::random(6));
                } while (DB::table('orders')->where('virtual_tracking_code', $code)->exists());

                DB::table('orders')->where('id', $order->id)->update([
                    'virtual_tracking_code' => $code,
                ]);
            }
        });
    }

    public function down(): void
    {
        DB::table('orders')->select(['id', 'created_at'])->orderBy('id')->chunkById(500, function ($orders): void {
            foreach ($orders as $order) {
                $date = $order->created_at ? date('Ymd', strtotime($order->created_at)) : date('Ymd');
                DB::table('orders')->where('id', $order->id)->update([
                    'virtual_tracking_code' => sprintf('HC-%s-%08d', $date, $order->id),
                ]);
            }
        });
    }
};
