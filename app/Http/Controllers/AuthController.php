<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Hiển thị trang đăng ký
    public function showRegister()
    {
        return view('auth.register');
    }

    // Xử lý đăng ký
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:6',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'user',
        ]);

        // Gửi email xác thực
        $user->sendEmailVerificationNotification();

        Auth::login($user);

        return redirect()->route('verification.notice')
            ->with('success', 'Vui lòng kiểm tra email để xác thực tài khoản.');
    }

    // Hiển thị trang đăng nhập
    public function showLogin()
    {
        return view('auth.login');
    }

    // Xử lý đăng nhập
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Nếu là Admin -> Vào trang Quản trị Admin
            if ($user->role === 'admin') {
                return redirect()->route('air_conditioners.index')
                    ->with('success', 'Đăng nhập trang quản trị thành công.');
            }

            if ($user->role === 'staff') {
                return redirect()->route('air_conditioners.index')
                    ->with('success', 'Đăng nhập trang nhân viên thành công.');
            }

            // Nếu là User thường -> Về trang chủ Siêu thị Bán hàng
            return redirect()->route('shop.index')
                ->with('success', 'Đăng nhập thành công.');
        }

        return back()
            ->withErrors([
                'email' => 'Email hoặc mật khẩu không chính xác.'
            ])
            ->onlyInput('email');
    }

    // Xử lý đăng xuất
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('shop.index')
            ->with('success', 'Bạn đã đăng xuất thành công.');
    }
}