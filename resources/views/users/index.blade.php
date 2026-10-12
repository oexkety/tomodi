<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pengguna | Tomodi</title>

    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #f3f6f5; color: #18352b; font-family: "Segoe UI", sans-serif; }
        header { background: #123e30; color: white; padding: 22px max(20px, calc((100% - 1200px) / 2)); }
        header strong { font-size: 26px; }
        .row { display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; }
        .nav { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        main { max-width: 1240px; margin: auto; padding: 28px 20px; }
        h1 { margin: 0; font-size: 27px; }
        h2 { margin: 0 0 8px; font-size: 19px; }
        .muted { color: #65786f; font-size: 14px; }
        .grid { display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr); gap: 22px; margin-top: 24px; align-items: start; }
        .panel { background: white; border: 1px solid #dce7e1; border-radius: 14px; padding: 22px; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th, td { padding: 13px 10px; border-bottom: 1px solid #e6ede9; text-align: left; }
        th { background: #f4f8f6; color: #65786f; }
        td { overflow-wrap: anywhere; }
        .badge { display: inline-block; padding: 5px 9px; border-radius: 20px; background: #e2f4eb; color: #176744; }
        .button { display: inline-block; padding: 9px 14px; border: 1px solid #cbdcd3; border-radius: 8px; color: #235b42; text-decoration: none; background: white; font: inherit; font-size: 14px; cursor: pointer; }
        .button:hover { background: #eef6f1; }
        .primary { width: 100%; background: #176744; color: white; border-color: #176744; }
        .primary:hover { background: #123e30; }
        label { display: block; margin: 16px 0 6px; font-size: 14px; font-weight: 600; }
        input, select { width: 100%; padding: 11px 12px; border: 1px solid #cbdcd3; border-radius: 8px; font: inherit; }
        input:focus, select:focus { outline: 2px solid #218361; outline-offset: 2px; }
        .notice { padding: 14px 18px; border-radius: 8px; margin-top: 20px; }
        .success { background: #e2f4eb; color: #176744; }
        .error { background: #fde7e7; color: #a43c3c; }
        .error ul { margin: 0; padding-left: 20px; }
        .pagination { margin-top: 18px; }
        @media (max-width: 900px) {
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <header>
        <div class="row">
            <strong>Tomodi</strong>

            <nav class="nav" aria-label="Menu utama">
                <span>{{ auth()->user()->name }} · Admin</span>

                <a class="button" href="{{ route('dashboard') }}">Dashboard</a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="button" type="submit">Keluar</button>
                </form>
            </nav>
        </div>
    </header>

    <main>
        <h1>Pengguna</h1>
        <p class="muted">Daftar dan penambahan akun Tomodi. Halaman ini khusus admin.</p>

        @if (session('success'))
            <div class="notice success" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="notice error" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid">
            <section class="panel">
                <h2>Daftar akun</h2>
                <p class="muted">
                    Total {{ $users->total() }} akun · Akun terbaru ditampilkan lebih dulu.
                </p>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Peran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <span class="badge">{{ ucfirst($user->role) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">Belum ada akun.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($users->hasPages())
                    <div class="row pagination">
                        <span class="muted">
                            Halaman {{ $users->currentPage() }} / {{ $users->lastPage() }}
                        </span>
                        <div>
                            @if ($users->previousPageUrl())
                                <a class="button" href="{{ $users->previousPageUrl() }}">Sebelumnya</a>
                            @endif

                            @if ($users->nextPageUrl())
                                <a class="button" href="{{ $users->nextPageUrl() }}">Berikutnya</a>
                            @endif
                        </div>
                    </div>
                @endif
            </section>

            <section class="panel">
                <h2>Tambah akun</h2>
                <p class="muted">Email digunakan sebagai identitas login.</p>

                <form method="POST" action="{{ route('users.store') }}">
                    @csrf

                    <label for="name">Nama</label>
                    <input
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        maxlength="255"
                        autocomplete="name"
                        required
                    >

                    <label for="email">Email</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        maxlength="255"
                        autocomplete="off"
                        required
                    >

                    <label for="role">Peran</label>
                    <select id="role" name="role" required>
                        <option value="pimpinan" @selected(old('role', 'pimpinan') === 'pimpinan')>
                            Pimpinan
                        </option>
                        <option value="operator" @selected(old('role') === 'operator')>
                            Operator
                        </option>
                        <option value="admin" @selected(old('role') === 'admin')>
                            Admin
                        </option>
                    </select>

                    <label for="password">Password</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >

                    <label for="password_confirmation">Ulangi password</label>
                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >

                    <p class="muted">Password minimal 8 karakter.</p>

                    <button class="button primary" type="submit">Simpan akun</button>
                </form>
            </section>
        </div>
    </main>
</body>
</html>
