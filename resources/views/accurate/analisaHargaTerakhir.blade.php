@extends('accurate.layouts.main')

@section('content')
    <div class="container mt-5">
        <div class="card">
            <div class="card-body">
                <div class="card-title">
                    <h5>Analisa Harga Beli & Jual Terakhir Produk</h5>
                </div>


                <div class="table-responsive mt-3">
                    <table class="table datatable">
                        <thead class="table-dark">
                            <tr>
                                <th>Nama Produk</th>
                                <th>Kode</th>
                                <th>Satuan</th>
                                <th>Harga Beli Terakhir</th>
                                <th>Harga Jual Terakhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($hasil as $produk)
                                @foreach ($produk['units'] as $unitName => $harga)
                                    <tr>
                                        <td>{{ $produk['nama'] }}</td>
                                        <td>{{ $produk['kode'] }}</td>
                                        <td>{{ $unitName }}</td>
                                        <td>Rp {{ number_format($harga['harga_beli_terakhir'], 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($harga['harga_jual_terakhir'], 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Tidak ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection
