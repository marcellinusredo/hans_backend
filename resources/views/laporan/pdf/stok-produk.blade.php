<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Laporan Stok Produk</title>
  <style>
    body { font-family: sans-serif; font-size: 12px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #000; padding: 6px; text-align: left; }
    th { background-color: #f2f2f2; }
    h2 { text-align: center; }
  </style>
</head>
<body>
  <h2>Laporan Stok Produk</h2>
  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>Nama Produk</th>
        <th>Stok</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($data as $i => $item)
      <tr>
        <td>{{ $i + 1 }}</td>
        <td>{{ $item->nama_produk }}</td>
        <td>{{ $item->stok_produk }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>