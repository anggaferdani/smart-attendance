<?php

namespace App\Http\Controllers;

use App\Models\Absen;
use App\Models\Lokasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->input('filter', 'day');
        $lokasiFilter = $request->input('lokasi', '');

        $query = Absen::query();

        // filter waktu
        if ($filter === 'month') {
            $query->whereYear('tanggal', date('Y'))
                  ->whereMonth('tanggal', date('m'));
        } elseif ($filter === 'year') {
            $query->whereYear('tanggal', date('Y'));
        } else {
            $query->whereDate('tanggal', date('Y-m-d'));
        }

        // filter lokasi
        if ($lokasiFilter) {
            $query->whereHas('token', function ($q) use ($lokasiFilter) {
                $q->where('lokasi_id', $lokasiFilter);
            });
        }

        // status label
        $statusLabels = [
            1 => 'Lebih Awal',
            2 => 'Tepat Waktu',
            3 => 'Terlambat'
        ];

        // ambil data masuk
        $masuk = (clone $query)->whereHas('token', fn($q) => $q->where('status', 1))
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // ambil data pulang
        $pulang = (clone $query)->whereHas('token', fn($q) => $q->where('status', 2))
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // total
        $totalMasuk = $masuk->sum();
        $totalPulang = $pulang->sum();

        // mapping label
        $statusesMasuk = $masuk->mapWithKeys(fn($c, $s) => [$statusLabels[$s] => $c]);
        $statusesPulang = $pulang->mapWithKeys(fn($c, $s) => [$statusLabels[$s] => $c]);

        $lokasis = Lokasi::where('status', 1)->get();

        return view('admin.dashboard', [
            'filter' => $filter,
            'statusesMasuk' => $statusesMasuk,
            'statusesPulang' => $statusesPulang,
            'totalMasuk' => $totalMasuk,
            'totalPulang' => $totalPulang,
            'lokasis' => $lokasis,
        ]);
    }
}
