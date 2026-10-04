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
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminActivityLogController;
use App\Http\Controllers\AdminNotificationController;
use App\Http\Controllers\AdminOrderPrintController;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\MomoController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\CustomerAccountController;
use App\Http\Controllers\WishlistController;
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

    $minPrice = is_numeric($request->input('min_price')) ? (float) $request->input('min_price') : null;
    $maxPrice = is_numeric($request->input('max_price')) ? (float) $request->input('max_price') : null;

    if ($minPrice !== null) {
        $query->where(function ($productQuery) use ($minPrice) {
            $productQuery->where('price', '>=', $minPrice)
                ->orWhereHas('variants', fn ($variantQuery) => $variantQuery->where('price', '>=', $minPrice));
        });
    }

    if ($maxPrice !== null) {
        $query->where(function ($productQuery) use ($maxPrice) {
            $productQuery->where('price', '<=', $maxPrice)
                ->orWhereHas('variants', fn ($variantQuery) => $variantQuery->where('price', '<=', $maxPrice));
        });
    }

    match ($request->input('sort')) {
        'price_asc' => $query->orderBy('price'),
        'price_desc' => $query->orderByDesc('price'),
        'name_asc' => $query->orderBy('name'),
        default => $query->latest(),
    };

    $airConditioners = $query->get();
    $brands = AirConditioner::query()
        ->whereNotNull('brand')
        ->whereRaw("TRIM(brand) <> ''")
        ->distinct()
        ->orderBy('brand')
        ->pluck('brand');

    return view('shop.index', compact('airConditioners', 'brands'));
})->name('shop.index');

Route::get('/compare', function (Request $request) {
    $ids = collect($request->session()->get('compare_products', []))
        ->map(fn ($id) => (int) $id)
        ->filter()
        ->unique()
        ->take(4);
    $products = AirConditioner::with('variants')->whereIn('id', $ids)->get()
        ->sortBy(fn ($product) => $ids->search($product->id))
        ->values();
    $specKeys = $products->flatMap(fn ($product) => $product->variants->flatMap(
        fn ($variant) => array_keys(is_array($variant->specifications) ? $variant->specifications : [])
    ))->unique()->values();

    return view('shop.compare', compact('products', 'specKeys'));
})->name('shop.compare');

Route::post('/compare/{id}', function (Request $request, int $id) {
    abort_unless(AirConditioner::whereKey($id)->exists(), 404);

    $ids = collect($request->session()->get('compare_products', []))
        ->map(fn ($item) => (int) $item)
        ->filter()
        ->unique();

    if (!$ids->contains($id) && $ids->count() >= 4) {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Bạn chỉ có thể so sánh tối đa 4 sản phẩm.'], 422);
        }

        return back()->with('error', 'Bạn chỉ có thể so sánh tối đa 4 sản phẩm.');
    }

    $request->session()->put('compare_products', $ids->push($id)->values()->all());

    if ($request->expectsJson()) {
        return response()->json([
            'message' => 'Đã thêm sản phẩm vào danh sách so sánh.',
            'compare_count' => $ids->count() + 1,
        ]);
    }

    return back()->with('success', 'Đã thêm sản phẩm vào danh sách so sánh.');
})->name('shop.compare.add');

Route::delete('/compare/{id}', function (Request $request, int $id) {
    $ids = collect($request->session()->get('compare_products', []))
        ->reject(fn ($item) => (int) $item === $id)
        ->values()
        ->all();
    $request->session()->put('compare_products', $ids);

    if ($request->expectsJson()) {
        return response()->json([
            'message' => 'Đã xóa sản phẩm khỏi danh sách so sánh.',
            'compare_count' => count($ids),
        ]);
    }

    return back()->with('success', 'Đã xóa sản phẩm khỏi danh sách so sánh.');
})->name('shop.compare.remove');

