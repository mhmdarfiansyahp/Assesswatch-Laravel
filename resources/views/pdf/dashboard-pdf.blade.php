<!DOCTYPE html>
<html>

<head>
    <title>Laporan Kompetensi</title>
    <style>
        body {
            font-family: sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #f5f5f5;
        }
    </style>
</head>

<body>
    <h2>
        Laporan Rekap Kompetensi Prodi
    </h2>
    <table>

        <thead>
            <tr>
                <th>Program Studi</th>
                <th>Kompeten</th>
                <th>Tidak Kompeten</th>
                <th>Tidak Hadir</th>
            </tr>
        </thead>

        <tbody>

            @foreach($data as $item)
                <tr>
                    <td>{{ $item->nama_prodi }}</td>
                    <td>{{ $item->kompeten }}</td>
                    <td>{{ $item->tidak_kompeten }}</td>
                    <td>{{ $item->tidak_hadir }}</td>
                </tr>
            @endforeach

        </tbody>
    </table>
</body>

</html>