<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | Tomodi</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: #f3f6f5;
            color: #18352b;
            font-family: "Segoe UI", sans-serif;
        }

        header {
            background: #123e30;
            color: white;
            padding: 24px max(20px, calc((100% - 1200px) / 2));
        }

        header strong { font-size: 26px; }
        header p { margin: 6px 0 0; color: #c5ded3; }

        main {
            max-width: 1240px;
            margin: auto;
            padding: 28px 20px;
        }

        h1 { margin: 0; font-size: 27px; }
        h2 { margin: 0; font-size: 19px; }
        .muted { color: #65786f; font-size: 14px; }

        .heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 24px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }

        .card, .panel {
            background: white;
            border: 1px solid #dce7e1;
            border-radius: 14px;
            padding: 22px;
        }

        .card p { margin: 0; color: #65786f; font-size: 14px; }
        .card strong { display: block; font-size: 30px; margin-top: 12px; }
        .card small { display: block; margin-top: 8px; color: #65786f; }
        .panel { margin-top: 22px; }

        .chart {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 12px;
            margin-top: 24px;
        }

        .day { text-align: center; }

        .plot {
            height: 160px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            border-bottom: 1px solid #dce7e1;
        }

        .bar {
            width: 65%;
            max-width: 60px;
            border-radius: 7px 7px 0 0;
            background: #218361;
        }

        .value { display: block; font-size: 13px; margin-bottom: 10px; }
        .day small { display: block; margin-top: 12px; color: #65786f; }
        .table-wrap { overflow-x: auto; margin-top: 18px; }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            white-space: nowrap;
        }

        th, td {
            padding: 14px 12px;
            border-bottom: 1px solid #e6ede9;
            text-align: left;
        }

        th { background: #f4f8f6; color: #65786f; font-size: 12px; }
        .num { text-align: right; font-variant-numeric: tabular-nums; }

        .badge {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 20px;
            font-size: 12px;
            background: #fff1d7;
            color: #875500;
        }

        .selesai { background: #e2f4eb; color: #176744; }
        .dibatalkan { background: #fde7e7; color: #a43c3c; }
        .empty { text-align: center; padding: 32px; color: #65786f; }

        .pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 20px;
        }

        .button {
            display: inline-block;
            padding: 9px 14px;
            border: 1px solid #cbdcd3;
            border-radius: 8px;
            color: #235b42;
            text-decoration: none;
            background: white;
            font-size: 14px;
        }

        .button:hover { background: #eef6f1; }

        @media (max-width: 760px) {
            .cards { grid-template-columns: repeat(2, 1fr); }
            .heading { align-items: flex-start; flex-direction: column; }
            .card, .panel { padding: 16px; }
            .chart { gap: 5px; }
            .value { font-size: 11px; }
            .pagination { flex-wrap: wrap; }
        }
        .filters {
    display: grid;
    grid-template-columns: 2fr 1.2fr 1fr 1fr;
    gap: 12px;
    margin-top: 20px;
}

.filters label {
    display: block;
    font-size: 13px;
    margin-bottom: 6px;
}

.filters input, .filters select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #cbdcd3;
    border-radius: 8px;
    font: inherit;
    background: white;
    color: #18352b;
}

.filter-actions {
    display: flex;
    gap: 10px;
    margin-top: 14px;
}

.filter-actions button {
    cursor: pointer;
}

.alert {
    padding: 14px 18px;
    margin-top: 18px;
    border-radius: 8px;
    background: #fde7e7;
    color: #a43c3c;
}

.alert ul {
    margin: 0;
    padding-left: 20px;
}

@media (max-width: 760px) {
    .filters {
        grid-template-columns: 1fr;
    }
}
.transaction-link {
    color: #176744;
    font-weight: 600;
}
    </style>
</head>

<body>

<header>
    <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap">
        <div>
            <strong>Tomodi</strong>
            <p>Pemantauan Timbangan TBS</p>
        </div>

        <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap">
            <span>
                {{ auth()->user()->name }}
                · {{ ucfirst(auth()->user()->role) }}
            </span>

            <a class="button" href="{{ route('laporan.index') }}">
                Laporan
            </a>

            @if (auth()->user()->role === 'admin')
                <a class="button" href="{{ route('users.index') }}">
                    Pengguna
                </a>
            @endif

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="button" type="submit" style="cursor:pointer">
                    Keluar
                </button>
            </form>
        </div>
    </div>
</header>

    <main>
        <div class="heading">
            <div>
                <h1>Dashboard Timbangan</h1>
                <p class="muted">
    {{ now()->format('d/m/Y H:i:s') }} WIB ·
    <span id="refresh-info">Diperbarui setiap 15 detik</span>
</p>
            </div>
            <a class="button" href="{{ request()->fullUrl() }}">Muat ulang</a>
        </div>

        <div class="cards">
            <div class="card">
                <p>Transaksi masuk hari ini</p>
                <strong>{{ number_format($masukHariIni, 0, ',', '.') }}</strong>
                <small>Bruto pertama diterima hari ini</small>
            </div>

            <div class="card">
                <p>Selesai hari ini</p>
                <strong>{{ number_format($selesaiHariIni, 0, ',', '.') }}</strong>
                <small>Tara diterima hari ini</small>
            </div>

            <div class="card">
                <p>Menunggu tara</p>
                <strong>{{ number_format($menungguTara, 0, ',', '.') }}</strong>
                <small>Seluruh transaksi yang masih terbuka</small>
            </div>

            <div class="card">
                <p>Tonase hari ini</p>
                <strong>
                    {{ number_format($tonaseHariIni, 3, ',', '.') }}
                    <span style="font-size:16px">ton</span>
                </strong>
                <small>Netto dari transaksi selesai hari ini</small>
            </div>
        </div>

        <section class="panel">
            <h2>Tonase 7 hari terakhir</h2>
            <p class="muted">
                Netto transaksi selesai, berdasarkan tanggal tara diterima.
            </p>

            <div class="chart" role="img" aria-label="Grafik tonase tujuh hari terakhir">
                @foreach ($grafik as $hari)
                    <div class="day">
                        <span class="value">
                            {{ number_format($hari['tonase'], 3, ',', '.') }} t
                        </span>
                        <div class="plot">
                            <div
                                class="bar"
                                style="height: {{ $hari['tonase'] / $maksTonase * 100 }}%"
                            ></div>
                        </div>
                        <small>{{ $hari['label'] }}</small>
                    </div>
                @endforeach
            </div>

            @if ($grafik->sum('tonase') == 0)
                <p class="muted">
                    Belum ada transaksi selesai dalam periode ini.
                </p>
            @endif
        </section>

        <section class="panel">
            <h2>Daftar transaksi</h2>
            <p class="muted">Diurutkan dari transaksi yang terakhir diperbarui.</p>
            @if ($errors->any())
    <div class="alert" role="alert">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form id="filter-transaksi" method="GET" action="{{ route('dashboard') }}">
    <div class="filters">
        <div>
            <label for="q">Pencarian</label>
            <input
                id="q"
                name="q"
                value="{{ $filter['q'] ?? '' }}"
                maxlength="100"
                placeholder="Kode, nomor polisi, atau asal TBS"
            >
        </div>

        <div>
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="">Semua status</option>

                <option value="menunggu_bruto"
                    @selected(($filter['status'] ?? '') === 'menunggu_bruto')>
                    Menunggu bruto
                </option>

                <option value="menunggu_tara"
                    @selected(($filter['status'] ?? '') === 'menunggu_tara')>
                    Menunggu tara
                </option>

                <option value="selesai"
                    @selected(($filter['status'] ?? '') === 'selesai')>
                    Selesai
                </option>

                <option value="dibatalkan"
                    @selected(($filter['status'] ?? '') === 'dibatalkan')>
                    Dibatalkan
                </option>
            </select>
        </div>

        <div>
            <label for="tanggal_awal">Tanggal masuk mulai</label>
            <input
                id="tanggal_awal"
                type="date"
                name="tanggal_awal"
                value="{{ $filter['tanggal_awal'] ?? '' }}"
            >
        </div>

        <div>
            <label for="tanggal_akhir">Tanggal masuk sampai</label>
            <input
                id="tanggal_akhir"
                type="date"
                name="tanggal_akhir"
                value="{{ $filter['tanggal_akhir'] ?? '' }}"
            >
        </div>
    </div>

    <div class="filter-actions">
        <button class="button" type="submit">Terapkan</button>
        <a class="button" href="{{ route('dashboard') }}">Reset</a>
    </div>
</form>

<p class="muted">
    Filter berlaku untuk daftar transaksi.
    Tanggal masuk mengacu pada waktu transaksi pertama diterima.
</p>

<p class="muted">
    Menampilkan {{ $transaksi->firstItem() ?? 0 }}–{{ $transaksi->lastItem() ?? 0 }}
    dari {{ $transaksi->total() }} transaksi yang sesuai.
</p>
                Diurutkan dari transaksi yang terakhir diperbarui.
            </p>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Kode transaksi</th>
                            <th>Nomor polisi</th>
                            <th>Asal TBS</th>
                            <th class="num">Bruto (kg)</th>
                            <th class="num">Tara (kg)</th>
                            <th class="num">Netto (kg)</th>
                            <th class="num">Tonase (t)</th>
                            <th>Status</th>
                            <th>Diperbarui</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($transaksi as $item)
                            <tr>
                                <td>
                                    <a class="transaction-link" href="{{ route('transaksi.show', $item) }}">
                                        {{ $item->kode_transaksi }}
                                    </a>
                                </td>
                                <td>{{ $item->nomor_polisi }}</td>
                                <td>{{ $item->asal_tbs }}</td>

                                <td class="num">
                                    {{ $item->bruto_kg !== null ? number_format($item->bruto_kg, 0, ',', '.') : '—' }}
                                </td>
                                <td class="num">
                                    {{ $item->tara_kg !== null ? number_format($item->tara_kg, 0, ',', '.') : '—' }}
                                </td>
                                <td class="num">
                                    {{ $item->netto_kg !== null ? number_format($item->netto_kg, 0, ',', '.') : '—' }}
                                </td>
                                <td class="num">
                                    {{ $item->tonase !== null ? number_format($item->tonase, 3, ',', '.') : '—' }}
                                </td>

                                <td>
                                    <span class="badge {{ $item->status === 'selesai' ? 'selesai' : ($item->status === 'dibatalkan' ? 'dibatalkan' : '') }}">
                                        {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                    </span>
                                </td>

                                <td>
                                    {{ $item->updated_at?->format('d/m/Y H:i') ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                 <td colspan="9" class="empty">
                                    Tidak ada transaksi yang sesuai dengan pencarian atau filter.
                                 </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($transaksi->hasPages())
                <div class="pagination">
                    <span class="muted">
                        Halaman {{ $transaksi->currentPage() }}
                        dari {{ $transaksi->lastPage() }}
                    </span>

                    <div>
                        @if ($transaksi->previousPageUrl())
                            <a class="button" href="{{ $transaksi->previousPageUrl() }}">
                                Sebelumnya
                            </a>
                        @endif

                        @if ($transaksi->nextPageUrl())
                            <a class="button" href="{{ $transaksi->nextPageUrl() }}">
                                Berikutnya
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </section>
    </main>
    <script>
    const formFilter = document.getElementById('filter-transaksi');
    const infoRefresh = document.getElementById('refresh-info');
    let refreshDijeda = false;

    formFilter.addEventListener('focusin', () => {
        refreshDijeda = true;
        infoRefresh.textContent =
            'Pembaruan dijeda. Klik Terapkan untuk melanjutkan.';
    });

    setInterval(() => {
        if (!refreshDijeda) {
            window.location.reload();
        }
    }, 15000);
</script>
</body>
</html>