// Đăng ký & Đăng nhập
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->middleware('throttle:6,1')->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
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
        $selectedVariant = $airConditioner->variants->first();
        $selectedSpecs = is_array($selectedVariant?->specifications) ? $selectedVariant->specifications : [];
        $selectedType = strtolower(trim((string) ($selectedSpecs['machine_type'] ?? $selectedSpecs['type'] ?? '')));
        $selectedCapacity = strtolower(trim((string) ($selectedVariant?->capacity_name ?? '')));
        $selectedCapacityNumber = (int) preg_replace('/\D+/', '', $selectedCapacity);
        $selectedBrand = strtolower(trim((string) $airConditioner->brand));

        $recommendedProducts = AirConditioner::with(['variants', 'images'])
            ->where('id', '!=', $airConditioner->id)
            ->get()
            ->map(function ($product) use ($selectedBrand, $selectedType, $selectedCapacity, $selectedCapacityNumber) {
                $variant = $product->variants->first();
                $specs = is_array($variant?->specifications) ? $variant->specifications : [];
                $type = strtolower(trim((string) ($specs['machine_type'] ?? $specs['type'] ?? '')));
                $capacity = strtolower(trim((string) ($variant?->capacity_name ?? '')));
                $capacityNumber = (int) preg_replace('/\D+/', '', $capacity);
                $score = 0;

                if ($selectedBrand !== '' && strtolower(trim((string) $product->brand)) === $selectedBrand) {
                    $score += 4;
                }
                if ($selectedType !== '' && $type !== '' && $selectedType === $type) {
                    $score += 3;
                }
                if ($selectedCapacity !== '' && $capacity !== '' && $selectedCapacity === $capacity) {
                    $score += 3;
                }
                if ($selectedCapacityNumber > 0 && $capacityNumber > 0 && abs($selectedCapacityNumber - $capacityNumber) <= 3000) {
                    $score += 2;
                }
                if ($variant && $variant->price !== null) {
                    $score += 1;
                }

                $product->recommendation_score = $score;
                return $product;
            })
            ->sortByDesc('recommendation_score')
            ->take(4)
            ->values();
        $eligibleReviewOrders = collect();
        $incompleteReviewOrders = collect();
        $isWishlisted = auth()->user()->role === 'user'
            && auth()->user()->wishlistItems()->where('air_conditioner_id', $airConditioner->id)->exists();

        if (auth()->user()->role === 'user') {
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

        return view('shop.detail', compact('airConditioner', 'recommendedProducts', 'eligibleReviewOrders', 'incompleteReviewOrders', 'isWishlisted'));
    })->name('shop.detail');


    // ==============================
    // 4. XÁC THỰC EMAIL (VERIFIED)
    // ==============================
    Route::middleware(['verified', 'customer'])->group(function () {
        Route::get('/account', [CustomerAccountController::class, 'index'])->name('account.index');
        Route::put('/account/profile', [CustomerAccountController::class, 'updateProfile'])->name('account.profile.update');
        Route::put('/account/password', [CustomerAccountController::class, 'updatePassword'])->name('account.password.update');
        Route::post('/account/addresses', [CustomerAccountController::class, 'storeAddress'])->name('account.addresses.store');
        Route::put('/account/addresses/{address}', [CustomerAccountController::class, 'updateAddress'])->name('account.addresses.update');
        Route::delete('/account/addresses/{address}', [CustomerAccountController::class, 'destroyAddress'])->name('account.addresses.destroy');
        Route::post('/account/addresses/{address}/default', [CustomerAccountController::class, 'setDefaultAddress'])->name('account.addresses.default');
        Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
        Route::post('/wishlist/{product}/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

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
        Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/admin/activity-logs', [AdminActivityLogController::class, 'index'])->name('admin.activity-logs.index');
        Route::get('/admin/notifications', [AdminNotificationController::class, 'index'])->name('admin.notifications');
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
        Route::get('/admin/orders/export/csv', [AdminOrderController::class, 'exportCsv'])->name('admin.orders.export');
        Route::get('/admin/orders/print', [AdminOrderPrintController::class, 'index'])->name('admin.orders.print.index');
        Route::get('/admin/orders/print/date', [AdminOrderPrintController::class, 'index'])->name('admin.orders.print.date');
        Route::get('/admin/orders/print/unprinted', function (Request $request) {
            return app(AdminOrderPrintController::class)->index($request->merge(['printed' => 'unprinted']));
        })->name('admin.orders.print.unprinted');
        Route::get('/admin/orders/print/printed', function (Request $request) {
            return app(AdminOrderPrintController::class)->index($request->merge(['printed' => 'printed']));
        })->name('admin.orders.print.printed');
        Route::post('/admin/orders/print/bulk', [AdminOrderPrintController::class, 'printBulk'])->name('admin.orders.print.bulk');
        Route::get('/admin/orders/{order}/print', [AdminOrderPrintController::class, 'printOne'])->name('admin.orders.print.one');
        Route::get('/admin/orders/{order}', [AdminOrderController::class, 'show'])->name('admin.orders.show');
        Route::get('/admin/orders/{order}/invoice', [AdminOrderController::class, 'invoice'])->name('admin.orders.invoice');
        Route::post('/admin/orders/{order}/confirm', [AdminOrderController::class, 'confirm'])->name('admin.orders.confirm');
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
use Illuminate\Support\Facades\Artisan;

Route::get('/run-seed-secret-999', function () {
    // Tự động chạy migration tạo bảng nếu chưa có và nạp dữ liệu mẫu
    Artisan::call('migrate', ['--force' => true]);
    Artisan::call('db:seed', ['--force' => true]);
    return 'Seed database successfully!<br><pre>' . Artisan::output() . '</pre>';
});