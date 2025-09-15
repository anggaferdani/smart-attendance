<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        table { width: 100%; border-collapse: collapse; font-size: 7px; }
        th, td { border: 1px solid #000; padding: 1px; text-align: center; }
        .yellow { background-color: #FFFF00; }
        .red { background-color: #FF0000; color: #fff; }
        .green { background-color: #00FF00; }
    </style>
</head>
<body>
    <h3 style="text-align:center;">Absen Bulan {{ $monthYear }}</h3>
    <table>
        <thead>
            <tr>
                <th>Nama</th>
                @for($d = 1; $d <= $daysInMonth; $d++)
                    <th>{{ $d }}</th>
                @endfor
                <th>Hadir</th>
                <th>Terlambat</th>
                <th>Izin</th>
                <th>Sakit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($absen as $r)
                <tr>
                    <td>{{ $r['nama'] }}</td>
                    @for($d = 1; $d <= $daysInMonth; $d++)
                        @php $day = $r['harian'][$d]; @endphp
                        <td class="{{ $day['color'] }}">{{ $day['label'] }}</td>
                    @endfor
                    <td>{{ $r['totalHadir'] }}</td>
                    <td>{{ $r['totalTelat'] }}</td>
                    <td>{{ $r['totalIzin'] }}</td>
                    <td>{{ $r['totalSakit'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
