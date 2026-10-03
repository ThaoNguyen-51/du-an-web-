<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AirConditionerController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AdminCustomerController;
use App\Http\Controllers\AdminCouponController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\MomoController;
use App\Http\Controllers\ProductReviewController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\AirConditioner;

// ==============================
// 1. ROUTE PUBLIC (KHÔNG CẦN ĐĂNG NHẬP)
// ==============================

Route::get('/up', function () {
    return response()->json(['status' => 'ok']);
});

// GHN chỉ được dùng để tra cứu địa chỉ, không tạo vận đơn và không gửi dữ liệu đơn hàng.
Route::prefix('ghn')->group(function () {
    Route::get('/provinces', [OrderController::class, 'getProvinces']);
    Route::get('/districts/{provinceId}', [OrderController::class, 'getDistricts']);
    Route::get('/wards/{districtId}', [OrderController::class, 'getWards']);
    Route::post('/shipping-fee', [OrderController::class, 'getShippingFee']);
});

Route::get('/', function (Request $request) {
    $query = AirConditioner::with('variants');

    if ($request->filled('keyword')) {
        $keyword = trim($request->keyword);
        $query->where(function ($productQuery) use ($keyword) {
            $productQuery->where('name', 'like', "%{$keyword}%")
                ->orWhere('brand', 'like', "%{$keyword}%");
        });
    }

    if ($request->filled('brand')) {
        $query->where('brand', $request->brand);
    }

    $airConditioners = $query->get();
    $brands = AirConditioner::query()
        ->whereNotNull('brand')
        ->whereRaw("TRIM(brand) <> ''")
        ->distinct()
        ->orderBy('brand')
        ->pluck('brand');

    return view('shop.index', compact('airConditioners', 'brands'));
})->name('shop.index');

// Đăng ký & Đăng nhập
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Đăng xuất
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


Route::post('/payment/momo/ipn', [MomoController::class, 'ipn'])->name('payment.momo.ipn');
Route::get('/payment/momo/callback', [MomoController::class, 'callback'])->name('payment.momo.callback');


