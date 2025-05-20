@extends('accurate.layouts.main')

@section('content')
    <div class="container mt-1">
        <div class="card">
            <div class="card-body">
                <div class="card-title">
                    <h5>Analisa Harga Beli & Jual Terakhir Produk</h5>
                </div>

                <!-- Form Pencarian -->
                {{-- <form method="GET" action="{{ url()->current() }}" class="mb-3">
                    <input type="hidden" name="dbId" value="{{ request('dbId') }}">
                    <div class="input-group">
                        <input type="text" class="form-control" name="keyword" placeholder="Cari Produk"
                            value="{{ request('keyword') }}">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </div>
                </form> --}}

                @php
                    use Carbon\Carbon;

                    $defaultStart = Carbon::now()->startOfMonth()->format('Y-m-d');
                    $defaultEnd = Carbon::now()->format('Y-m-d');
                @endphp

                <!-- Form Pencarian -->
                <div class="mb-3">
                    <input type="text" id="search-input" class="form-control" placeholder="Cari di tabel...">
                </div>

                <form method="GET" action="{{ url()->current() }}" class="mb-3">
                    <input type="hidden" name="dbId" value="{{ request('dbId') }}">
                    <div class="row g-2">

                        <div class="col-md-3">
                            <input type="date" class="form-control" name="start_date"
                                value="{{ request('start_date', $defaultStart) }}">
                        </div>
                        <div class="col-md-3">
                            <input type="date" class="form-control" name="end_date"
                                value="{{ request('end_date', $defaultEnd) }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Cari
                            </button>
                        </div>
                    </div>
                </form>



                <div class="table-responsive mt-3"
                    style="max-height: calc(100vh - 200px); overflow-y: auto; overflow-x: auto;">
                    <table class="table table-bordered table-striped" id="data-table">
                        <thead class="table-light">
                            <tr>
                                {{-- Sticky header atas --}}
                                <th style="position: sticky; top: 0; background: #f8f9fa; z-index: 10;">Aksi</th>
                                <th style="position: sticky; top: 0; background: #f8f9fa; z-index: 10;">Tanggal</th>

                                {{-- Sticky kiri --}}
                                <th style="position: sticky; top: 0; left: 0; background: #f8f9fa; z-index: 11;">Kode#</th>
                                <th style="position: sticky; top: 0; left: 80px; background: #f8f9fa; z-index: 11;">
                                    Nama&nbsp;Produk</th>

                                {{-- Header lainnya --}}
                                <th style="position: sticky; top: 0; background: #f8f9fa; z-index: 10;">Qty</th>
                                <th style="position: sticky; top: 0; background: #f8f9fa; z-index: 10;">St</th>
                                <th style="position: sticky; top: 0; background: #f8f9fa; z-index: 10;">HB&nbsp;Baru</th>
                                <th style="position: sticky; top: 0; background: #f8f9fa; z-index: 10;">
                                    Diskon&nbsp;FP&nbsp;Baru</th>
                                <th style="position: sticky; top: 0; background: #f8f9fa; z-index: 10;">HB&nbsp;Lama</th>

                                @for ($i = 1; $i <= 5; $i++)
                                    <th style="position: sticky; top: 0; background: #f8f9fa; z-index: 10;">HJ&nbsp;Baru
                                        {{ $i }}</th>
                                    <th style="position: sticky; top: 0; background: #f8f9fa; z-index: 10;">
                                        St&nbsp;{{ $i }}</th>
                                    <th style="position: sticky; top: 0; background: #f8f9fa; z-index: 10;">
                                        Rt&nbsp;{{ $i }}</th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($hasil as $produk)
                                @if ($produk['hb_baru'] > 0)
                                    <tr>
                                        <td style="white-space: nowrap;">
                                            <!-- Button trigger modal -->
                                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                                data-bs-target="#produkModal{{ $produk['no'] }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                        </td>
                                        <td style="white-space: nowrap;">{{ $produk['tgl_transaksi'] }}</td>

                                        {{-- Sticky kiri --}}
                                        <td style="position: sticky; left: 0; background: white; z-index: 10;">
                                            {{ $produk['no'] }}
                                        </td>
                                        <td style="position: sticky; left: 80px; background: white; z-index: 10;">
                                            {{ $produk['nama_barang'] }}
                                        </td>

                                        <td>{{ $produk['qty'] ?? '-' }}</td>
                                        <td>{{ $produk['st'] ?? '-' }}</td>
                                        <td>Rp&nbsp;{{ number_format($produk['hb_baru'] ?? 0, 0, ',', '.') }}</td>
                                        <td>{{ $produk['disc_fp_baru'] ?? '-' }}</td>
                                        <td>Rp&nbsp;{{ number_format($produk['hb_lama'] ?? 0, 0, ',', '.') }}</td>

                                        @for ($i = 1; $i <= 5; $i++)
                                            <td>Rp&nbsp;{{ number_format($produk['hj_baru' . $i] ?? 0, 0, ',', '.') }}</td>
                                            <td>{{ $produk['st' . $i] ?? '-' }}</td>
                                            <td>{{ $produk['rs' . $i] ?? '-' }}</td>
                                        @endfor
                                    </tr>
                                    <!-- Modal -->
                                    <div class="modal fade" id="produkModal{{ $produk['no'] }}" tabindex="-1"
                                        aria-labelledby="produkLabel{{ $produk['no'] }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <p class="modal-title " id="produkLabel{{ $produk['no'] }}">
                                                        {{ $produk['no'] }}&nbsp;{{ $produk['nama_barang'] }}
                                                    </p>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="{{ route('update.harga') }}" method="POST">
                                                        @csrf
                                                        @if (isset($produk))
                                                            <input type="text" name="dbId"
                                                                value="{{ request('dbId') }}" hidden>
                                                            <input type="text" name="nama_produk"
                                                                value="{{ $produk['nama_barang'] }}" hidden>
                                                            <input type="text" name="itemType"
                                                                value="{{ $produk['itemType'] }}" hidden>
                                                            <input type="text" name="id"
                                                                value="{{ $produk['id'] }}" hidden>
                                                            <input type="text" name="no"
                                                                value="{{ $produk['no'] }}" hidden>

                                                            @for ($i = 1; $i <= 5; $i++)
                                                                <div class="input-group mt-3">
                                                                    <span class="input-group-text">Rp</span>
                                                                    <input type="text"
                                                                        name="{{ $i == 1 ? 'unitPrice' : 'unit' . $i . 'Price' }}"
                                                                        class="form-control"
                                                                        value="{{ $produk['hj_baru' . $i] ?? null }}"
                                                                        aria-label="Harga Jual Baru">

                                                                    <span class="input-group-text" style="width: 80px;">
                                                                        <input type="text"
                                                                            name="rs[{{ $i }}]"
                                                                            class="form-control border-0 bg-transparent p-0"
                                                                            value="{{ $produk['rs' . $i] ?? '-' }}"
                                                                            readonly>
                                                                    </span>
                                                                    <span class="input-group-text" style="width: 80px;">
                                                                        <input type="text"
                                                                            name="st[{{ $i }}]"
                                                                            class="form-control border-0 bg-transparent p-0"
                                                                            value="{{ $produk['st' . $i] ?? '-' }}"
                                                                            readonly>
                                                                    </span>
                                                                </div>
                                                            @endfor
                                                        @else
                                                            <div class="alert alert-danger">Data produk tidak tersedia.
                                                            </div>
                                                        @endif

                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                </div>
                                                </form>

                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="26" class="text-center">Tidak ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- Pagination --}}
                    @php
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($totalPages, $startPage + 4);
                        $startPage = max(1, $endPage - 4);
                        $baseUrl = url()->current() . '?dbId=' . $dbId . '&keyword=' . request('keyword');
                    @endphp

                    <div class="d-flex justify-content-center mt-4">
                        <nav>
                            <ul class="pagination">
                                <li class="page-item {{ $currentPage == 1 ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ $baseUrl }}&page=1">« First</a>
                                </li>
                                <li class="page-item {{ $currentPage == 1 ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ $baseUrl }}&page={{ $currentPage - 1 }}">‹
                                        Prev</a>
                                </li>
                                @for ($i = $startPage; $i <= $endPage; $i++)
                                    <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                        <a class="page-link"
                                            href="{{ $baseUrl }}&page={{ $i }}">{{ $i }}</a>
                                    </li>
                                @endfor
                                <li class="page-item {{ $currentPage == $totalPages ? 'disabled' : '' }}">
                                    <a class="page-link" href="{{ $baseUrl }}&page={{ $currentPage + 1 }}">Next
                                        ›</a>
                                </li>
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

    <script>
        document.getElementById('search-input').addEventListener('input', function() {
            const searchValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('#data-table tbody tr');

            rows.forEach(row => {
                const cells = Array.from(row.querySelectorAll('td'));
                const rowText = cells.map(cell => cell.textContent.toLowerCase()).join(' ');

                if (rowText.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
@endsection
