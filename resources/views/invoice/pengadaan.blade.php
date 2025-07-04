<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice Pengadaan</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
    </style>
</head>
<body>
    <div class="header">
        <h2>INVOICE PENGADAAN STOK</h2>
        <p><strong>No. Invoice:</strong> {{ $pengadaan->nomor_invoice_pengadaan_stok }}</p>
        <p><strong>Tanggal:</strong> {{ $pengadaan->waktu_pengadaan_stok }}</p>
    </div>

    <p><strong>Supplier:</strong> {{ $pengadaan->supplier->nama_supplier }}</p>
    <p><strong>Staff:</strong> {{ $pengadaan->staff->nama_staff }}</p>

    <table>
        <thead>
            <tr>
                <th>Produk</th>
                <th>Jumlah</th>
                <th>Harga</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pengadaan->detail_pengadaan_stok as $item)
                <tr>
                    <td>{{ $item->produk->nama_produk }}</td>
                    <td>{{ $item->jumlah_produk_detail_pengadaan_stok }}</td>
                    <td>Rp {{ number_format($item->harga_produk_detail_pengadaan_stok, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->subtotal_produk_detail_pengadaan_stok, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <br>
    <p><strong>Total Pengadaan:</strong> Rp {{ number_format($pengadaan->total_harga_pengadaan_stok, 0, ',', '.') }}</p>

    <br>
    <p>Terima kasih telah melakukan pengadaan barang.</p>
</body>
</html>