// ==============================
// 3. YÊU CẦU ĐĂNG NHẬP (AUTH)
// ==============================
Route::middleware('auth')->group(function () {

    Route::middleware('customer')->group(function () {
        Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
        Route::post('/chat/messages', [ChatController::class, 'send'])->name('chat.send');
    });

    // Chi tiết sản phẩm
    Route::get('/products/{id}', function ($id) {
        $airConditioner = AirConditioner::with(['variants', 'images', 'reviews.user', 'reviews.repliedBy'])->findOrFail($id);
        $eligibleReviewOrders = collect();
        $incompleteReviewOrders = collect();

        if (auth()->user()->role === 'customer') {
            $matchingProduct = function ($query) use ($airConditioner) {
                    $query->where('air_conditioner_id', $airConditioner->id)
                        ->orWhere(function ($legacyQuery) use ($airConditioner) {
                            $legacyQuery->whereNull('air_conditioner_id')
                                ->where('product_name', $airConditioner->name);
                        });
                };

            $customerOrders = auth()->user()->orders()
                ->whereHas('items', $matchingProduct)
                ->whereDoesntHave('reviews', fn ($query) => $query->where('air_conditioner_id', $airConditioner->id))
                ->latest();

            $eligibleReviewOrders = (clone $customerOrders)
                ->where('status', Order::STATUS_COMPLETED)
                ->get(['id', 'created_at', 'status']);

            $incompleteReviewOrders = (clone $customerOrders)
                ->whereNotIn('status', [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])
                ->get(['id', 'created_at', 'status']);
        }

        return view('shop.detail', compact('airConditioner', 'eligibleReviewOrders', 'incompleteReviewOrders'));
    })->name('shop.detail');


    // ==============================
    // 4. XÁC THỰC EMAIL (VERIFIED)
    // ==============================
    Route::middleware(['verified', 'customer'])->group(function () {
        
        // Quản lý Giỏ hàng
        Route::get('/cart', [CartController::class, 'index'])->name('user.cart.index');
        Route::post('/add-to-cart/{id}', [CartController::class, 'addToCart'])->name('cart.add');
        Route::post('/update-cart', [CartController::class, 'update'])->name('cart.update');
        Route::post('/remove-from-cart', [CartController::class, 'remove'])->name('cart.remove');
        Route::post('/clear-cart', [CartController::class, 'clear'])->name('cart.clear');

        // Quản lý Đơn hàng người dùng
        Route::get('/orders', [OrderController::class, 'index'])->name('user.orders.index');
        Route::post('/orders', [OrderController::class, 'store'])->name('user.orders.store');
        Route::post('/coupons/validate', [OrderController::class, 'validateCoupon'])->name('user.coupons.validate');
        
        Route::get('/orders/{id}', [OrderController::class, 'show'])->name('user.orders.show');
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('user.orders.cancel');
        Route::post('/orders/{order}/messages', [OrderController::class, 'sendMessage'])->name('user.orders.sendMessage');
        Route::post('/products/{product}/reviews', [ProductReviewController::class, 'store'])->name('product-reviews.store');
        Route::get('/shop/orders/{id}', [OrderController::class, 'show'])->name('shop.orders.show');
        Route::get('/orders/{order}/start-momo', [MomoController::class, 'start'])->name('payment.momo.start');
        Route::get('/orders/{order}/pay/momo', [MomoController::class, 'payAgain'])->name('payment.momo.pay');

    });


    Route::middleware('admin_or_staff')->group(function () {
        Route::get('/admin/chat', [ChatController::class, 'index'])->name('admin.chat.index');
        Route::post('/admin/chat/messages', [ChatController::class, 'send'])->name('admin.chat.send');
        Route::get('/admin/coupons', [AdminCouponController::class, 'index'])->name('admin.coupons.index');
        Route::post('/admin/coupons', [AdminCouponController::class, 'store'])->name('admin.coupons.store');
        Route::put('/admin/coupons/{coupon}', [AdminCouponController::class, 'update'])->name('admin.coupons.update');
        Route::delete('/admin/coupons/{coupon}', [AdminCouponController::class, 'destroy'])->name('admin.coupons.destroy');

        Route::get('/air_conditioners', [AirConditionerController::class, 'index'])->name('air_conditioners.index');
        Route::get('/air_conditioners/create', [AirConditionerController::class, 'create'])->name('air_conditioners.create');
        Route::post('/air_conditioners', [AirConditionerController::class, 'store'])->name('air_conditioners.store');
        Route::get('/air_conditioners/{air_conditioner}', [AirConditionerController::class, 'show'])->name('air_conditioners.show');
        Route::get('/air_conditioners/{air_conditioner}/edit', [AirConditionerController::class, 'edit'])->name('air_conditioners.edit');
        Route::put('/air_conditioners/{air_conditioner}', [AirConditionerController::class, 'update'])->name('air_conditioners.update');
        Route::delete('/air_conditioners/{air_conditioner}', [AirConditionerController::class, 'destroy'])->name('air_conditioners.destroy');

        Route::get('/admin/orders', [AdminOrderController::class, 'index'])->name('admin.orders.index');
        Route::get('/admin/orders/{order}', [AdminOrderController::class, 'show'])->name('admin.orders.show');
        Route::post('/admin/orders/{id}/status', [AdminOrderController::class, 'updateStatus'])->name('admin.orders.updateStatus');
        Route::post('/admin/orders/{id}/messages', [AdminOrderController::class, 'sendMessage'])->name('admin.orders.sendMessage');
        Route::post('/admin/product-reviews/{review}/reply', [ProductReviewController::class, 'reply'])->name('admin.product-reviews.reply');
    });

    // ==============================
    // 5. CHỈ ADMIN MỚI ĐƯỢC QUẢN LÝ
    // ==============================
    Route::middleware('admin')->group(function () {
        Route::get('/admin/customers', [AdminCustomerController::class, 'index'])->name('admin.customers.index');
        Route::put('/admin/customers/{customer}', [AdminCustomerController::class, 'update'])->name('admin.customers.update');
        Route::get('/admin/staff', [AdminStaffController::class, 'index'])->name('admin.staff.index');
        Route::post('/admin/staff', [AdminStaffController::class, 'store'])->name('admin.staff.store');
        Route::put('/admin/staff/{staff}', [AdminStaffController::class, 'update'])->name('admin.staff.update');
        Route::delete('/admin/staff/{staff}', [AdminStaffController::class, 'destroy'])->name('admin.staff.destroy');

        // Báo cáo doanh thu và thống kê
        Route::get('/admin/reports', [AdminReportController::class, 'index'])->name('admin.reports.index');
        Route::get('/admin/reports/charts', [AdminReportController::class, 'charts'])->name('admin.reports.charts');

        // Quản lý và báo cáo thanh toán
        Route::get('/admin/finance', [FinanceController::class, 'index'])->name('admin.finance.index');
        Route::get('/admin/finance/transactions', [FinanceController::class, 'transactions'])->name('admin.finance.transactions');
        Route::patch('/admin/finance/{order}/status', [FinanceController::class, 'updateStatus'])->name('admin.finance.update-status');
    });

});


// ==============================
// 6. ROUTE XÁC THỰC EMAIL
// ==============================
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login')->with('success', 'Xác thực email thành công! Vui lòng đăng nhập lại.');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Đã gửi lại liên kết xác thực!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::get('/check-email-verified', function () {
    $user = auth()->user();
    return response()->json([
        'verified' => $user ? $user->hasVerifiedEmail() : false
    ]);
});