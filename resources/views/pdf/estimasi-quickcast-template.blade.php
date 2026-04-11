<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Estimasi Quick Cast</title>
    <style> body { font-family: DejaVu Sans, sans-serif; font-size: 11px; } table{ width:100%; border-collapse:collapse;} td{padding:4px;} </style>
</head>
<body>
    <h2>Laporan Estimasi Quick Cast</h2>
    <p>Dicetak: {{ $printed_at }}</p>
    <table>
        <tr><td style="width:180px;">No Dokumen</td><td>: {{ $document_no }}</td></tr>
        <tr><td>Nama Proyek</td><td>: {{ $project['nama_proyek'] }}</td></tr>
        <tr><td>Lokasi</td><td>: {{ $project['lokasi_proyek'] }}</td></tr>
        <tr><td>Mutu Beton</td><td>: {{ $project['mutu_beton'] }}</td></tr>
        <tr><td>Total Akhir</td><td>: {{ $summary['total_akhir_m3'] }} m³</td></tr>
        <tr><td>Estimasi Harga</td><td>: Rp {{ $summary['estimasi_harga_total'] }}</td></tr>
    </table>
</body>
</html>