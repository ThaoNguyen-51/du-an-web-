<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FinanceController extends Controller
{
    private const METHODS = [
        'cod' => 'COD',
        'visa' => 'Visa',
        'domestic' => 'Thẻ nội địa',
    ];

    private const STATUSES = [
        'pending' => 'Chờ thanh toán',
        'initiated' => 'Đang chờ MoMo',
        'paid' => 'Đã thanh toán',
        'failed' => 'Thanh toán thất bại',
        'cancelled' => 'Đã hủy',
        'refunded' => 'Đã hoàn tiền',
    ];

    private const COD_TRANSITIONS = [
        'pending' => ['pending', 'paid'],
        'failed' => ['pending', 'paid'],
        'paid' => ['paid'],
        'refunded' => ['refunded'],
    ];

    private const PAYMENT_PRIORITY = "CASE WHEN status IN ('paid', 'refunded') THEN 0 ELSE 1 END";

    private function ordersQuery(): Builder
    {
        $paymentId = DB::table('payment_transactions')
            ->select('id')
            ->whereColumn('order_id', 'orders.id')
            ->orderByRaw(self::PAYMENT_PRIORITY)
            ->orderByDesc('id')
            ->limit(1);

        $orders = DB::table('orders')
            ->leftJoin('payment_transactions as payment', function ($join) use ($paymentId) {
                $join->on('payment.order_id', '=', 'orders.id')
                    ->where('payment.id', '=', $paymentId);
            })
            ->select([
                'orders.*',
                'payment.id as payment_id',
                'payment.gateway as payment_gateway',
                'payment.amount as payment_amount',
                'payment.paid_at',
            ])
            ->selectRaw("CASE WHEN orders.payment_method IN ('visa', 'domestic') THEN orders.payment_method ELSE COALESCE(payment.gateway, NULLIF(orders.payment_method, ''), 'unknown') END as gateway")
            ->selectRaw("COALESCE(payment.status, 'pending') as payment_status");

        return DB::query()->fromSub($orders, 'finance_orders');
    }

    private function filteredOrders(Request $request): array
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', ...($request->filled('date_from') ? ['after_or_equal:date_from'] : [])],
            'min_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
            'max_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99', ...($request->filled('min_amount') ? ['gte:min_amount'] : [])],
            'gateway' => ['nullable', Rule::in(array_keys(self::METHODS))],
            'payment_status' => ['nullable', Rule::in(array_keys(self::STATUSES))],
            'sort' => ['nullable', Rule::in(['newest', 'oldest', 'amount_asc', 'amount_desc'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ], [
            'date_to.after_or_equal' => 'Ngày kết thúc phải từ ngày bắt đầu trở đi.',
            'max_amount.gte' => 'Số tiền tối đa phải lớn hơn hoặc bằng số tiền tối thiểu.',
            '*.date_format' => 'Ngày lọc không hợp lệ.',
            '*.numeric' => 'Số tiền phải là một giá trị số.',
            '*.min' => 'Giá trị bộ lọc nhỏ hơn mức cho phép.',
            '*.in' => 'Giá trị bộ lọc không hợp lệ.',
        ]);

        $query = $this->ordersQuery()->where('created_at', '<=', now());

        if ($request->filled('search')) {
            $search = trim($filters['search']);
            $query->where(function (Builder $query) use ($search) {
                $query->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");

                $orderId = ltrim($search, '#');
                if (ctype_digit($orderId)) {
                    $query->orWhere('id', (int) $orderId);
                }
            });
        }

        foreach (['gateway', 'payment_status'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $filters[$field]);
            }
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<', Carbon::parse($filters['date_to'])->addDay()->startOfDay());
        }

        foreach (['min_amount' => '>=', 'max_amount' => '<='] as $field => $operator) {
            if ($request->filled($field)) {
                $query->where('total_amount', $operator, $filters[$field]);
            }
        }

        return [$query, $filters];
    }

    public function index(Request $request)
    {
        [$query, $filters] = $this->filteredOrders($request);

        $summary = (clone $query)->selectRaw('COUNT(*) as order_count, COALESCE(SUM(total_amount), 0) as total_amount')->first();
        $statusTotals = (clone $query)->select('payment_status')->selectRaw('COUNT(*) as order_count, COALESCE(SUM(total_amount), 0) as total_amount')->groupBy('payment_status')->get()->keyBy('payment_status');
        $methodTotals = (clone $query)->select('gateway')->selectRaw('COUNT(*) as order_count, COALESCE(SUM(total_amount), 0) as total_amount')->selectRaw("COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END), 0) as paid_amount")->groupBy('gateway')->get()->keyBy('gateway')->only(array_keys(self::METHODS));

        return view('admin.finance.index', [
            'filters' => $filters,
            'summary' => $summary,
            'statusTotals' => $statusTotals,
            'methodTotals' => $methodTotals,
            'statuses' => self::STATUSES,
            'methods' => self::METHODS,
        ]);
    }

    public function transactions(Request $request)
    {
        [$query, $filters] = $this->filteredOrders($request);
        [$column, $direction] = match ($filters['sort'] ?? 'newest') {
            'oldest' => ['created_at', 'asc'],
            'amount_asc' => ['total_amount', 'asc'],
            'amount_desc' => ['total_amount', 'desc'],
            default => ['created_at', 'desc'],
        };

        $orders = $query->orderBy($column, $direction)->orderBy('id', $direction)->paginate(15)->withQueryString();

        return view('admin.finance.transactions', [
            'orders' => $orders,
            'filters' => $filters,
            'statuses' => self::STATUSES,
            'codTransitions' => self::COD_TRANSITIONS,
            'methods' => self::METHODS,
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'payment_status' => ['required', Rule::in(array_keys(self::COD_TRANSITIONS))],
            'current_payment_status' => ['required', 'string'],
            'current_order_status' => ['required', 'string'],
            'current_payment_id' => ['required', 'integer', 'min:0'],
        ], [
            'payment_status.in' => 'Trạng thái COD không hợp lệ.',
            '*.required' => 'Thiếu thông tin trạng thái. Vui lòng tải lại trang.',
        ]);

        DB::transaction(function () use ($order, $data, $request) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $payment = $lockedOrder->paymentTransactions()->orderByRaw(self::PAYMENT_PRIORITY)->orderByDesc('id')->lockForUpdate()->first();
            $isCod = ($payment?->gateway ?? $lockedOrder->payment_method) === 'cod';

            if (!$isCod) {
                throw ValidationException::withMessages(['payment_status' => 'Chỉ được cập nhật thủ công cho đơn COD.']);
            }

            $currentStatus = $payment?->status ?? 'pending';
            if ($currentStatus !== $data['current_payment_status'] || $lockedOrder->status !== $data['current_order_status'] || (int) ($payment?->id ?? 0) !== (int) $data['current_payment_id']) {
                throw ValidationException::withMessages(['payment_status' => 'Đơn hàng vừa thay đổi. Vui lòng tải lại trang trước khi cập nhật.']);
            }

            $newStatus = $data['payment_status'];
            if (!in_array($newStatus, self::COD_TRANSITIONS[$currentStatus] ?? [], true)) {
                throw ValidationException::withMessages(['payment_status' => 'Không thể chuyển sang trạng thái thanh toán này.']);
            }

            if (in_array($newStatus, ['pending', 'paid'], true) && ($lockedOrder->status === Order::STATUS_CANCELLED || in_array($lockedOrder->shipping_status, ['cancelled', 'return', 'returned'], true))) {
                throw ValidationException::withMessages(['payment_status' => 'Không thể xác nhận thu tiền cho đơn đã hủy hoặc hoàn hàng.']);
            }

            if ($newStatus === $currentStatus) {
                return;
            }

            $attributes = [
                'status' => $newStatus,
                'message' => 'Quản trị viên #' . $request->user()->id . ' cập nhật: ' . self::STATUSES[$newStatus],
                'paid_at' => $newStatus === 'paid' ? ($payment?->paid_at ?? now()) : $payment?->paid_at,
            ];

            if ($payment) {
                $payment->update($attributes);
            } else {
                $lockedOrder->paymentTransactions()->create(array_merge($attributes, ['gateway' => 'cod', 'amount' => $lockedOrder->total_amount]));
            }
        });

        return back()->with('success', 'Đã lưu trạng thái thanh toán đơn COD #' . $order->id . '.');
    }
}