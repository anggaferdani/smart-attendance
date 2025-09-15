<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Izin;
use App\Models\User;
use App\Models\Absen;
use App\Models\Shift;
use App\Models\Lokasi;
use App\Exports\AbsenExport;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class AbsenController extends Controller
{
    public function export(Request $request, $format = 'excel')
    {
        $bulan = $request->input('bulan', Carbon::now()->month);
        $tahun = $request->input('tahun', Carbon::now()->year);
        $daysInMonth = Carbon::create($tahun, $bulan)->daysInMonth;
        $monthYear = Carbon::create($tahun, $bulan)->format('F Y');

        $karyawans = User::orderBy('name')->get();
        $absenData = [];

        foreach ($karyawans as $karyawan) {
            $harian = [];
            $totalHadir = $totalTelat = $totalIzin = $totalSakit = 0;

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $tanggal = Carbon::create($tahun, $bulan, $d);

                if ($tanggal->isWeekend()) {
                    $harian[$d] = ['label' => '-', 'color' => ''];
                    continue;
                }

                $shift = Shift::where('user_id', $karyawan->id)
                    ->whereDate('tanggal', $tanggal)
                    ->first();

                $absensi = Absen::where('user_id', $karyawan->id)
                    ->whereDate('tanggal', $tanggal)
                    ->first();

                $izin = Izin::where('user_id', $karyawan->id)
                    ->whereDate('dari', '<=', $tanggal)
                    ->whereDate('sampai', '>=', $tanggal)
                    ->first();

                $label = $shift ? $shift->shift : '-';
                $color = '';

                if ($izin && $izin->status_process == 2) {
                    $color = 'green';
                    if ($izin->status_izin == 1) $totalIzin++;
                    elseif ($izin->status_izin == 2) $totalSakit++;
                } elseif ($absensi) {
                    if ($absensi->status == 3) {
                        $color = 'yellow';
                        $totalTelat++;
                        $totalHadir++;
                    } else {
                        $color = ''; // Tepat waktu
                        $totalHadir++;
                    }
                } else {
                    if ($shift) {
                        $color = 'red'; // Alpha / tidak hadir
                    } else {
                        $color = ''; // Tidak ada jadwal shift → tetap '-'
                    }
                }

                $harian[$d] = ['label' => $label, 'color' => $color];
            }

            $absenData[] = [
                'nama' => $karyawan->name,
                'harian' => $harian,
                'totalHadir' => $totalHadir,
                'totalTelat' => $totalTelat,
                'totalIzin' => $totalIzin,
                'totalSakit' => $totalSakit,
            ];
        }

        if ($format == 'excel') {
            return Excel::download(new AbsenExport($absenData, $daysInMonth, $monthYear), "Absen-$monthYear.xlsx");
        } else {
            $pdf = Pdf::loadView('exports.absen', [
                'absen' => $absenData,
                'daysInMonth' => $daysInMonth,
                'monthYear' => $monthYear,
            ])->setPaper('a4', 'landscape');

            return $pdf->download("Absen-$monthYear.pdf");
        }
    }

    public function absen(Request $request) {
        $query = Absen::with('token', 'token.lokasi', 'user')->latest();
        
        if ($request->has('search') && !empty($request->input('search'))) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('kode', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        if ($request->has('date_range') && !empty($request->input('date_range'))) {
            $dateRange = explode(' - ', $request->input('date_range'));
            
            if (count($dateRange) === 2) {
                $startDate = Carbon::parse(trim($dateRange[0]))->startOfDay();
                $endDate = Carbon::parse(trim($dateRange[1]))->endOfDay();
                
                $query->whereBetween('tanggal', [$startDate, $endDate]);
                
                $daysInMonth = $startDate->daysInMonth;
                $monthYear = $startDate->format('F Y');
            }
        } else {
            $tanggal = Carbon::now()->format('Y-m-d');
            $daysInMonth = Carbon::now()->daysInMonth;
            $monthYear = Carbon::now()->format('F Y');
        }
        
        if ($request->has('lokasi') && !empty($request->input('lokasi'))) {
            $lokasiId = $request->input('lokasi');
            $query->whereHas('token.lokasi', function ($q) use ($lokasiId) {
                $q->where('id', $lokasiId);
            });
        }
        
        if ($request->has('status_absen') && !empty($request->input('status_absen'))) {
            $statusAbsen = $request->input('status_absen');
            $query->whereHas('token', function ($q) use ($statusAbsen) {
                $q->where('status', $statusAbsen);
            });
        }
        
        if ($request->has('status') && !empty($request->input('status'))) {
            $statusKedatangan = $request->input('status');
            $query->where('status', $statusKedatangan);
        }
        
        $fileDate = $request->has('tanggal') && !empty($request->input('tanggal'))
                ? $request->input('tanggal')
                : Carbon::now()->format('Y-m-d');
        
        if ($request->has('export') && $request->export == 'excel') {
            
        }
        
        if ($request->has('export') && $request->export == 'pdf') {
            
        }

        $absens = $query->paginate(10);
        $lokasis = Lokasi::where('status', 1)->get();
    
        return view('admin.absen', [
            'absens' => $absens,
            'lokasis' => $lokasis,
            'daysInMonth' => $daysInMonth,
            'monthYear' => $monthYear
        ]);
    }
}
