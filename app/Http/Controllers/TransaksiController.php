<?php

namespace App\Http\Controllers;

use App\Models\Timbangan;
use Illuminate\View\View;

class TransaksiController extends Controller
{
    public function show(Timbangan $timbangan): View
    {
        return view('transaksi.show', compact('timbangan'));
    }
}
