<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminStaffController extends Controller
{
    public function index()
    {
        $staffMembers = User::where('role', 'staff')->orderBy('name')->paginate(20);

        return view('admin.staff.index', compact('staffMembers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $staff = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'staff',
        ]);
        $staff->email_verified_at = now();
        $staff->save();

        return redirect()->route('admin.staff.index')->with('success', 'Đã tạo tài khoản nhân viên.');
    }

    public function update(Request $request, User $staff)
    {
        $this->ensureStaff($staff);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($staff->id)],
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $staff->name = $data['name'];
        $staff->email = $data['email'];
        if (!empty($data['password'])) {
            $staff->password = Hash::make($data['password']);
        }
        $staff->save();

        return redirect()->route('admin.staff.index')->with('success', 'Đã cập nhật nhân viên.');
    }

    public function destroy(User $staff)
    {
        $this->ensureStaff($staff);
        $staff->delete();

        return redirect()->route('admin.staff.index')->with('success', 'Đã xóa tài khoản nhân viên.');
    }

    private function ensureStaff(User $user): void
    {
        abort_unless($user->role === 'staff', 404);
    }
}
