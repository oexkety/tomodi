<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timbangan', function (Blueprint $table) {
            $table->id();

            $table->string('kode_transaksi', 50)->unique();
            $table->string('nomor_polisi', 20);
            $table->string('asal_tbs', 100);

            $table->unsignedInteger('bruto_kg')->nullable();
            $table->unsignedInteger('tara_kg')->nullable();

            $table->string('status', 20)
                ->default('menunggu_bruto');

            $table->timestamp('bruto_at')->nullable();
            $table->timestamp('tara_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timbangan');
    }
};
