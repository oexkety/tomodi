<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail transaksi | Tomodi</title>

    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #f3f6f5; color: #18352b; font-family: "Segoe UI", sans-serif; }
        header { background: #123e30; color: white; padding: 22px max(20px, calc((100% - 1100px) / 2)); }
        header strong { font-size: 26px; }
        main { max-width: 1140px; margin: auto; padding: 28px 20px; }
        h1 { margin: 0; font-size: 27px; }
        h2 { margin: 0 0 16px; font-size: 19px; }
        .row { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
        .actions { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .muted { color: #65786f; font-size: 14px; }
        .button { display: inline-block; padding: 9px 14px; border: 1px solid #cbdcd3; border-radius: 8px; color: #235b42; text-decoration: none; background: white; font: inherit; font-size: 14px; cursor: pointer; }
        .button:hover { background: #eef6f1; }
        .panel, .card { background: white; border: 1px solid #dce7e1; border-radius: 14px; padding: 22px; }
        .panel { margin-top: 22px; }
        .cards { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-top: 22px; }
        .card p { margin: 0 0 12px; color: #65786f; font-size: 14px; }
        .card strong { font-size: 28px; }
        .info { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; margin: 0; }
        dt { color: #65786f; font-size: 14px; }
        dd { margin: 6px 0 0; font-weight: 600; overflow-wrap: anywhere; }
        .badge { display: inline-block; padding: 6px 10px; border-radius: 20px; background: #fff1d7; color: #875500; font-size: 13px; }
        .selesai { background: #e2f4eb; color: #176744; }
        .dibatalkan { background: #fde7e7; color: #a43c3c; }

        @media (max-width: 760px) {
            .cards { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 520px) {
            .info { grid-template-columns: 1fr; }
        }

        @page { size: A4; margin: 16mm; }

        @media print {
            .no-print { display: none !important; }
            body { background: white; color: black; }
            main { max-width: none; padding: 0; }
            .cards { grid-template-columns: repeat(4, minmax(0, 1fr)); }
            .panel, .card { padding: 16px; border-color: #bbb; break-inside: avoid; }
            .card strong { font-size: 24px; }
        }
    </style>
</head>
<body>
    <header class="no-print">
        <div class="row">
            <strong>Tomodi</strong>

            <div class="actions">
                <span>
                    {{ auth()->user()->name }}
                    · {{ ucfirst(auth()->user()->role) }}
                </span>

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
        <div class="row">
            <div>
                <h1>Tomodi · Detail transaksi</h1>
                <p class="muted">Pemantauan Timbangan TBS</p>
            </div>

            <div class="actions no-print">
    <a class="button" href="{{ route('dashboard') }}">Dashboard</a>

    <a class="button" href="{{ route('laporan.index') }}">Laporan</a>

    <a class="button" href="{{ route('transaksi.show', $timbangan) }}">
        Muat ulang
    </a>

    <button class="button" type="button" onclick="window.print()">
        Cetak
    </button>
</div>
        </div>

        <section class="panel">
            <h2>Identitas transaksi</h2>

            <dl class="info">
                <div>
                    <dt>Kode transaksi</dt>
                    <dd>{{ $timbangan->kode_transaksi }}</dd>
                </div>

                <div>
                    <dt>Nomor polisi</dt>
                    <dd>{{ $timbangan->nomor_polisi }}</dd>
                </div>

                <div>
                    <dt>Asal TBS</dt>
                    <dd>{{ $timbangan->asal_tbs }}</dd>
                </div>

                <div>
                    <dt>Status</dt>
                    <dd>
                        <span class="badge {{ $timbangan->status === 'selesai' ? 'selesai' : ($timbangan->status === 'dibatalkan' ? 'dibatalkan' : '') }}">
                            {{ ucfirst(str_replace('_', ' ', $timbangan->status)) }}
                        </span>
                    </dd>
                </div>
            </dl>
        </section>

        <div class="cards">
            @foreach ([
                'Bruto (kg)' => $timbangan->bruto_kg,
                'Tara (kg)' => $timbangan->tara_kg,
                'Netto (kg)' => $timbangan->netto_kg,
            ] as $label => $nilai)
                <div class="card">
                    <p>{{ $label }}</p>
                    <strong>
                        {{ $nilai !== null ? number_format($nilai, 0, ',', '.') : '—' }}
                    </strong>
                </div>
            @endforeach

            <div class="card">
                <p>Tonase (ton)</p>
                <strong>
                    {{ $timbangan->tonase !== null ? number_format($timbangan->tonase, 3, ',', '.') : '—' }}
                </strong>
            </div>
        </div>

        <p class="muted">
            Netto = bruto − tara. Tonase = netto ÷ 1.000.
            Keduanya ditampilkan untuk transaksi berstatus selesai.
        </p>

        <section class="panel">
            <h2>Waktu penerimaan</h2>

            <dl class="info">
                <div>
                    <dt>Bruto diterima</dt>
                    <dd>
                        {{ $timbangan->bruto_at?->format('d/m/Y H:i:s') ?? 'Belum diterima' }}
                    </dd>
                </div>

                <div>
                    <dt>Tara diterima</dt>
                    <dd>
                        {{ $timbangan->tara_at?->format('d/m/Y H:i:s') ?? 'Belum diterima' }}
                    </dd>
                </div>

                <div>
                    <dt>Transaksi dibuat</dt>
                    <dd>
                        {{ $timbangan->created_at?->format('d/m/Y H:i:s') ?? '—' }}
                    </dd>
                </div>

                <div>
                    <dt>Terakhir diperbarui</dt>
                    <dd>
                        {{ $timbangan->updated_at?->format('d/m/Y H:i:s') ?? '—' }}
                    </dd>
                </div>
            </dl>

            <p class="muted">
                Waktu mengacu pada penerimaan data oleh website, dalam WIB.
            </p>
        </section>
    </main>
</body>
</html>
