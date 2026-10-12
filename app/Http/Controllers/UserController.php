<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::select('id', 'name', 'email', 'role', 'created_at')
            ->orderByDesc('id')
            ->paginate(10);

        return view('users.index', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $email = $request->input('email');

        if (is_string($email)) {
            $request->merge([
                'email' => strtolower(trim($email)),
            ]);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'role' => 'required|in:admin,operator,pimpinan',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'required' => ':attribute wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'role.in' => 'Peran tidak valid.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
        ], [
            'name' => 'Nama',
            'email' => 'Email',
            'role' => 'Peran',
            'password' => 'Password',
        ]);

        $user = new User;
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];
        $user->password = Hash::make($data['password']);
        $user->save();

        return redirect()->route('users.index')
            ->with('success', 'Akun ' . $user->email . ' berhasil ditambahkan.');
    }
}
