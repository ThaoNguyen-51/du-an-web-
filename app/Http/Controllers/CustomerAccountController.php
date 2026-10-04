<?php

namespace App\Http\Controllers;

use App\Models\CustomerAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CustomerAccountController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $addresses = $user->addresses()->latest()->get();
        $orders = $user->orders()->with('items')->latest()->paginate(10, ['*'], 'orders_page');

        return view('account.index', compact('user', 'addresses', 'orders'));
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        $emailChanged = $data['email'] !== $user->email;
        if ($emailChanged) {
            $user->email_verified_at = null;
        }
        $user->update($data);
        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        return back()->with('success', $emailChanged
            ? 'Đã cập nhật thông tin. Vui lòng xác thực email mới.'
            : 'Đã cập nhật thông tin cá nhân.');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ], [
            'current_password.current_password' => 'Mật khẩu hiện tại không chính xác.',
        ]);

        $request->user()->update(['password' => Hash::make($data['password'])]);

        return back()->with('success', 'Đã đổi mật khẩu thành công.');
    }

    public function storeAddress(Request $request)
    {
        $data = $this->validateAddress($request);
        $address = $request->user()->addresses()->create($data);
        $this->makeDefaultIfNeeded($request->user(), $address, $request->boolean('is_default'));

        return back()->with('success', 'Đã thêm địa chỉ giao hàng.');
    }

    public function updateAddress(Request $request, CustomerAddress $address)
    {
        abort_unless($address->user_id === $request->user()->id, 404);
        $data = $this->validateAddress($request);
        $address->update($data);
        $this->makeDefaultIfNeeded($request->user(), $address, $request->boolean('is_default'));

        return back()->with('success', 'Đã cập nhật địa chỉ giao hàng.');
    }

    public function destroyAddress(Request $request, CustomerAddress $address)
    {
        abort_unless($address->user_id === $request->user()->id, 404);
        $wasDefault = $address->is_default;
        $address->delete();
        if ($wasDefault) {
            $request->user()->addresses()->latest()->first()?->update(['is_default' => true]);
        }

        return back()->with('success', 'Đã xóa địa chỉ giao hàng.');
    }

    public function setDefaultAddress(Request $request, CustomerAddress $address)
    {
        abort_unless($address->user_id === $request->user()->id, 404);
        $this->makeDefaultIfNeeded($request->user(), $address, true);

        return back()->with('success', 'Đã đặt địa chỉ mặc định.');
    }

    private function validateAddress(Request $request): array
    {
        return $request->validate([
            'label' => ['required', 'string', 'max:80'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:500'],
            'province' => ['required', 'string', 'max:120'],
            'province_id' => ['required', 'integer'],
            'district' => ['required', 'string', 'max:120'],
            'district_id' => ['required', 'integer'],
            'ward' => ['required', 'string', 'max:120'],
            'ward_code' => ['required', 'string', 'max:20'],
            'is_default' => ['nullable', 'boolean'],
        ]);
    }

    private function makeDefaultIfNeeded($user, CustomerAddress $address, bool $makeDefault): void
    {
        if ($makeDefault || $user->addresses()->where('is_default', true)->count() === 0) {
            $user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        } else {
            $address->update(['is_default' => false]);
        }
    }
}
