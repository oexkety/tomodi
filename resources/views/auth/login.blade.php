<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Tomodi</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background: #edf4f0;
            color: #18352b;
            font-family: "Segoe UI", sans-serif;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            padding: 32px;
            border: 1px solid #dce7e1;
            border-radius: 16px;
            background: white;
            box-shadow: 0 12px 36px #123e3010;
        }

        .brand {
            color: #123e30;
            font-size: 30px;
            font-weight: 700;
        }

        h1 { margin: 24px 0 8px; font-size: 24px; }
        p { color: #65786f; font-size: 14px; margin: 0 0 24px; }

        label {
            display: block;
            margin: 18px 0 8px;
            font-size: 14px;
            font-weight: 600;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbdcd3;
            border-radius: 8px;
            font: inherit;
        }

        input:focus {
            outline: 2px solid #218361;
            outline-offset: 2px;
        }

        button {
            width: 100%;
            margin-top: 24px;
            padding: 12px;
            border: 0;
            border-radius: 8px;
            background: #123e30;
            color: white;
            font: inherit;
            font-weight: 600;
            cursor: pointer;
        }

        button:hover { background: #195239; }

        .error {
            padding: 12px;
            border-radius: 8px;
            background: #fde7e7;
            color: #a43c3c;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <main class="login-card">
        <div class="brand">Tomodi</div>
        <h1>Masuk ke dashboard</h1>
        <p>Pemantauan Timbangan TBS</p>

        @if ($errors->any())
            <div class="error" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}">
            @csrf

            <label for="email">Email</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                maxlength="255"
                autocomplete="username"
                required
                autofocus
            >

            <label for="password">Password</label>
            <input
                id="password"
                name="password"
                type="password"
                autocomplete="current-password"
                required
            >

            <button type="submit">Masuk</button>
        </form>
    </main>
</body>
</html>
