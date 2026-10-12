<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateTomodiUser extends Command
{
    protected $signature = 'tomodi:buat-user';

    protected $description = 'Membuat akun pengguna Tomodi';

    public function handle(): int
    {
        $data = [
            'name' => trim((string) $this->ask('Nama pengguna')),
            'email' => strtolower(trim((string) $this->ask('Email'))),
            'role' => $this->choice(
                'Peran',
                ['admin', 'operator', 'pimpinan'],
                0
            ),
            'password' => (string) $this->secret(
                'Password (minimal 8 karakter)'
            ),
            'password_confirmation' => (string) $this->secret(
                'Ulangi password'
            ),
        ];

        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'role' => 'required|in:admin,operator,pimpinan',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'required' => ':attribute wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];
        $user->password = Hash::make($data['password']);
        $user->save();

        $this->info(
            'Akun ' . $user->email .
            ' berhasil dibuat (' . $user->role . ').'
        );

        return self::SUCCESS;
    }
}
