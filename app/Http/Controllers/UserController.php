<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Izin;
use App\Models\Absen;
use App\Models\Shift;
use App\Models\Token;
use App\Models\Lokasi;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\ContactPerson;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function dashboard() {
        return view('user.dashboard');
    }

    public function shift() {
        return view('user.shift');
    }
    
    public function index(Request $request) {
        $user = auth()->user();
        $lokasi = $user->lokasi_id ? Lokasi::find($user->lokasi_id) : null;

        $lokasis = Lokasi::where('status', 1)->get();

        $today = now()->toDateString();
        $shiftHariIni = Shift::where('user_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        $absenHariIni = Absen::where('user_id', $user->id)
            ->whereDate('tanggal', $today)
            ->get();

        $sudahCheckIn = $absenHariIni->where('token.status', 1)->count() > 0;
        $sudahCheckOut = $absenHariIni->where('token.status', 2)->count() > 0;

        return view('user.index', compact('lokasis', 'lokasi', 'shiftHariIni', 'sudahCheckIn', 'sudahCheckOut'));
    }

    public function response($kode) {
        $absen = Absen::with('token')->where('kode', $kode)->first();
        return view('user.response', compact('absen'));
    }

    private function image(UploadedFile $file): string
    {
        $folder = 'progress';
        
        do {
            $name = Str::random(16) . '.' . $file->getClientOriginalExtension();
            $path = $folder . '/' . $name;
        } while (Absen::where('progress_file', $path)->exists());

        Storage::disk('public')->put($path, file_get_contents($file));

        return $path;
    }

    public function absen(Request $request)
    {
        $rules = [
            'shift' => 'required',
            'status' => 'required',
            'jam_masuk' => 'required',
            'jam_pulang' => 'required',
            'progress_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
        ];

        if ($request->shift == 'WFO') {
            $rules['lokasi_id'] = 'required';
            $rules['token'] = 'required';
            $rules['lat'] = 'required';
            $rules['long'] = 'required';
        }

        $request->validate($rules);

        try {
            $progressPath = null;
            if ($request->hasFile('progress_file')) {
                $progressPath = $this->image($request->file('progress_file'));
            }

            $arrayToken = [
                'lokasi_id' => $request['lokasi_id'] ?? null,
                'token' => $request['token'],
                'tanggal' => now(),
                'status' => $request['status'],
            ];

            $token = Token::create($arrayToken);

            if ($token) {
                $time = $token->tanggal->format('H:i');
                $jamMasukTime = Carbon::parse($request['jam_masuk'])->format('H:i');
                $jamPulangTime = Carbon::parse($request['jam_pulang'])->format('H:i');

                $absenStatus = 1;

                if ($token->status == 1) {
                    $absenStatus = ($time < $jamMasukTime) ? 1 : (($time == $jamMasukTime) ? 2 : 3);
                } elseif ($token->status == 2) {
                    $absenStatus = ($time < $jamPulangTime) ? 1 : (($time == $jamPulangTime) ? 2 : 3);
                }

                $kode = $this->generateKodeAbsen();

                $arrayAbsen = [
                    'token_id' => $token->id,
                    'user_id' => Auth::id(),
                    'kode' => $kode,
                    'lat' => $request['lat'] ?? null,
                    'long' => $request['long'] ?? null,
                    'tanggal' => now(),
                    'shift' => $request['shift'],
                    'status' => $absenStatus,
                    'progress_file' => $progressPath,
                ];

                $absen = Absen::create($arrayAbsen);

                if ($absen) {
                    return redirect()->route('user.response', ['kode' => $absen->kode])
                        ->with('success', 'Success.');
                }
            }
        } catch (\Throwable $th) {
            return back()->with('error', $th->getMessage());
        }
    }

    public function history(Request $request) {
        $userId = Auth::id();
        $currentYear = now()->year;
        $currentMonth = now()->month;

        $selectedMonth = $request->get('bulan', $currentMonth);

        // ambil semua absen bulan berjalan
        $absens = Absen::with('token', 'token.lokasi', 'user')
            ->where('user_id', $userId)
            ->whereYear('tanggal', $currentYear)
            ->whereMonth('tanggal', $selectedMonth)
            ->latest()
            ->paginate(5);

        // ambil kehadiran unik per hari (biar 1 hari = 1 hadir)
        $absenKehadiran = Absen::with('token', 'token.lokasi', 'user')
            ->where('user_id', $userId)
            ->whereYear('tanggal', $currentYear)
            ->whereMonth('tanggal', $selectedMonth)
            ->whereIn('id', function ($query) use ($userId, $currentYear, $selectedMonth) {
                $query->select(DB::raw('MIN(id)'))
                    ->from('absens')
                    ->where('user_id', $userId)
                    ->whereYear('tanggal', $currentYear)
                    ->whereMonth('tanggal', $selectedMonth)
                    ->groupBy(DB::raw('DATE(tanggal)'));
            })
            ->latest()
            ->get();

        // izin (status_izin = 1)
        $izinDays = Izin::where('user_id', $userId)
            ->where('status_izin', 1)
            ->where('status', 1)
            ->whereYear('dari', $currentYear)
            ->whereMonth('dari', $selectedMonth)
            ->sum(DB::raw("DATEDIFF(sampai, dari) + 1"));

        // sakit (status_izin = 2)
        $sickDays = Izin::where('user_id', $userId)
            ->where('status_izin', 2)
            ->where('status', 1)
            ->whereYear('dari', $currentYear)
            ->whereMonth('dari', $selectedMonth)
            ->sum(DB::raw("DATEDIFF(sampai, dari) + 1"));

        // terlambat (status = 3 di absen, token.status = 1 = masuk)
        $lateDays = Absen::where('user_id', $userId)
            ->where('status', 3)
            ->whereHas('token', function($query) {
                $query->where('status', 1);
            })
            ->whereYear('tanggal', $currentYear)
            ->whereMonth('tanggal', $selectedMonth)
            ->count();

        // total hari kerja (Senin–Jumat saja)
        $totalDaysInMonth = $this->countWorkDays($currentYear, $selectedMonth);

        // hadir unik
        $actualAttendanceDays = $absenKehadiran->count();

        // hitung alpha
        $alphaDays = $totalDaysInMonth - ($actualAttendanceDays + $izinDays + $sickDays);
        if ($alphaDays < 0) $alphaDays = 0;

        // persentase hadir
        $attendancePercentage = $totalDaysInMonth > 0 
            ? round(($actualAttendanceDays / $totalDaysInMonth) * 100, 1) 
            : 0;

        $contactPerson = ContactPerson::where('status', 1)->first();

        return view('user.history', compact(
            'absens',
            'selectedMonth',
            'izinDays',
            'sickDays',
            'lateDays',
            'alphaDays',
            'attendancePercentage',
            'contactPerson',
        ));
    }

    // fungsi hitung hari kerja Senin–Jumat
    private function countWorkDays($year, $month) {
        $totalDays = 0;
        $date = Carbon::create($year, $month, 1);
        while ($date->month == $month) {
            if ($date->isWeekday()) { // Senin–Jumat
                $totalDays++;
            }
            $date->addDay();
        }
        return $totalDays;
    }

    private function generateKodeAbsen() {
        do {
            $kode = mt_rand(100000000000, 999999999999);
            $exists = Absen::where('kode', $kode)->exists();
        } while ($exists);
    
        return $kode;
    }
}
