<table class="table table-bordered table-striped table-sm" style="font-size: 10px; width: 100%; border-collapse: collapse;">
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
        @foreach($absenData as $r)
            <tr>
                <td>{{ $r['nama'] }}</td>
                @for($d = 1; $d <= $daysInMonth; $d++)
                    @php $day = $r['harian'][$d] ?? ['label'=>'-','color'=>'']; @endphp
                    <td 
                        @if($day['color'] == 'red') style="background-color:#FF0000; color:#fff;" 
                        @elseif($day['color'] == 'yellow') style="background-color:#FFFF00;" 
                        @elseif($day['color'] == 'green') style="background-color:#00FF00;" 
                        @endif
                    >
                        {{ $day['label'] }}
                    </td>
                @endfor
                <td>{{ $r['totalHadir'] }}</td>
                <td>{{ $r['totalTelat'] }}</td>
                <td>{{ $r['totalIzin'] }}</td>
                <td>{{ $r['totalSakit'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
