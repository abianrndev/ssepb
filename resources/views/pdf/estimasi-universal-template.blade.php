<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Estimasi Kebutuhan Beton</title>
    <style>
        @page {
            margin: 12mm 12mm 12mm 12mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1f2937;
            line-height: 1.3;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .muted { color: #6b7280; }
        .small { font-size: 9px; }

        .header {
            width: 100%;
            border-bottom: 1.5px solid #374151;
            padding-bottom: 7px;
            margin-bottom: 8px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .logo-box {
            width: 100px;
            height: 44px;
            border: 1px dashed #9ca3af;
            display: inline-block;
            text-align: center;
            line-height: 44px;
            color: #9ca3af;
            font-size: 9px;
        }

        .title {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .subtitle {
            font-size: 9px;
            color: #6b7280;
        }

        .section {
            margin-top: 7px;
        }

        .section-title {
            font-size: 10px;
            font-weight: bold;
            background: #e5e7eb;
            padding: 4px 7px;
            border: 1px solid #d1d5db;
            margin-bottom: 0;
        }

        .box {
            border: 1px solid #d1d5db;
            border-top: none;
            padding: 5px 7px;
        }

        .info-table, .data-table, .summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 2px 4px;
            vertical-align: top;
            font-size: 10px;
        }

        .label {
            color: #4b5563;
            white-space: nowrap;
        }

        .data-table th, .data-table td {
            border: 1px solid #d1d5db;
            padding: 3px 5px;
            font-size: 9px;
        }

        .data-table th {
            background: #f3f4f6;
            font-weight: bold;
            text-align: center;
        }

        .summary-table td {
            padding: 2px 5px;
            font-size: 10px;
        }

        .notes {
            margin-top: 7px;
            border: 1px solid #fbbf24;
            padding: 5px 7px;
            font-size: 9px;
            color: #374151;
            background: #fffbeb;
        }

        .sign-space {
            height: 40px;
        }

        .footer {
            position: fixed;
            bottom: -4mm;
            left: 0;
            right: 0;
            font-size: 9px;
            color: #9ca3af;
            text-align: center;
        }

        .footer hr {
            border: none;
            border-top: 1px solid #e5e7eb;
            margin-bottom: 3px;
        }
    </style>
</head>
<body>

    {{-- HEADER --}}
    <div class="header">
        <table class="header-table">
            <tr>
                <td style="width: 110px; vertical-align: middle;">
                    <div class="logo-box">TEMPAT LOGO</div>
                </td>

                <td style="vertical-align: middle; padding-left: 6px;">
                    <div class="title">LAPORAN ESTIMASI KEBUTUHAN BETON</div>
                    <div class="subtitle">Sistem Estimasi Proyek Beton</div>
                    <div class="small muted" style="margin-top: 4px;">
                        Dicetak: {{ $printed_at ?? '-' }} &nbsp;|&nbsp; Jenis: {{ $jenis_kalkulasi ?? '-' }}
                    </div>
                </td>

                <td style="vertical-align: middle; white-space: nowrap; padding-left: 10px;">
                    <table style="border-collapse: collapse; font-size: 10px;">
                        <tr>
                            <td style="color: #4b5563; padding: 2px 3px 2px 0;">No. Dokumen</td>
                            <td style="padding: 2px 3px;">: {{ $document_no ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="color: #4b5563; padding: 2px 3px 2px 0;">Disusun Oleh</td>
                            <td style="padding: 2px 3px;">: {{ $prepared_by ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="color: #4b5563; padding: 2px 3px 2px 0;">Jabatan</td>
                            <td style="padding: 2px 3px;">: Engineer</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    {{-- A. INFO PROYEK --}}
    <div class="section">
        <div class="section-title">A. Informasi Proyek</div>
        <div class="box">
            <table class="info-table">
                <tr>
                    <td class="label" style="width: 90px;">Nama Proyek</td>
                    <td style="width: 120px;">: {{ $project['nama_proyek'] ?? '-' }}</td>
                    <td class="label" style="width: 90px;">Lokasi Proyek</td>
                    <td>: {{ $project['lokasi_proyek'] ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Mutu Beton</td>
                    <td>: {{ $project['mutu_beton'] ?? '-' }}</td>
                    <td class="label">Waste Factor</td>
                    <td>: {{ $project['waste_percent'] ?? '-' }}%</td>
                </tr>
                <tr>
                    <td class="label">Metode / Mode</td>
                    <td>: {{ $project['metode_input'] ?? '-' }}</td>
                    <td class="label">Tanggal Estimasi</td>
                    <td>: {{ $project['tanggal_estimasi'] ?? '-' }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- B. PARAMETER UTAMA --}}
    <div class="section">
        <div class="section-title">B. Parameter Input Utama</div>
        <div class="box">
            <table class="info-table">
                @forelse(($parameters ?? []) as $row)
                    <tr>
                        <td class="label" style="width: 120px;">{{ $row['label'] ?? '-' }}</td>
                        <td>: {{ $row['value'] ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="muted">Tidak ada parameter tambahan.</td>
                    </tr>
                @endforelse
            </table>
        </div>
    </div>

    {{-- C. DETAIL PERHITUNGAN --}}
    <div class="section">
        <div class="section-title">C. Detail Perhitungan</div>
        <div class="box" style="padding: 0; border-top: none;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 28px;">No</th>
                        <th style="text-align: left;">Item / Segmen</th>
                        <th style="width: 48px;">Jumlah</th>
                        <th style="width: 155px; text-align: left;">Dimensi (P × L × T)</th>
                        <th style="width: 75px;">Volume (m³)</th>
                        <th style="text-align: left;">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($detail_items ?? []) as $i => $item)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>{{ $item['nama'] ?? '-' }}</td>
                            <td class="text-center">{{ $item['jumlah'] ?? '-' }}</td>
                            <td>{{ $item['dimensi'] ?? '-' }}</td>
                            <td class="text-right">{{ $item['volume'] ?? '-' }}</td>
                            <td>{{ $item['keterangan'] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center muted" style="padding: 6px;">Tidak ada data detail.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- D. DETAIL PENGURANG --}}
    <div class="section">
        <div class="section-title">D. Detail Pengurang (Opsional)</div>
        <div class="box" style="padding: 0; border-top: none;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 28px;">No</th>
                        <th style="text-align: left;">Jenis Pengurang</th>
                        <th style="width: 48px;">Jumlah</th>
                        <th style="width: 155px; text-align: left;">Dimensi (P × L × T)</th>
                        <th style="width: 75px;">Volume (m³)</th>
                        <th style="text-align: left;">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($deductions ?? []) as $i => $d)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>{{ $d['nama'] ?? '-' }}</td>
                            <td class="text-center">{{ $d['jumlah'] ?? '-' }}</td>
                            <td>{{ $d['dimensi'] ?? '-' }}</td>
                            <td class="text-right">{{ $d['volume'] ?? '-' }}</td>
                            <td>{{ $d['keterangan'] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center muted" style="padding: 6px;">Tidak ada data pengurang.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- E. RINGKASAN --}}
    <div class="section">
        <div class="section-title">E. Ringkasan Hasil Estimasi</div>
        <div class="box" style="padding: 0; border-top: none;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 55%; padding: 6px 8px; vertical-align: top;">
                        <table class="summary-table">
                            <tr><td style="width: 140px; color: #4b5563;">Volume Kotor</td><td>: {{ $summary['volume_kotor'] ?? '-' }} m³</td></tr>
                            <tr><td style="color: #4b5563;">Volume Pengurang</td><td>: {{ $summary['volume_pengurang'] ?? '-' }} m³</td></tr>
                            <tr><td style="color: #4b5563;">Volume Bersih</td><td>: {{ $summary['volume_bersih'] ?? '-' }} m³</td></tr>
                            <tr><td style="color: #4b5563;">Waste Volume</td><td>: {{ $summary['waste_volume'] ?? '-' }} m³</td></tr>
                            <tr><td style="color: #4b5563;">Total Akhir</td><td>: {{ $summary['total_akhir_m3'] ?? '-' }} m³</td></tr>
                            <tr><td style="color: #4b5563;">Harga Beton per m³</td><td>: Rp {{ $summary['harga_per_m3'] ?? '-' }}</td></tr>
                        </table>
                    </td>
                    <td style="width: 45%; padding: 6px 8px; vertical-align: middle;">
                        <div style="background: #eef2ff; border: 1.5px solid #818cf8; border-radius: 4px; padding: 10px 12px; text-align: center;">
                            <div style="font-size: 9px; color: #6b7280; margin-bottom: 3px;">Estimasi Harga Total</div>
                            <div style="font-size: 14px; font-weight: bold; color: #3730a3;">
                                Rp {{ $summary['estimasi_harga_total'] ?? '-' }}
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <table style="width: 100%; border-collapse: collapse; margin-top: 8px;">
        <tr>
            <td style="width: 55%; vertical-align: top; padding-right: 8px;">
                <div class="notes">
                    <b>Catatan:</b><br>
                    1) Nilai ini adalah estimasi awal berdasarkan input pengguna.<br>
                    2) Verifikasi teknis lapangan tetap diperlukan sebelum pelaksanaan.<br>
                    3) Harga satuan mengikuti harga aktif pada sistem saat estimasi dibuat.
                </div>
            </td>
            <td style="width: 45%; vertical-align: top;">
                <table style="width: 100%; border-collapse: collapse; text-align: center; font-size: 10px;">
                    <tr>
                        <td style="width: 50%; padding: 0 4px;">
                            Disusun oleh,
                            <div class="sign-space"></div>
                            <b>{{ $prepared_by ?? '(Nama Pelaksana)' }}</b>
                        </td>
                        <td style="width: 50%; padding: 0 4px;">
                            Diperiksa oleh,
                            <div class="sign-space"></div>
                            <b>(..............................)</b>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="footer">
        <hr>
        Dokumen ini digenerate otomatis oleh sistem &bull; {{ $app_name ?? 'Aplikasi Estimasi Beton' }}
    </div>

</body>
</html>