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


                <button class="btn btn-success" id="exportExcel">
                    <i class="bi bi-file-earmark-spreadsheet"></i> Export
                </button>

                <!-- Tombol trigger modal -->
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#importExcelModal">
                    <i class="bi bi-file-earmark-spreadsheet"></i> Import
                </button>

                <!-- Modal -->
                <div class="modal fade" id="importExcelModal" tabindex="-1" aria-labelledby="importExcelLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <form id="formImportExcel" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="importExcelLabel">Penyesuaian Harga</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Tutup"></button>
                                </div>
                                <div id="formAlertContainer"></div>

                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="transDate">Tanggal Transaksi</label>
                                        <input type="date" name="transDate" class="form-control" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="description">Deskripsi</label>
                                        <input type="text" name="description" class="form-control">
                                    </div>

                                    <div class="mb-3">
                                        <label>Kategori Penjualan</label><br>
                                        @foreach ($kategori_penjualan as $kategori)
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox"
                                                    name="salesAdjustmentCategoryList[]" value="{{ $kategori['id'] }}"
                                                    id="kategori-{{ $kategori['id'] }}">
                                                <label class="form-check-label" for="kategori-{{ $kategori['id'] }}">
                                                    {{ $kategori['name'] }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="mb-3">
                                        <label for="file">Pilih File Excel</label>
                                        <input type="file" name="file" class="form-control" accept=".xlsx, .xls "
                                            required>
                                    </div>

                                    <input type="hidden" name="dbId" value="{{ request('dbId') }}">
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary" id="submitBtn">
                                        Upload & Kirim
                                    </button>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>

                <!-- AJAX Script -->
                <script>
                    document.getElementById('formImportExcel').addEventListener('submit', async function(e) {
                        e.preventDefault();

                        const form = e.target;
                        const formData = new FormData(form);
                        const submitBtn = document.getElementById('submitBtn');
                        const alertContainer = document.getElementById('formAlertContainer');

                        // Ubah tombol jadi spinner
                        submitBtn.disabled = true;
                        const originalBtnHtml = submitBtn.innerHTML;
                        submitBtn.innerHTML =
                            `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengirim...`;

                        // Clear alert sebelumnya
                        alertContainer.innerHTML = '';

                        try {
                            const res = await fetch("{{ route('penyesuaian.import') }}", {
                                method: "POST",
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                },
                                body: formData
                            });

                            const text = await res.text();
                            try {
                                const data = JSON.parse(text);

                                if (data.success) {
                                    // Alert sukses
                                    alertContainer.innerHTML = `
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            ${data.message}
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>`;

                                    // Reset form
                                    form.reset();

                                    // Tutup modal setelah delay sebentar
                                    const modalEl = document.getElementById('importExcelModal');
                                    const modal = bootstrap.Modal.getInstance(modalEl);
                                    setTimeout(() => {
                                        modal.hide();
                                    }, 1000);
                                } else {
                                    // Alert gagal
                                    alertContainer.innerHTML = `
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            Gagal: ${data.message}
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>`;
                                    if (data.debug) console.log("Detail error:", data.debug);
                                }
                            } catch (err) {
                                console.error("Response bukan JSON valid:", text);
                                alertContainer.innerHTML = `
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        Response bukan JSON valid, lihat console.
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>`;
                            }
                        } catch (err) {
                            console.error("Error:", err);
                            alertContainer.innerHTML = `
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    Terjadi kesalahan saat mengunggah. Coba lagi nanti.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>`;
                        } finally {
                            // Kembalikan tombol ke semula
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalBtnHtml;
                        }
                    });
                </script>








                <div class="table-responsive mt-3"
                    style="max-height: calc(100vh - 200px); overflow-y: auto; overflow-x: auto;">
                    <div id="barang-check"></div>
                    <table class="table table-bordered table-striped" id="data-table">
                        <thead class="table-light">
                            <tr>
                                {{-- Sticky header atas --}}
                                <th style="position: sticky; top: 0; background: #f8f9fa; z-index: 10;">#</th>
                                {{-- Checkbox untuk centang semua --}}
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
                                    @php
                                        $satuan = [];
                                        for ($i = 1; $i <= 5; $i++) {
                                            $hjKey = 'hj_baru' . $i;
                                            $stKey = 'st' . $i;
                                            $rsKey = 'rs' . $i;

                                            if (!empty($produk[$hjKey]) && $produk[$hjKey] > 0) {
                                                $satuan[] = [
                                                    'harga' => $produk[$hjKey],
                                                    'satuan' => $produk[$stKey] ?? '-',
                                                    'reseller' => $produk[$rsKey] ?? '-',
                                                ];
                                            }
                                        }

                                        $item = [
                                            'no' => $produk['no'],
                                            'nama_barang' => $produk['nama_barang'],
                                            'satuan' => $satuan,
                                        ];
                                    @endphp

                                    <tr>
                                        <td>
                                            <input type="checkbox" class="checkItem"
                                                value='@json($item)'>
                                        </td>


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
                                                <form id="form-penyesuaian-{{ $produk['no'] }}"
                                                    action="{{ route('sales.adjustment') }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <p class="modal-title" id="produkLabel{{ $produk['no'] }}">
                                                            {{ $produk['no'] }} - {{ $produk['nama_barang'] }}
                                                        </p>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        @if (session('success'))
                                                            <div class="alert alert-success">{{ session('success') }}
                                                            </div>
                                                        @endif
                                                        @if (session('error'))
                                                            <div class="alert alert-danger">{{ session('error') }}</div>
                                                        @endif
                                                        <div id="response-message" class="mt-2"></div>

                                                        <input type="hidden" name="dbId"
                                                            value="{{ request('dbId') }}">
                                                        <input type="hidden" name="itemNo"
                                                            value="{{ $produk['no'] }}">
                                                        <input type="hidden" name="itemName"
                                                            value="{{ $produk['nama_barang'] }}">

                                                        <div class="mb-3">
                                                            <label for="hj_selector_{{ $produk['no'] }}">Pilih Kategori
                                                                Harga</label>
                                                            <select class="form-select mb-2"
                                                                id="hj_selector_{{ $produk['no'] }}">
                                                                @for ($i = 1; $i <= 5; $i++)
                                                                    @php
                                                                        $hj = $produk['hj_baru' . $i] ?? 0;
                                                                        $rs = $produk['rs' . $i] ?? '-';
                                                                        $st = $produk['st' . $i] ?? '-';
                                                                    @endphp
                                                                    @if ($hj > 0)
                                                                        <option
                                                                            value="{{ (int) $hj }}|{{ $st }}">
                                                                            [{{ $rs }} - {{ $st }}] Rp
                                                                            {{ number_format($hj, 0, ',', '.') }}
                                                                        </option>
                                                                    @endif
                                                                @endfor
                                                            </select>


                                                            <input type="hidden" name="st"
                                                                id="st-{{ $produk['no'] }}">

                                                            <label for="price-{{ $produk['no'] }}">Harga
                                                                Penyesuaian</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text">Rp</span>
                                                                <input type="number" class="form-control" name="price"
                                                                    id="price-{{ $produk['no'] }}"
                                                                    value="{{ $produk['hj_baru1'] ?? 0 }}" required>
                                                            </div>

                                                        </div>

                                                        <div class="mb-3">
                                                            <label for="transDate">Tanggal Berlaku</label>
                                                            <input type="date" class="form-control" name="transDate"
                                                                value="{{ date('Y-m-d') }}" required>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label for="description">Catatan Penyesuaian</label>
                                                            <input type="text" class="form-control" name="description"
                                                                value="Penyesuaian untuk {{ $produk['nama_barang'] }}">
                                                        </div>

                                                        <div class="mb-3">
                                                            <label>Kategori Penjualan</label><br>
                                                            @foreach ($kategori_penjualan as $kategori)
                                                                <div class="form-check form-check-inline">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        name="salesAdjustmentCategoryList[]"
                                                                        value="{{ $kategori['id'] }}"
                                                                        id="kategori-{{ $kategori['id'] }}">
                                                                    <label class="form-check-label"
                                                                        for="kategori-{{ $kategori['id'] }}">
                                                                        {{ $kategori['name'] }}
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Tutup</button>
                                                        <button type="submit" class="btn btn-primary"
                                                            id="submit-btn-{{ $produk['no'] }}">
                                                            <span class="spinner-border spinner-border-sm d-none"
                                                                role="status" aria-hidden="true"
                                                                id="spinner-{{ $produk['no'] }}"></span>
                                                            <span class="btn-text">Kirim Penyesuaian</span>
                                                        </button>

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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

    <script>
        document.getElementById('exportExcel').addEventListener('click', function() {
            const headers = ['No', 'Nama Barang', 'Harga', 'Satuan'];
            const rows = [headers];

            document.querySelectorAll('.checkItem:checked').forEach((checkbox) => {
                try {
                    const data = JSON.parse(checkbox.value);

                    if (!data.satuan || data.satuan.length === 0) {
                        rows.push([data.no, data.nama_barang, '', '']);
                    } else {
                        data.satuan.forEach((varian, i) => {
                            rows.push([
                                data.no ?? '',
                                data.nama_barang ?? '',
                                varian.harga ?? '',
                                varian.satuan ?? ''
                            ]);
                        });
                    }
                } catch (error) {
                    console.error('JSON parsing error:', error);
                }
            });

            if (rows.length === 1) {
                alert('Tidak ada data yang dicentang.');
                return;
            }

            // Convert array to worksheet
            const worksheet = XLSX.utils.aoa_to_sheet(rows);
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, "Data Barang");

            // Export file
            XLSX.writeFile(workbook, "data_produk.xlsx");
        });
    </script>










    <script>
        $(document).ready(function() {
            @foreach ($hasil as $produk)
                $(document).on('change', '#hj_selector_{{ $produk['no'] }}', function() {
                    const value = $(this).val(); // contoh: "15000|PCS"
                    const [price, satuan] = value.split('|');

                    $('#price-{{ $produk['no'] }}').val(price);
                    $('#st-{{ $produk['no'] }}').val(satuan);
                });
            @endforeach
        });
    </script>





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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('form[id^="form-penyesuaian-"]').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formId = this.id;
                    const produkNo = formId.replace('form-penyesuaian-', '');
                    const url = this.action;
                    const formData = new FormData(this);

                    // Elements spinner dan tombol
                    const submitBtn = document.getElementById('submit-btn-' + produkNo);
                    const spinner = document.getElementById('spinner-' + produkNo);
                    const btnText = submitBtn.querySelector('.btn-text');

                    // Reset message area
                    const messageDiv = document.getElementById('response-message');
                    messageDiv.innerHTML = '';

                    // Tampilkan spinner dan disable tombol
                    spinner.classList.remove('d-none');
                    btnText.textContent =
                        'Loading...';
                    submitBtn.disabled = true;

                    fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': formData.get('_token'),
                                'Accept': 'application/json'
                            },
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                messageDiv.innerHTML =
                                    `<div class="alert alert-success">${data.message}</div>`;
                                setTimeout(() => {
                                    const modalEl = document.getElementById(
                                        'produkModal' + produkNo);
                                    const modal = bootstrap.Modal.getInstance(
                                        modalEl);
                                    modal.hide();
                                    // Optional: reload page atau update data
                                }, 1500);
                            } else {
                                messageDiv.innerHTML =
                                    `<div class="alert alert-danger">${data.message}</div>`;
                            }
                        })
                        .catch(err => {
                            messageDiv.innerHTML =
                                `<div class="alert alert-danger">Terjadi kesalahan: ${err.message}</div>`;
                        })
                        .finally(() => {
                            // Hide spinner dan enable tombol kembali
                            spinner.classList.add('d-none');
                            btnText.textContent = 'Kirim Penyesuaian';
                            submitBtn.disabled = false;
                        });
                });
            });
        });
    </script>
@endsection
