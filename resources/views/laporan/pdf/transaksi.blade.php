<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
        h2 { text-align: center; }
    </style>
</head>
<body>
    <h2>Laporan Transaksi</h2>
    <p>Periode: {{ $start }} s.d. {{ $end }}</p>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Staff</th>
                <th>Nama Pelanggan</th>
                <th>Waktu Transaksi</th>
                <th>Total Harga</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->nama_staff }}</td>
                <td>{{ $item->nama_pelanggan }}</td>
                <td>{{ \Carbon\Carbon::parse($item->waktu_transaksi)->format('d/m/Y H:i') }}</td>
                <td>Rp {{ number_format($item->total_harga_transaksi, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
