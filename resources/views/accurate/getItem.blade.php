@extends('accurate.layouts.main')

@section('content')
    <div class="container mt-5">
        <div class="card">
            <div class="card-body">
                <div class="card-title">
                    <h5>Daftar Produk</h5>
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

                <!-- Form Cetak PDF -->
                <form action="{{ route('produk.print-pdf') }}" method="POST" target="_blank">
                    @csrf
                    <button type="submit" class="btn btn-primary mb-3">Cetak PDF</button>

                    <!-- Tabel Produk -->
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th>Kode Produk</th>
                                <th>Nama Produk</th>
                                <th>Kategori</th>
                                <th>Cabang</th>
                                <th>Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="selected_products[]"
                                            value="{{ base64_encode(json_encode($item)) }}">
                                    </td>
                                    <td>{{ $item['kode'] }}</td>
                                    <td>{{ $item['nama'] }}</td>
                                    <td>{{ $item['kategori'] }}</td>
                                    <td>{{ $item['cabang'] }}</td>
                                    <td>
                                        @php $index = 1; @endphp
                                        @foreach ($item['harga_satuan'] as $satuan => $harga)
                                            @if ($harga > 0)
                                                {{ $satuan !== '' ? $satuan : 'Unit ' . $index }}:
                                                Rp {{ number_format($harga, 0, ',', '.') }}<br>
                                            @endif
                                            @php $index++; @endphp
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </form>

                <!-- Pagination -->
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

    <script>
        document.getElementById('select-all').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('input[name="selected_products[]"]');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    </script>
@endsection
