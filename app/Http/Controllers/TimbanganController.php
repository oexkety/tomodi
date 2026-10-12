<?php

namespace App\Http\Controllers;

use App\Models\Timbangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimbanganController extends Controller
{
    private function periksaToken(Request $request): void
    {
        $token = (string) config('timbangan.api_key');

        abort_if($token === '', 503, 'Kunci API belum diatur.');

        abort_unless(
            hash_equals($token, (string) $request->bearerToken()),
            401,
            'Kunci API tidak sesuai.'
        );
    }

    public function index(Request $request)
    {
        $this->periksaToken($request);

        return response()->json(
            Timbangan::orderByDesc('id')->paginate(20)
        );
    }

    public function store(Request $request)
    {
        $this->periksaToken($request);

        abort_unless($request->isJson(), 415, 'Gunakan body JSON.');

        $data = $request->validate([
            'kode_transaksi' => 'required|string|max:50|regex:/^[A-Za-z0-9_-]+$/',
            'nomor_polisi' => 'required|string|max:20',
            'asal_tbs' => 'required|string|max:100',
            'tahap' => 'required|in:bruto,tara',
            'berat_kg' => 'required|integer|min:1|max:100000',
            'stabil' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value !== true) {
                        $fail('Pembacaan berat harus stabil (true).');
                    }
                },
            ],
        ]);

        $data['kode_transaksi'] = strtoupper($data['kode_transaksi']);
        $data['nomor_polisi'] = strtoupper($data['nomor_polisi']);
        $data['berat_kg'] = (int) $data['berat_kg'];

        return DB::transaction(function () use ($data) {
            // Transaksi baru dibuat ketika bruto diterima.
            if ($data['tahap'] === 'bruto') {
                Timbangan::firstOrCreate(
                    ['kode_transaksi' => $data['kode_transaksi']],
                    [
                        'nomor_polisi' => $data['nomor_polisi'],
                        'asal_tbs' => $data['asal_tbs'],
                    ]
                );
            }

            $timbangan = Timbangan::where(
                'kode_transaksi',
                $data['kode_transaksi']
            )->lockForUpdate()->first();

            abort_unless(
                $timbangan,
                422,
                'Kirim bruto untuk transaksi ini terlebih dahulu.'
            );

            abort_if(
                $timbangan->status === 'dibatalkan',
                409,
                'Transaksi sudah dibatalkan.'
            );

            // Identitas harus tetap sama untuk kode transaksi yang sama.
            abort_unless(
                $timbangan->nomor_polisi === $data['nomor_polisi'] &&
                $timbangan->asal_tbs === $data['asal_tbs'],
                409,
                'Identitas kendaraan atau asal TBS berbeda.'
            );

            $kolom = $data['tahap'] . '_kg';

            // Kenali kiriman ulang tanpa menimpa berat sebelumnya.
            if ($timbangan->{$kolom} !== null) {
                abort_unless(
                    $timbangan->{$kolom} === $data['berat_kg'],
                    409,
                    'Tahap ini sudah tersimpan dengan berat berbeda.'
                );

                return response()->json([
                    'message' => 'Kiriman ulang dikenali.',
                    'duplikat' => true,
                    'data' => $timbangan,
                ]);
            }

            if ($data['tahap'] === 'tara') {
                abort_unless(
                    $timbangan->bruto_kg !== null,
                    422,
                    'Bruto belum tersedia.'
                );

                abort_unless(
                    $data['berat_kg'] < $timbangan->bruto_kg,
                    422,
                    'Tara harus lebih kecil dari bruto.'
                );
            }

            $timbangan->update([
                $kolom => $data['berat_kg'],
                $data['tahap'] . '_at' => now(),
                'status' => $data['tahap'] === 'bruto'
                    ? 'menunggu_tara'
                    : 'selesai',
            ]);

            return response()->json([
                'message' => 'Berat ' . $data['tahap'] . ' tersimpan.',
                'duplikat' => false,
                'data' => $timbangan->fresh(),
            ], 201);
        }, 3);
    }
}
