<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

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
            'password' => 'required|confirmed|min:8',
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

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
        ]);

        $status = Password::sendResetLink(['email' => $data['email']]);

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', 'Đã gửi liên kết đặt lại mật khẩu. Vui lòng kiểm tra email.');
        }

        return back()->withErrors(['email' => 'Không tìm thấy tài khoản với email này.']);
    }

    public function showResetPassword(string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => request('email'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $status = Password::reset(
            $data,
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', 'Đặt lại mật khẩu thành công. Bạn có thể đăng nhập.');
        }

        return back()->withErrors(['email' => 'Liên kết đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.']);
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