@extends('accurate.layouts.main')

@section('content')
    <div class="container mt-5">
        <div class="card">
            <div class="card-body">
                <div class="card-title">
                    <h5>Daftar Produk</h5>
                </div>
                <form action="{{ route('produk.print-pdf') }}" method="POST" target="_blank">
                    @csrf
                    <button type="submit" class="btn btn-primary mb-3">Cetak PDF</button>

                    <table class="table">
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
