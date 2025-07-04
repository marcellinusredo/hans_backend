<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Laporan Keuangan</title>
  <style>
    body { font-family: sans-serif; font-size: 12px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #000; padding: 6px; text-align: left; }
    th { background-color: #f2f2f2; }
    h2 { text-align: center; }
  </style>
</head>
<body>
  <h2>Laporan Keuangan</h2>
  <p>Periode: {{ $start }} s.d. {{ $end }}</p>
  <table>
    <thead>
      <tr>
        <th>Pendapatan</th>
        <th>Pengeluaran</th>
        <th>Laba Kotor</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Rp {{ number_format($data[0]['pendapatan'], 0, ',', '.') }}</td>
        <td>Rp {{ number_format($data[0]['pengeluaran'], 0, ',', '.') }}</td>
        <td>Rp {{ number_format($data[0]['laba_kotor'], 0, ',', '.') }}</td>
      </tr>
    </tbody>
  </table>
</body>
</html>
