<?php

namespace App\Http\Controllers;

use App\Models\AirConditionerVariant;
use App\Models\ChatMessage;
use App\Models\Order;
use App\Models\OrderMessage;
use Illuminate\Support\Facades\Schema;

class AdminNotificationController extends Controller
{
    public function index()
    {
        $pendingOrders = Order::where('status', Order::STATUS_PENDING_CONFIRMATION)->count();
        $lowStock = Schema::hasTable('air_conditioner_variants')
            ? AirConditionerVariant::where('stock', '<=', 0)->count() : 0;
        $unreadMessages = 0;
        if (Schema::hasTable('chat_messages') && Schema::hasColumn('chat_messages', 'read_at')) {
            $unreadMessages += ChatMessage::where('sender_role', 'customer')->whereNull('read_at')->count();
        }
        if (Schema::hasTable('order_messages') && Schema::hasColumn('order_messages', 'read_at')) {
            $unreadMessages += OrderMessage::where('sender_role', 'customer')->whereNull('read_at')->count();
        }

        return response()->json([
            'counts' => ['orders' => $pendingOrders, 'messages' => $unreadMessages, 'out_of_stock' => $lowStock],
            'total' => $pendingOrders + $unreadMessages + $lowStock,
        ]);
    }
}
