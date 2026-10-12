<?php

namespace App\Http\Controllers;

use App\Models\Timbangan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $rules = [
            'q' => 'nullable|string|max:100',
            'status' => 'nullable|in:menunggu_bruto,menunggu_tara,selesai,dibatalkan',
            'tanggal_awal' => 'bail|nullable|date_format:Y-m-d',
            'tanggal_akhir' => 'bail|nullable|date_format:Y-m-d',
        ];

        if ($request->filled('tanggal_awal')) {
            $rules['tanggal_akhir'] .= '|after_or_equal:tanggal_awal';
        }

        $validator = Validator::make($request->query(), $rules, [
            'q.string' => 'Pencarian harus berupa teks.',
            'q.max' => 'Pencarian maksimal 100 karakter.',
            'status.in' => 'Status filter tidak valid.',
            'date_format' => 'Tanggal harus menggunakan format tahun-bulan-hari.',
            'tanggal_akhir.after_or_equal' => 'Tanggal akhir harus sama atau setelah tanggal awal.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('dashboard')->withErrors($validator);
        }

        $filter = $validator->validated();

        // Ringkasan dashboard.
        $hariIni = now()->startOfDay();
        $besok = $hariIni->copy()->addDay();
        $awalGrafik = $hariIni->copy()->subDays(6);

        $selesai = Timbangan::query()
            ->where('status', 'selesai')
            ->whereNotNull('bruto_kg')
            ->whereNotNull('tara_kg');

        $masukHariIni = Timbangan::where('created_at', '>=', $hariIni)
            ->where('created_at', '<', $besok)
            ->count();

        $selesaiHariIni = (clone $selesai)
            ->where('tara_at', '>=', $hariIni)
            ->where('tara_at', '<', $besok)
            ->count();

        $menungguTara = Timbangan::where('status', 'menunggu_tara')->count();

        $tonaseHariIni = (float) (clone $selesai)
            ->where('tara_at', '>=', $hariIni)
            ->where('tara_at', '<', $besok)
            ->sum(DB::raw('bruto_kg - tara_kg')) / 1000;

        $rekap = (clone $selesai)
            ->where('tara_at', '>=', $awalGrafik)
            ->where('tara_at', '<', $besok)
            ->selectRaw(
                'DATE(tara_at) AS tanggal, SUM(bruto_kg - tara_kg) / 1000.0 AS total_ton'
            )
            ->groupByRaw('DATE(tara_at)')
            ->pluck('total_ton', 'tanggal');

        $grafik = collect(range(0, 6))->map(function ($i) use ($awalGrafik, $rekap) {
            $tanggal = $awalGrafik->copy()->addDays($i);

            return [
                'label' => $tanggal->format('d/m'),
                'tonase' => (float) $rekap->get($tanggal->format('Y-m-d'), 0),
            ];
        });

        $maksTonase = max(1, $grafik->max('tonase'));

        // Pencarian dan filter daftar transaksi.
        $query = Timbangan::query();
        $kata = trim($filter['q'] ?? '');

        if ($kata !== '') {
            $query->where(function ($query) use ($kata) {
                $query->where('kode_transaksi', 'like', '%' . $kata . '%')
                    ->orWhere('nomor_polisi', 'like', '%' . $kata . '%')
                    ->orWhere('asal_tbs', 'like', '%' . $kata . '%');
            });
        }

        if (!empty($filter['status'])) {
            $query->where('status', $filter['status']);
        }

        if (!empty($filter['tanggal_awal'])) {
            $query->where(
                'created_at',
                '>=',
                Carbon::parse($filter['tanggal_awal'])->startOfDay()
            );
        }

        if (!empty($filter['tanggal_akhir'])) {
            $query->where(
                'created_at',
                '<',
                Carbon::parse($filter['tanggal_akhir'])->startOfDay()->addDay()
            );
        }

        $transaksi = $query->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('dashboard', compact(
            'masukHariIni',
            'selesaiHariIni',
            'menungguTara',
            'tonaseHariIni',
            'grafik',
            'maksTonase',
            'transaksi',
            'filter'
        ));
    }
}
