<?php

namespace App\Http\Controllers;

use App\Models\AirConditioner;
use App\Models\ChatMessage;
use App\Models\Order;
use App\Models\OrderMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $isAdmin = in_array(auth()->user()->role, ['admin', 'staff'], true);
        $keyword = trim((string) $request->query('keyword', ''));
        $customers = collect();
        $selectedCustomer = null;

        if ($isAdmin) {
            $customersQuery = User::query()->whereNotIn('role', ['admin', 'staff']);

            if ($keyword !== '') {
                $customersQuery->where(function ($query) use ($keyword) {
                    $query->where('name', 'like', "%{$keyword}%")
                        ->orWhereHas('orders', function ($orders) use ($keyword) {
                            $orders->where('id', 'like', "%{$keyword}%")
                                ->orWhere('ghn_order_code', 'like', "%{$keyword}%")
                                ->orWhereHas('messages', fn ($messages) => $messages->where('message', 'like', "%{$keyword}%"))
                                ->orWhereHas('chatMessages', fn ($messages) => $messages->where('message', 'like', "%{$keyword}%"));
                        })
                        ->orWhereHas('sentChatMessages', fn ($messages) => $messages->where('message', 'like', "%{$keyword}%"))
                        ->orWhereHas('receivedChatMessages', fn ($messages) => $messages->where('message', 'like', "%{$keyword}%"));
                });
            } else {
                $customersQuery->where(function ($query) {
                    $query->whereHas('orders')
                        ->orWhereHas('sentChatMessages')
                        ->orWhereHas('receivedChatMessages');
                });
            }

            $customers = $customersQuery->orderByDesc('id')->limit(60)->get();
            $selectedCustomer = $customers->firstWhere('id', (int) $request->query('customer_id'))
                ?? $customers->first();
        } else {
            $selectedCustomer = auth()->user();
        }

        $orderSearch = trim((string) $request->query('order_search', ''));
        $ordersQuery = $selectedCustomer?->orders()->latest();

        if ($ordersQuery && $orderSearch !== '') {
            $ordersQuery->where(function ($query) use ($orderSearch) {
                $query->where('id', 'like', "%{$orderSearch}%")
                    ->orWhere('customer_name', 'like', "%{$orderSearch}%")
                    ->orWhere('customer_phone', 'like', "%{$orderSearch}%")
                    ->orWhere('ghn_order_code', 'like', "%{$orderSearch}%");
            });
        }

        $orders = $ordersQuery ? $ordersQuery->limit(50)->get() : collect();
        $latestCustomerOrder = $selectedCustomer?->orders()->latest()->first();
        $products = AirConditioner::orderBy('name')->get(['id', 'name', 'brand', 'image']);
        $selectedOrder = $request->filled('order_id')
            ? $orders->firstWhere('id', (int) $request->query('order_id'))
            : null;
        $selectedProduct = $request->filled('product_id')
            ? $products->firstWhere('id', (int) $request->query('product_id'))
            : null;
        $messages = collect();

        if ($selectedCustomer) {
            $legacyOrderMessages = OrderMessage::with(['sender', 'order'])
                ->whereHas('order', fn ($query) => $query->where('user_id', $selectedCustomer->id))
                ->get();
            $allChatMessages = ChatMessage::with(['sender', 'product', 'order'])
                ->where(function ($query) use ($selectedCustomer) {
                    $query->where('sender_id', $selectedCustomer->id)
                        ->orWhere('recipient_id', $selectedCustomer->id);
                })
                ->get();

            $messages = $legacyOrderMessages->concat($allChatMessages)
                ->sortBy('created_at')->values();
        }

        return view('chat.index', compact(
            'isAdmin',
            'keyword',
            'customers',
            'selectedCustomer',
            'orders',
            'latestCustomerOrder',
            'orderSearch',
            'products',
            'selectedOrder',
            'selectedProduct',
            'messages'
        ));
    }

    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string|min:1|max:2000',
            'customer_id' => 'nullable|integer|exists:users,id',
            'order_id' => 'nullable|integer|exists:orders,id',
            'product_id' => 'nullable|integer|exists:air_conditioners,id',
            'order_search' => 'nullable|string|max:100',
        ]);

        $isAdmin = in_array(auth()->user()->role, ['admin', 'staff'], true);
        $customer = $isAdmin
            ? User::whereNotIn('role', ['admin', 'staff'])->findOrFail($request->customer_id)
            : auth()->user();
        $order = null;

        if ($request->filled('order_id')) {
            $order = Order::where('user_id', $customer->id)->findOrFail($request->order_id);
        }

        $recipient = $isAdmin
            ? $customer
            : User::where('role', 'admin')->orderBy('id')->first();

        if (!$recipient) {
            throw ValidationException::withMessages(['message' => 'Hiện chưa có nhân viên shop để nhận tin nhắn.']);
        }

        ChatMessage::create([
            'order_id' => $order?->id,
            'product_id' => $request->input('product_id'),
            'sender_id' => auth()->id(),
            'recipient_id' => $recipient->id,
            'sender_role' => $isAdmin ? auth()->user()->role : 'customer',
            'message' => trim($request->message),
        ]);

        return redirect()->route($isAdmin ? 'admin.chat.index' : 'chat.index', array_filter([
            'customer_id' => $isAdmin ? $customer->id : null,
            'order_id' => $order?->id,
            'product_id' => $request->input('product_id'),
            'order_search' => $request->input('order_search'),
            'keyword' => $isAdmin ? $request->input('keyword') : null,
        ]))->with('success', 'Đã gửi tin nhắn.');
    }
}
