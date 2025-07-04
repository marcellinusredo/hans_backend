<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice Transaksi</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
    </style>
</head>
<body>
    <div class="header">
        <h2>INVOICE TRANSAKSI</h2>
        <p><strong>No. Invoice:</strong> {{ $transaksi->nomor_invoice_transaksi }}</p>
        <p><strong>Tanggal:</strong> {{ $transaksi->waktu_transaksi }}</p>
    </div>

    <p><strong>Nama Pelanggan:</strong> {{ $transaksi->pelanggan->nama_pelanggan }}</p>
    <p><strong>Nama Staff:</strong> {{ $transaksi->staff->nama_staff }}</p>

    <table>
        <thead>
            <tr>
                <th>Produk / Jasa</th>
                <th>Harga</th>
                <th>Jumlah</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transaksi->detail_transaksi as $item)
                <tr>
                    <td>{{ $item->produk->nama_produk }}</td>
                    <td>Rp {{ number_format($item->harga_produk_detail_transaksi, 0, ',', '.') }}</td>
                    <td>{{ $item->jumlah_produk_detail_transaksi }}</td>
                    <td>Rp {{ number_format($item->subtotal_produk_detail_transaksi, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            @foreach ($transaksi->detail_transaksi_jasa as $item)
                <tr>
                    <td>{{ $item->jasa->nama_jasa }}</td>
                    <td>Rp {{ number_format($item->harga_jasa_detail_transaksi_jasa, 0, ',', '.') }}</td>
                    <td>1</td>
                    <td>Rp {{ number_format($item->harga_jasa_detail_transaksi_jasa, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <br><br>
    <p><strong>Total:</strong> Rp {{ number_format($transaksi->total_harga_transaksi, 0, ',', '.') }}</p>
    <p><strong>Bayar:</strong> Rp {{ number_format($transaksi->pembayaran_transaksi, 0, ',', '.') }}</p>
    <p><strong>Kembalian:</strong> Rp {{ number_format($transaksi->kembalian_transaksi, 0, ',', '.') }}</p>

    <br>
    <p>Terima kasih telah bertransaksi di bengkel kami.</p>
</body>
</html>
