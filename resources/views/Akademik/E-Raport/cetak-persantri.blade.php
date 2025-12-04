<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Hasil Belajar - {{ $santri->nama }}</title>
    <style>
        @page {
            size: A4;
            margin: 1.5cm 2cm;
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
            margin-bottom: 20px;
            position: relative;
            border-bottom: 3px double #000;
            padding-bottom: 15px;
        }
        .header-logo {
            position: absolute;
            left: 10px;
            top: 0;
            width: 75px;
            height: auto;
        }
        .header-text {
            margin-left: 0;
        }
        .header-instansi {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .school-name {
            font-size: 18pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 5px 0;
        }
        .school-address {
            font-size: 10pt;
        }
        .report-title {
            text-align: center;
            margin: 20px 0 30px 0;
            font-weight: bold;
            text-transform: uppercase;
        }
        .report-title h2 {
            font-size: 14pt;
            margin-bottom: 5px;
        }
        .student-info {
            width: 100%;
            margin-bottom: 20px;
            font-size: 11pt;
        }
        .student-info td {
            padding: 3px 5px;
            vertical-align: top;
        }
        .table-content {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11pt;
        }
        .table-content th, .table-content td {
            border: 1px solid #000;
            padding: 6px 8px;
        }
        .table-content th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .italic { font-style: italic; }
        
        .signature-table {
            width: 100%;
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .signature-space {
            height: 80px;
        }
        .rank-note {
            margin-top: 10px;
            font-size: 11pt;
            font-weight: bold;
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
            <div class="header-instansi">YAYASAN PONDOK PESANTREN</div>
            <div class="school-name">{{ $sekolah->nama_sekolah ?? 'AL-MADINAH' }}</div>
            <div class="school-address">{{ $sekolah->alamat ?? 'Alamat Madrasah' }}</div>
        </div>
    </div>

    <!-- Title -->
    <div class="report-title">
        <h2>LAPORAN HASIL BELAJAR SISWA</h2>
        <h2>(RAPOR)</h2>
    </div>

    <!-- Student Info -->
    <table class="student-info">
        <tr>
            <td width="18%">Nama Siswa</td>
            <td width="2%">:</td>
            <td width="45%" class="bold">{{ strtoupper($santri->nama) }}</td>
            <td width="15%">Kelas</td>
            <td width="2%">:</td>
            <td width="18%">{{ $kelas->level }} / {{ $kelas->nama_kelas }}</td>
        </tr>
        <tr>
            <td>NIS / NISN</td>
            <td>:</td>
            <td>{{ $santri->santri->nis ?? '-' }} / {{ $santri->santri->nisn ?? '-' }}</td>
            <td>Semester</td>
            <td>:</td>
            <td>{{ ucfirst($tahunAjaran->semester) }}</td>
        </tr>
        <tr>
            <td>Nama Madrasah</td>
            <td>:</td>
            <td>{{ $sekolah->nama_sekolah ?? 'Ponpes Al-Madinah' }}</td>
            <td>Tahun Pelajaran</td>
            <td>:</td>
            <td>{{ $tahunAjaran->nama }}</td>
        </tr>
    </table>

    <!-- Grades Table -->
    <h3 style="margin-bottom: 10px; font-size: 12pt;">A. KOMPETENSI PENGETAHUAN DAN KETERAMPILAN</h3>
    <table class="table-content">
        <thead>
            <tr>
                <th width="5%">NO</th>
                <th width="35%">MATA PELAJARAN</th>
                <th width="10%">KKM</th>
                <th width="10%">NILAI</th>
                <th width="10%">PREDIKAT</th>
                <th width="30%">KETERANGAN</th>
            </tr>
        </thead>
        <tbody>
            @php
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
                <td style="font-size: 10pt;">{{ $nilai['deskripsi'] }}</td>
            </tr>
            @php
                if(isset($nilai['nilai']) && is_numeric($nilai['nilai'])) {
                    $totalNilai += $nilai['nilai'];
                    $count++;
                }
            @endphp
            @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada data mata pelajaran</td>
            </tr>
            @endforelse
            
            <tr>
                <td colspan="3" class="text-center bold">JUMLAH NILAI</td>
                <td class="text-center bold">{{ $totalNilai }}</td>
                <td colspan="2" class="bg-light"></td>
            </tr>
            <tr>
                <td colspan="3" class="text-center bold">RATA-RATA</td>
                <td class="text-center bold">{{ $count > 0 ? round($totalNilai / $count, 1) : 0 }}</td>
                <td colspan="2" class="bg-light"></td>
            </tr>
        </tbody>
    </table>

    <!-- Ranking & Attendance -->
    <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
        <div style="width: 48%; float: left;">
            <h3 style="margin-bottom: 10px; font-size: 12pt;">B. KETIDAKHADIRAN</h3>
            <table class="table-content">
                <tr>
                    <td width="60%">Sakit</td>
                    <td width="40%" class="text-center">{{ $absensi['S'] }} hari</td>
                </tr>
                <tr>
                    <td>Izin</td>
                    <td class="text-center">{{ $absensi['I'] }} hari</td>
                </tr>
                <tr>
                    <td>Tanpa Keterangan</td>
                    <td class="text-center">{{ $absensi['A'] }} hari</td>
                </tr>
            </table>
        </div>
        <div style="width: 48%; float: right;">
             <h3 style="margin-bottom: 10px; font-size: 12pt;">C. PERINGKAT</h3>
             <div style="border: 1px solid #000; padding: 15px; text-align: center;">
                 Berdasarkan hasil penilaian, siswa tersebut memperoleh peringkat:
                 <br><br>
                 <span style="font-size: 16pt; font-weight: bold;">{{ $ranking }}</span>
                 <br><br>
                 dari <span class="bold">{{ $totalSiswa }}</span> siswa di kelas ini.
             </div>
        </div>
        <div style="clear: both;"></div>
    </div>

    <!-- Signatures -->
    <table class="signature-table">
        <tr>
            <td width="33%" class="text-center" style="vertical-align: top;">
                Mengetahui,<br>
                Orang Tua / Wali
                <div class="signature-space"></div>
                (.......................................)
            </td>
            <td width="33%" class="text-center" style="vertical-align: top;">
                <br>
                Wali Kelas
                <div class="signature-space"></div>
                <span class="bold" style="text-decoration: underline;">{{ $kelas->waliKelas->nama ?? '.........................' }}</span>
                <br>NIP. ...........................
            </td>
            <td width="33%" class="text-center" style="vertical-align: top;">
                {{ $sekolah->kota ?? 'Bangka Tengah' }}, {{ now()->format('d F Y') }}<br>
                Kepala Madrasah
                <div class="signature-space"></div>
                <span class="bold" style="text-decoration: underline;">(.......................................)</span>
                <br>NIP. ...........................
            </td>
        </tr>
    </table>

</body>
</html>
