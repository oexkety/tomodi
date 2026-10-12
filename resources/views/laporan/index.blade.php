<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan | Tomodi</title>

    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #f3f6f5; color: #18352b; font-family: "Segoe UI", sans-serif; }
        header { background: #123e30; color: white; padding: 22px max(20px, calc((100% - 1200px) / 2)); }
        header strong { font-size: 26px; }
        main { max-width: 1240px; margin: auto; padding: 28px 20px; }
        h1 { margin: 0; font-size: 27px; }
        h2 { margin: 0 0 12px; font-size: 19px; }
        .row { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
        .actions { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .muted { color: #65786f; font-size: 14px; }
        .button { display: inline-block; padding: 9px 14px; border: 1px solid #cbdcd3; border-radius: 8px; color: #235b42; text-decoration: none; background: white; font: inherit; font-size: 14px; cursor: pointer; }
        .button:hover { background: #eef6f1; }
        .panel, .card { background: white; border: 1px solid #dce7e1; border-radius: 14px; padding: 22px; }
        .panel { margin-top: 22px; }
        .cards { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; margin-top: 22px; }
        .card p { margin: 0 0 12px; color: #65786f; font-size: 14px; }
        .card strong { font-size: 30px; }
        .filters { display: flex; gap: 16px; flex-wrap: wrap; align-items: end; }
        label { display: block; margin-bottom: 6px; font-size: 14px; }
        input { padding: 10px 12px; border: 1px solid #cbdcd3; border-radius: 8px; font: inherit; }
        .alert { padding: 14px 18px; border-radius: 8px; background: #fde7e7; color: #a43c3c; margin-top: 20px; }
        .alert ul { margin: 0; padding-left: 20px; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th, td { padding: 14px 12px; border-bottom: 1px solid #e6ede9; text-align: left; }
        th, tfoot { background: #f4f8f6; }
        td:first-child { overflow-wrap: anywhere; min-width: 160px; }
        .num { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
        .empty { text-align: center; padding: 28px; color: #65786f; }

        @media (max-width: 760px) {
            .cards { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <header>
        <div class="row">
            <strong>Tomodi</strong>

            <div class="actions">
                <span>
                    {{ auth()->user()->name }}
                    · {{ ucfirst(auth()->user()->role) }}
                </span>

                <a class="button" href="{{ route('dashboard') }}">Dashboard</a>

                @if (auth()->user()->role === 'admin')
                    <a class="button" href="{{ route('users.index') }}">Pengguna</a>
                @endif

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="button" type="submit">Keluar</button>
                </form>
            </div>
        </div>
    </header>

    <main>
        <h1>Laporan tonase</h1>
        <p class="muted">
            Rekap transaksi selesai per asal TBS,
            berdasarkan tanggal tara diterima dalam WIB.
        </p>

        @if ($errors->any())
            <div class="alert" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="panel">
            <form class="filters" method="GET" action="{{ route('laporan.index') }}">
                <div>
                    <label for="tanggal_awal">Tanggal awal</label>
                    <input
                        id="tanggal_awal"
                        type="date"
                        name="tanggal_awal"
                        value="{{ $filter['tanggal_awal'] }}"
                        required
                    >
                </div>

                <div>
                    <label for="tanggal_akhir">Tanggal akhir</label>
                    <input
                        id="tanggal_akhir"
                        type="date"
                        name="tanggal_akhir"
                        value="{{ $filter['tanggal_akhir'] }}"
                        required
                    >
                </div>

                <button class="button" type="submit">Terapkan</button>
                <a class="button" href="{{ route('laporan.index') }}">
                    7 hari terakhir
                </a>
            </form>
        </section>

        <div class="cards">
            <div class="card">
                <p>Transaksi selesai</p>
                <strong>{{ number_format($totalTransaksi, 0, ',', '.') }}</strong>
            </div>

            <div class="card">
                <p>Total netto (kg)</p>
                <strong>{{ number_format($totalNettoKg, 0, ',', '.') }}</strong>
            </div>

            <div class="card">
                <p>Total tonase (ton)</p>
                <strong>{{ number_format($totalNettoKg / 1000, 3, ',', '.') }}</strong>
            </div>
        </div>

        <section class="panel">
            <div class="row">
                <div>
                    <h2>Rekap per asal TBS</h2>
                    <p class="muted">
                        Periode {{ $filter['tanggal_awal'] }}
                        sampai {{ $filter['tanggal_akhir'] }}
                    </p>
                </div>

                <a class="button" href="{{ route('laporan.csv', $filter) }}">
                    Unduh CSV
                </a>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Asal TBS</th>
                            <th class="num">Transaksi</th>
                            <th class="num">Bruto (kg)</th>
                            <th class="num">Tara (kg)</th>
                            <th class="num">Netto (kg)</th>
                            <th class="num">Tonase (ton)</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($rekap as $item)
                            <tr>
                                <td>{{ $item->asal_tbs }}</td>
                                <td class="num">{{ number_format($item->jumlah_transaksi, 0, ',', '.') }}</td>
                                <td class="num">{{ number_format($item->total_bruto_kg, 0, ',', '.') }}</td>
                                <td class="num">{{ number_format($item->total_tara_kg, 0, ',', '.') }}</td>
                                <td class="num">{{ number_format($item->total_netto_kg, 0, ',', '.') }}</td>
                                <td class="num">{{ number_format($item->total_netto_kg / 1000, 3, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty">
                                    Belum ada transaksi selesai pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    <tfoot>
                        <tr>
                            <th>TOTAL</th>
                            <th class="num">{{ number_format($totalTransaksi, 0, ',', '.') }}</th>
                            <th class="num">{{ number_format($totalBrutoKg, 0, ',', '.') }}</th>
                            <th class="num">{{ number_format($totalTaraKg, 0, ',', '.') }}</th>
                            <th class="num">{{ number_format($totalNettoKg, 0, ',', '.') }}</th>
                            <th class="num">{{ number_format($totalNettoKg / 1000, 3, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
