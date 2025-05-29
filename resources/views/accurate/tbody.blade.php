@foreach ($hasil as $produk)
    @if ($produk['hb_baru'] > 0)
        <tr>
            <td>{{ $produk['nama_barang'] }}</td>
            <td>{{ $produk['qty'] }}</td>
            <td>{{ $produk['st'] }}</td>
            <td>{{ number_format($produk['hb_baru'], 0, ',', '.') }}</td>
            <td>{{ number_format($produk['disc_fp_baru'], 0, ',', '.') }}</td>
            <td>{{ number_format($produk['hb_lama'], 0, ',', '.') }}</td>
            <td>{{ $produk['tgl_transaksi'] }}</td>
            {{-- Tambah kolom lain sesuai kebutuhan --}}
        </tr>
    @endif
@endforeach
