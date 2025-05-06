@extends('accurate.layouts.main')

@section('content')
    <div class="container mt-5">
        <div class="card">
            <div class="card-body">
                <div class="card-title">
                    <h5>Analisa Harga Beli & Jual Terakhir Produk</h5>
                </div>

                <!-- Form Pencarian -->
                <form method="GET" action="{{ url()->current() }}" class="mb-3">
                    <input type="hidden" name="dbId" value="{{ request('dbId') }}">
                    <div class="input-group">
                        <input type="text" class="form-control" name="keyword" placeholder="Cari Produk"
                            value="{{ request('keyword') }}">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </div>
                </form>

                <div class="table-responsive mt-3">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nama Produk</th>
                                <th>Kode</th>
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
                                        <td>Rp {{ $harga['harga_beli_terakhir'] }}</td>
                                        <td>Rp {{ $harga['harga_jual_terakhir'] }}</td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Tidak ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @php
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($totalPages, $startPage + 4);
                        $startPage = max(1, $endPage - 4); // Pastikan tetap 5 halaman

                        $baseUrl = url()->current() . '?dbId=' . $dbId . '&keyword=' . request('keyword');
                    @endphp

                    <div class="d-flex justify-content-center mt-4">
                        <nav>
                            <ul class="pagination">
                                {{-- Tombol ke Halaman Pertama --}}
                                <li class="page-item {{ $currentPage == 1 ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ $baseUrl }}&page=1">« First</a>
                                </li>

                                {{-- Tombol Previous --}}
                                <li class="page-item {{ $currentPage == 1 ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ $baseUrl }}&page={{ $currentPage - 1 }}">‹ Prev</a>
                                </li>

                                {{-- Nomor Halaman --}}
                                @for ($i = $startPage; $i <= $endPage; $i++)
                                    <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                        <a class="page-link"
                                            href="{{ $baseUrl }}&page={{ $i }}">{{ $i }}</a>
                                    </li>
                                @endfor

                                {{-- Tombol Next --}}
                                <li class="page-item {{ $currentPage == $totalPages ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ $baseUrl }}&page={{ $currentPage + 1 }}">Next ›</a>
                                </li>

                                {{-- Tombol ke Halaman Terakhir --}}
                                <li class="page-item {{ $currentPage == $totalPages ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ $baseUrl }}&page={{ $totalPages }}">Last »</a>
                                </li>
                            </ul>
                        </nav>
                    </div>



                </div>
            </div>
        </div>
    </div>

@endsection
