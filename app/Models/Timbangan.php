<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Timbangan extends Model
{
    protected $table = 'timbangan';

    protected $fillable = [
        'kode_transaksi',
        'nomor_polisi',
        'asal_tbs',
        'bruto_kg',
        'tara_kg',
        'status',
        'bruto_at',
        'tara_at',
    ];

    protected $casts = [
        'bruto_kg' => 'integer',
        'tara_kg' => 'integer',
        'bruto_at' => 'datetime',
        'tara_at' => 'datetime',
    ];

    protected $appends = [
        'netto_kg',
        'tonase',
    ];

    public function getNettoKgAttribute(): ?int
    {
        if (
            $this->status !== 'selesai' ||
            $this->bruto_kg === null ||
            $this->tara_kg === null
        ) {
            return null;
        }

        return $this->bruto_kg - $this->tara_kg;
    }

    public function getTonaseAttribute(): ?float
    {
        if ($this->netto_kg === null) {
            return null;
        }

        return $this->netto_kg / 1000;
    }
}
