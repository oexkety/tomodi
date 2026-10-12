<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    public function index(Request $request): View
    {
        return view('laporan.index', $this->dataLaporan($request));
    }

    public function csv(Request $request): StreamedResponse
    {
        $data = $this->dataLaporan($request);
        $filter = $data['filter'];

        $namaFile = 'laporan-tonase-'
            . $filter['tanggal_awal'] . '-'
            . $filter['tanggal_akhir'] . '.csv';

        return response()->streamDownload(function () use ($data) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");

            fputcsv($file, [
                'Asal TBS',
                'Jumlah transaksi',
                'Bruto (kg)',
                'Tara (kg)',
                'Netto (kg)',
                'Tonase (ton)',
            ], ';', '"', '');

            foreach ($data['rekap'] as $item) {
                fputcsv($file, [
                    $this->teksCsv($item->asal_tbs),
                    $item->jumlah_transaksi,
                    $item->total_bruto_kg,
                    $item->total_tara_kg,
                    $item->total_netto_kg,
                    number_format($item->total_netto_kg / 1000, 3, ',', ''),
                ], ';', '"', '');
            }

            fputcsv($file, [
                'TOTAL',
                $data['totalTransaksi'],
                $data['totalBrutoKg'],
                $data['totalTaraKg'],
                $data['totalNettoKg'],
                number_format($data['totalNettoKg'] / 1000, 3, ',', ''),
            ], ';', '"', '');

            fclose($file);
        }, $namaFile, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function dataLaporan(Request $request): array
    {
        $hariIni = now()->startOfDay();

        $validator = Validator::make([
            'tanggal_awal' => $request->query(
                'tanggal_awal',
                $hariIni->copy()->subDays(6)->format('Y-m-d')
            ),
            'tanggal_akhir' => $request->query(
                'tanggal_akhir',
                $hariIni->format('Y-m-d')
            ),
        ], [
            'tanggal_awal' => 'bail|required|date_format:Y-m-d',
            'tanggal_akhir' => 'bail|required|date_format:Y-m-d|after_or_equal:tanggal_awal',
        ], [
            'required' => ':attribute wajib diisi.',
            'date_format' => 'Format tanggal tidak valid.',
            'tanggal_akhir.after_or_equal' => 'Tanggal akhir harus sama atau setelah tanggal awal.',
        ], [
            'tanggal_awal' => 'Tanggal awal',
            'tanggal_akhir' => 'Tanggal akhir',
        ]);

        if ($validator->fails()) {
            throw (new ValidationException($validator))
                ->redirectTo(route('laporan.index'));
        }

        $filter = $validator->validated();
        $awal = Carbon::parse($filter['tanggal_awal'])->startOfDay();
        $akhir = Carbon::parse($filter['tanggal_akhir'])->startOfDay()->addDay();

        $rekap = DB::table('timbangan')
            ->where('status', 'selesai')
            ->whereNotNull('bruto_kg')
            ->whereNotNull('tara_kg')
            ->where('tara_at', '>=', $awal)
            ->where('tara_at', '<', $akhir)
            ->select('asal_tbs')
            ->selectRaw('COUNT(*) AS jumlah_transaksi')
            ->selectRaw('SUM(bruto_kg) AS total_bruto_kg')
            ->selectRaw('SUM(tara_kg) AS total_tara_kg')
            ->selectRaw('SUM(bruto_kg - tara_kg) AS total_netto_kg')
            ->groupBy('asal_tbs')
            ->orderByDesc('total_netto_kg')
            ->orderBy('asal_tbs')
            ->get();

        $totalTransaksi = (int) $rekap->sum('jumlah_transaksi');
        $totalBrutoKg = (int) $rekap->sum('total_bruto_kg');
        $totalTaraKg = (int) $rekap->sum('total_tara_kg');
        $totalNettoKg = (int) $rekap->sum('total_netto_kg');

        return compact(
            'filter',
            'rekap',
            'totalTransaksi',
            'totalBrutoKg',
            'totalTaraKg',
            'totalNettoKg'
        );
    }

    private function teksCsv(string $teks): string
    {
        // Nama asal TBS diperlakukan sebagai teks pada spreadsheet.
        return preg_match('/^\s*[=+\-@]/u', $teks)
            ? "'" . $teks
            : $teks;
    }
}
