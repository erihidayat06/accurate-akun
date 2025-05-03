<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Produk Terpilih</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            /* Agar kartu bisa ditata dalam beberapa baris */
            justify-content: space-between;
            /* Menjaga jarak antar kolom */
            width: 80%;
            /* Mengatur lebar menjadi 80% */
            margin: 0 auto;
            /* Memusatkan row */
        }

        .card {
            flex: 1 1 48%;
            /* Menentukan bahwa tiap kartu memiliki lebar 48% dan fleksibel */
            border: 1px solid #585858;
            padding: 10px;
            vertical-align: top;
            box-sizing: border-box;
            margin-bottom: 15px;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .card-header {
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #585858;
            margin-bottom: 6px;
            padding-bottom: 4px;
        }

        .card-body {
            margin-bottom: 6px;
            min-height: 60px;
            text-align: right;
        }

        .card-footer {
            border-top: 1px solid #585858;
            padding-top: 4px;
        }

        h3 {
            margin-bottom: 15px;
        }

        hr {
            border: 0;
            border-top: 1px solid #585858;
            margin: 5px 0;
        }
    </style>
</head>

<body>


    @foreach ($produk->chunk(2) as $row)
        <div class="row">
            @foreach ($row as $item)
                <div class="card">
                    <table style="width: 100%">
                        <tr>
                            <td>
                                Nama Item
                            </td>
                            <td style="text-align: right">
                                {{ strtoupper($item['nama']) }}
                            </td>
                        </tr>
                    </table>


                    <table style="width: 100%">
                        <tr>
                            <td>
                                Kode Item
                            </td>
                            <td style="text-align: right">
                                {{ strtoupper($item['kode']) }}
                            </td>
                        </tr>
                    </table>


                    <table style="width: 100%">
                        <tr>
                            <td>
                                Kategori Penjualan
                            </td>
                            <td style="text-align: right">
                                {{ strtoupper($item['kategori']) }}
                            </td>
                        </tr>
                    </table>

                    <hr>

                    <div class="card-body">
                        <table style="width: 100%">
                            <tr>
                                <td style="vertical-align: top;">
                                    Harga
                                </td>
                                <td style="text-align: right">
                                    @php $index = 1; @endphp
                                    @foreach ($item['harga_satuan'] as $satuan => $harga)
                                        @if ($harga > 0)
                                            Rp {{ number_format($harga, 0, ',', '.') }} /
                                            {{ $satuan !== '' ? $satuan : 'Unit ' . $index }}<br>
                                        @endif
                                        @php $index++; @endphp
                                    @endforeach
                                </td>
                            </tr>
                        </table>

                    </div>
                    <hr>
                    <table style="width: 100%">
                        <tr>
                            <td>
                                Berlaku di cabang
                            </td>
                            <td style="text-align: right">
                                {{ ucfirst($item['cabang']) }}
                            </td>
                        </tr>
                    </table>

                </div>
            @endforeach
        </div>
    @endforeach

</body>

</html>
