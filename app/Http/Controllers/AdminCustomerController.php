<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminCustomerController extends Controller
{
    public function index(Request $request)
    {
        $keyword = trim((string) $request->query('keyword', ''));
        $customersQuery = User::query()
            ->whereNotIn('role', ['admin', 'staff'])
            ->withCount('orders');

        if ($keyword !== '') {
            $customersQuery->where(function ($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhereHas('orders', function ($orders) use ($keyword) {
                        $orders->where('customer_name', 'like', "%{$keyword}%")
                            ->orWhere('customer_phone', 'like', "%{$keyword}%")
                            ->orWhere('id', 'like', "%{$keyword}%")
                            ->orWhere('ghn_order_code', 'like', "%{$keyword}%");
                    });
            });
        }

        $customers = $customersQuery->orderByDesc('id')->paginate(20)->withQueryString();

        return view('admin.customers.index', compact('customers', 'keyword'));
    }

    public function update(Request $request, User $customer)
    {
        $this->ensureCustomer($customer);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($customer->id)],
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $customer->name = $data['name'];
        $customer->email = $data['email'];
        if (!empty($data['password'])) {
            $customer->password = Hash::make($data['password']);
        }
        $customer->save();

        return redirect()->route('admin.customers.index', ['keyword' => $request->input('keyword')])
            ->with('success', 'Đã cập nhật tài khoản khách hàng.');
    }

    private function ensureCustomer(User $user): void
    {
        abort_if(in_array($user->role, ['admin', 'staff'], true), 404);
    }
}
