<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raport - {{ $santri->nama }}</title>
    <style>
        @page {
            size: A4;
            margin: 10mm 15mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.3;
            color: #000;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
            position: relative;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
        }
        .header-logo {
            position: absolute;
            left: 10px;
            top: 5px;
            width: 80px;
            height: 80px;
            object-fit: contain;
        }
        .header-text {
            margin-left: 0;
        }
        .school-name {
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 2px;
            margin-top: 10px;
        }
        .school-address {
            font-size: 9pt;
            font-style: italic;
        }
        .report-title {
            text-align: center;
            margin: 15px 0;
            font-weight: bold;
            text-transform: uppercase;
        }
        .report-title h2 {
            font-size: 14pt;
            margin-bottom: 5px;
        }
        .report-title h3 {
            font-size: 12pt;
        }
        .student-info {
            width: 100%;
            margin-bottom: 15px;
            font-size: 11pt;
        }
        .student-info td {
            padding: 2px 5px;
            vertical-align: top;
        }
        .table-content {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 10pt;
        }
        .table-content th, .table-content td {
            border: 1px solid #000;
            padding: 4px 6px;
        }
        .table-content th {
            background-color: #fff;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        
        .footer {
            margin-top: 20px;
            width: 100%;
        }
        .signature-table {
            width: 100%;
            margin-top: 30px;
            text-align: center;
        }
        .signature-space {
            height: 70px;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        @if($sekolah && $sekolah->logo)
            <img src="{{ public_path('storage/' . $sekolah->logo) }}" alt="Logo" class="header-logo">
        @endif
        <div class="header-text">
            {{-- Removed Arabic text due to font support issues in PDF generation --}}
            <div class="school-name">{{ $sekolah->nama_sekolah ?? 'PONDOK PESANTREN AL-MADINAH' }}</div>
            <div class="school-address">{{ $sekolah->alamat ?? 'Alamat Madrasah' }}</div>
        </div>
    </div>

    <!-- Title -->
    <div class="report-title">
        <h2>RAPOR PONDOK</h2>
        <h3>PENILAIAN TENGAH SEMESTER</h3>
    </div>

    <!-- Student Info -->
    <table class="student-info">
        <tr>
            <td width="15%">NAMA</td>
            <td width="2%">:</td>
            <td width="40%" class="bold">{{ strtoupper($santri->nama) }}</td>
            <td width="15%">KELAS</td>
            <td width="2%">:</td>
            <td width="26%">{{ $kelas->level }}</td>
        </tr>
        <tr>
            <td>NOMOR INDUK</td>
            <td>:</td>
            <td>{{ $santri->santri->nisn ?? '-' }}</td>
            <td>SEMESTER</td>
            <td>:</td>
            <td>{{ ucfirst($tahunAjaran->semester) }} ({{ $tahunAjaran->semester == 'ganjil' ? 'I' : 'II' }})</td>
        </tr>
        <tr>
            <td>JENJANG</td>
            <td>:</td>
            <td>{{ strtoupper($kelas->jenjang) }}</td>
            <td>TAHUN PELAJARAN</td>
            <td>:</td>
            <td>{{ $tahunAjaran->nama }}</td>
        </tr>
    </table>

    <!-- Grades Table -->
    <table class="table-content">
        <thead>
            <tr>
                <th rowspan="2" width="5%">NO</th>
                <th rowspan="2">MATA PELAJARAN</th>
                <th rowspan="2" width="8%">KKM</th>
                <th colspan="2">NILAI</th>
            </tr>
            <tr>
                <th width="12%">ANGKA</th>
                <th width="15%">PREDIKAT</th>
            </tr>
        </thead>
        <tbody>
            @php
                // Display ALL subjects as per user request to fix "missing data" issue.
                // If specific filtering is needed later, we can re-add it, but for now we show what we have.
                $mataPelajaran = $nilaiMapel; 
                
                $totalNilai = 0;
                $count = 0;
            @endphp

            @forelse($mataPelajaran as $index => $nilai)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $nilai['mapel']->nama_mapel }}</td>
                <td class="text-center">{{ $nilai['kkm'] ?? 75 }}</td>
                <td class="text-center bold">{{ $nilai['nilai'] ?? '-' }}</td>
                <td class="text-center">{{ $nilai['predikat'] ?? '-' }}</td>
            </tr>
            @php
                if(isset($nilai['nilai']) && is_numeric($nilai['nilai'])) {
                    $totalNilai += $nilai['nilai'];
                    $count++;
                }
            @endphp
            @empty
            <tr>
                <td colspan="5" class="text-center">Tidak ada data mata pelajaran</td>
            </tr>
            @endforelse
            
            <!-- Summary Rows -->
            <tr>
                <td colspan="3" class="text-center bold">JUMLAH</td>
                <td class="text-center bold">{{ $totalNilai }}</td>
                <td class="text-center bg-light"></td>
            </tr>
            <tr>
                <td colspan="3" class="text-center bold">RATA-RATA</td>
                <td class="text-center bold">{{ $count > 0 ? round($totalNilai / $count, 1) : 0 }}</td>
                <td class="text-center bg-light"></td>
            </tr>
        </tbody>
    </table>

    <!-- Footer / Signatures -->
    <table class="signature-table">
        <tr>
            <td width="33%">
                <br>
                Orang Tua / Wali,
                <div class="signature-space"></div>
                (.......................................)
            </td>
            <td width="33%">
                <br>
                Wali Kelas,
                <div class="signature-space"></div>
                <span class="bold" style="text-decoration: underline;">{{ $kelas->waliKelas->nama ?? '.........................' }}</span>
            </td>
            <td width="33%">
                {{ $sekolah->kota ?? 'Bangka Tengah' }}, {{ now()->format('d F Y') }}<br>
                Kepala Madrasah,
                <div class="signature-space"></div>
                <span class="bold" style="text-decoration: underline;">(.......................................)</span>
                <br>NIP. ...........................
            </td>
        </tr>
    </table>

</body>
</html>
