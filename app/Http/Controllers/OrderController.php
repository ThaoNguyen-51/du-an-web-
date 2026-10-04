<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\AirConditioner;
use App\Services\GHNService;
use App\Services\CouponService;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function __construct(private GHNService $ghnService, private CouponService $couponService)
    {
    }

    public function getProvinces()
    {
        return response()->json($this->ghnService->getProvinces());
    }

    public function getDistricts($provinceId)
    {
        return response()->json($this->ghnService->getDistricts((int) $provinceId));
    }

    public function getWards($districtId)
    {
        return response()->json($this->ghnService->getWards((int) $districtId));
    }

    public function getShippingFee(Request $request)
    {
        $request->validate([
            'to_district_id' => 'required|integer',
            'to_ward_code' => 'required|string',
        ]);

        // Only shipping parameters are sent to GHN. No customer or order details.
        return response()->json($this->ghnService->calculateFee([
            'from_district_id' => (int) config('services.ghn.from_district_id'),
            'to_district_id' => (int) $request->to_district_id,
            'to_ward_code' => (string) $request->to_ward_code,
            'service_type_id' => 2,
            'weight' => 25000,
            'length' => 80,
            'width' => 40,
            'height' => 60,
        ]));
    }

    // ==========================================
    // CÁC HÀM QUẢN LÝ ĐƠN HÀNG
    // ==========================================
    public function index(Request $request)
    {
        $query = Order::where('user_id', auth()->id())
            ->with('paymentTransactions');

        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);

            $query->where(function ($q) use ($keyword) {
                $q->where('id', 'like', "%{$keyword}%")
                    ->orWhere('ghn_order_code', 'like', "%{$keyword}%")
                    ->orWhere('virtual_tracking_code', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('shop.orders', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::with(['items.airConditioner', 'paymentTransactions', 'messages.sender', 'chatMessages.sender'])
            ->where('user_id', auth()->id())->findOrFail($id);

        $order->items->each(function (OrderItem $item) use ($order): void {
            $product = $item->airConditioner;
            if (!$product && $item->product_name) {
                $product = AirConditioner::where('name', $item->product_name)->first();
            }

            $item->setRelation('airConditioner', $product);
            $item->setRelation(
                'purchaseReview',
                $product?->reviews()->with('repliedBy')->where('order_id', $order->id)->first()
            );
        });

        $order->setRelation('messages', $order->messages->concat($order->chatMessages)->sortBy('created_at')->values());

        return view('shop.order_detail', compact('order'));
    }

    public function cancel(Order $order)
    {
        abort_unless($order->user_id === auth()->id(), 403);

        DB::transaction(function () use ($order): void {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $status = Order::normalizeStatus($lockedOrder->status);
            $canCancel = $lockedOrder->payment_method === 'cod'
                && in_array($status, [Order::STATUS_PENDING_CONFIRMATION, Order::STATUS_AWAITING_PICKUP], true)
                && !$lockedOrder->ghn_order_code
                && !in_array($lockedOrder->shipping_status, ['delivering', 'delivered', 'cancelled'], true);

            if (!$canCancel) {
                throw ValidationException::withMessages([
                    'order' => 'Chỉ có thể hủy đơn COD trước khi đơn được bàn giao cho đơn vị vận chuyển.',
                ]);
            }

            $lockedOrder->update([
                'status' => Order::STATUS_CANCELLED,
                'shipping_status' => 'cancelled',
            ]);

            $lockedOrder->paymentTransactions()
                ->where('gateway', 'cod')
                ->whereIn('status', ['pending', 'initiated'])
                ->update(['status' => 'cancelled', 'message' => 'Khách hàng đã hủy đơn COD trước khi giao hàng.']);
        });

        return redirect()->route('user.orders.index')->with('success', 'Đã hủy đơn hàng COD.');
    }

    public function sendMessage(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'message' => 'required|string|min:1|max:1000',
        ]);

        $order->messages()->create([
            'sender_id' => auth()->id(),
            'sender_role' => 'customer',
            'message' => trim($request->message),
        ]);

        return redirect()->back()->with('success', 'Đã gửi tin nhắn cho shop.');
    }

    public function validateCoupon(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string|max:40',
            'selected_items' => 'required|array|min:1',
        ]);

        $cart = session()->get('cart', []);
        $selectedKeys = $this->selectedCartKeys($request, $cart);
        $subtotal = 0;
        foreach ($selectedKeys as $key) {
            $subtotal += ($cart[$key]['price'] ?? 0) * ($cart[$key]['quantity'] ?? 1);
        }

        if ($subtotal <= 0) {
            throw ValidationException::withMessages(['coupon_code' => 'Vui lòng chọn sản phẩm trước khi áp mã.']);
        }

        $coupon = $this->couponService->findApplicable($request->coupon_code, $subtotal);
        $discount = $this->couponService->calculateDiscount($coupon, $subtotal);

        return response()->json([
            'valid' => true,
            'code' => $coupon->code,
            'discount' => $discount,
            'message' => 'Đã áp dụng mã giảm giá.',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'address_id' => 'nullable|integer',
        ]);
        if ($request->filled('address_id')) {
            $savedAddress = auth()->user()->addresses()->findOrFail($request->integer('address_id'));
            $request->merge([
                'customer_name' => $savedAddress->recipient_name,
                'customer_phone' => $savedAddress->phone,
                'customer_address' => collect([
                    $savedAddress->address,
                    $savedAddress->ward,
                    $savedAddress->district,
                    $savedAddress->province,
                ])->filter()->implode(', '),
                'to_district_id' => $savedAddress->district_id,
                'to_ward_code' => $savedAddress->ward_code,
            ]);
        }

        $request->validate([
            'customer_name'    => 'required|string|max:255',
            'customer_phone'   => 'required|string|max:20',
            'customer_address' => 'required|string|max:255',
            'payment_method'   => 'required|in:cod,visa,domestic',
            'selected_items'   => 'required|array|min:1',
            'to_district_id'   => 'nullable|integer',
            'to_ward_code'     => 'nullable|string',
            'shipping_fee'     => 'nullable|numeric',
            'coupon_code'      => 'nullable|string|max:40',
            'address_id'       => 'nullable|integer',
        ]);

        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('user.cart.index')->with('error', 'Giỏ hàng của bạn đang trống!');
        }

        $selectedKeys = $this->selectedCartKeys($request, $cart);
        if (empty($selectedKeys)) {
            return redirect()->route('user.cart.index')->with('error', 'Vui lòng chọn ít nhất một sản phẩm.');
        }

        DB::beginTransaction();
        try {
            $subtotal = 0;
            $orderItemsData = [];

            foreach ($selectedKeys as $key) {
                if (isset($cart[$key])) {
                    $item = $cart[$key];
                    $itemSubtotal = $item['price'] * $item['quantity'];
                    $subtotal += $itemSubtotal;

                    $orderItemsData[] = [
                        'air_conditioner_id' => AirConditioner::whereKey($item['product_id'] ?? 0)->value('id'),
                        'product_name' => $item['name'],
                        'price'        => $item['price'],
                        'quantity'     => $item['quantity'],
                        'capacity_name' => $item['capacity'] ?? null,
                        'subtotal'     => $itemSubtotal,
                    ];
                }
            }

            $coupon = null;
            $discountAmount = 0;
            if ($request->filled('coupon_code')) {
                $coupon = $this->couponService->findApplicable($request->coupon_code, $subtotal, true);
                $discountAmount = $this->couponService->calculateDiscount($coupon, $subtotal);
            }

            $shippingFee = 0;
            if ($request->filled('to_district_id') && $request->filled('to_ward_code')) {
                // Recalculate on the server using shipping-only fields.
                $feeResponse = $this->ghnService->calculateFee([
                    'from_district_id' => (int) config('services.ghn.from_district_id'),
                    'to_district_id' => (int) $request->to_district_id,
                    'to_ward_code' => (string) $request->to_ward_code,
                    'service_type_id' => 2,
                    'weight' => 25000,
                    'length' => 80,
                    'width' => 40,
                    'height' => 60,
                ]);

                if (($feeResponse['code'] ?? null) === 200) {
                    $shippingFee = (int) ($feeResponse['data']['total'] ?? 0);
                }
            }
            $totalAmount = max(0, $subtotal - $discountAmount) + $shippingFee;

            $order = Order::create([
                'user_id'          => auth()->id(),
                'customer_name'    => $request->customer_name,
                'customer_phone'   => $request->customer_phone,
                'customer_address' => $request->customer_address,
                'note'             => $request->note,
                'subtotal'         => $subtotal,
                'shipping_fee'     => $shippingFee,
                'coupon_id'        => $coupon?->id,
                'coupon_code'      => $coupon?->code,
                'discount_amount'  => $discountAmount,
                'total_amount'     => $totalAmount,
                'ghn_total_fee'    => $shippingFee,
                'to_district_id'   => $request->to_district_id ?? null,
                'to_ward_code'     => $request->to_ward_code ?? null,
                'payment_method'   => $request->payment_method,
                'status'           => Order::STATUS_PENDING_CONFIRMATION,
                'shipping_status'  => 'not_shipped',
            ]);

            foreach ($orderItemsData as $itemData) {
                $order->items()->create($itemData);
            }

            foreach ($selectedKeys as $key) {
                unset($cart[$key]);
            }

            if ($coupon) {
                $coupon->increment('used_count');
            }

            if ($request->payment_method === 'cod') {
                PaymentTransaction::create([
                    'order_id' => $order->id,
                    'gateway' => 'cod',
                    'amount' => $order->total_amount,
                    'status' => 'pending',
                    'message' => 'Thanh toán khi nhận hàng',
                ]);
            }

            DB::commit();
            session()->forget('buy_now_key');

            if (Order::isOnlinePaymentMethod($request->payment_method)) {
                return redirect()->route('payment.momo.start', $order);
            }

            session()->put('cart', $cart);
            return redirect()->route('user.orders.show', $order->id)
                 ->with('success', 'Đã tạo đơn hàng thành công.');

        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra khi tạo đơn hàng: ' . $e->getMessage());
        }
    }

    private function selectedCartKeys(Request $request, array $cart): array
    {
        return array_values(array_filter(
            $request->input('selected_items', []),
            fn ($key) => (is_string($key) || is_int($key)) && array_key_exists($key, $cart)
        ));
    }
}