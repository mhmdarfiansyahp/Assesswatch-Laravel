<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Surat Keterangan Kompetensi</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
        }

        .kop-surat {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
        }

        .kop-surat h2 {
            margin: 0;
            font-size: 20px;
            text-transform: uppercase;
        }

        .kop-surat p {
            margin: 5px 0 0 0;
            font-size: 12px;
        }

        .judul-surat {
            text-align: center;
            margin-bottom: 30px;
        }

        .judul-surat h3 {
            margin: 0;
            text-transform: uppercase;
            text-decoration: underline;
        }

        .content {
            margin-bottom: 30px;
            text-align: justify;
        }

        .table-data {
            margin: 20px auto;
            width: 80%;
        }

        .table-data td {
            padding: 5px;
        }

        .footer {
            margin-top: 50px;
            float: right;
            width: 250px;
            text-align: center;
        }

        .qr-section {
            margin-top: 15px;
            margin-bottom: 15px;
        }

        .clear {
            clear: both;
        }
    </style>
</head>

<body>

    <div class="kop-surat">
        <h2>UNIVERSITAS TEKNOLOGI KAMPUS</h2>
        <p>Jl. Jenderal Sudirman No. 123, Jakarta Telp: (021) 123456</p>
        <p>Website: www.kampus.ac.id | Email: info@kampus.ac.id</p>
    </div>

    <div class="judul-surat">
        <h3>SURAT KETERANGAN KOMPETENSI</h3>
        <p>Nomor: SKK/{{ $asesmen->certificate_code }}</p>
    </div>

    <div class="content">
        <p>Rektor Universitas Teknologi Kampus dengan ini menerangkan bahwa:</p>

        <table class="table-data">
            <tr>
                <td width="35%">Nama Mahasiswa</td>
                <td width="5%">:</td>
                <td width="60%"><strong>{{ $asesmen->user->name }}</strong></td>
            </tr>
            <tr>
                <td>Nomor Induk Mahasiswa</td>
                <td>:</td>
                <td>{{ $asesmen->user->nim }}</td>
            </tr>
            <tr>
                <td>Program Studi</td>
                <td>:</td>
                <td>{{ $asesmen->sertifikasi->prodi?->nama_prodi ?? '-' }}</td>
            </tr>
        </table>

        <p>Telah dinyatakan <strong>KOMPETEN</strong> dalam asesmen kompetensi yang diselenggarakan pada tanggal
            {{ \Carbon\Carbon::parse($asesmen->tanggal_asesmen)->translatedFormat('d F Y') }} untuk skema sertifikasi:
        </p>

        <table class="table-data">
            <tr>
                <td width="35%">Nama Sertifikasi</td>
                <td width="5%">:</td>
                <td width="60%"><strong>{{ $asesmen->sertifikasi->nama_sertifikasi }}</strong></td>
            </tr>
            <tr>
                <td>Lembaga Penyelenggara</td>
                <td>:</td>
                <td>{{ $asesmen->sertifikasi->lembaga }}</td>
            </tr>
            <tr>
                <td>Tingkat / Level</td>
                <td>:</td>
                <td>{{ $asesmen->sertifikasi->level }}</td>
            </tr>
        </table>

        <p>Surat keterangan ini diterbitkan secara sistem sebagai bukti sementara sebelum Sertifikat Resmi diterbitkan
            oleh lembaga terkait.</p>
    </div>

    <div class="footer">
        <p>Jakarta, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>Mengetahui,</p>

        <div class="qr-section">
            <img src="data:image/svg+xml;base64,{{ $qrCode }}" alt="QR Code Verifikasi" width="120">
        </div>

        <p><strong>Tim Administrasi Sertifikasi</strong></p>
    </div>

    <div class="clear"></div>

</body>

</html>