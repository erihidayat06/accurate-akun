<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\SimpleExcel\SimpleExcelWriter;

class ExportController extends Controller
{
    public function exportProduk()
    {
        // Lokasi file CSV
        $path = storage_path('app/public/produk.csv');

        // Tulis isi CSV
        SimpleExcelWriter::create($path)
            ->addRow(['nama' => 'Produk A', 'harga' => 1000])
            ->addRow(['nama' => 'Produk B', 'harga' => 2000]);

        return response()->download($path);
    }
}
