<?php

namespace App\Http\Controllers;

use App\Models\Absen;
use App\Models\Token;
use Illuminate\Http\Request;

class TokenController extends Controller
{
    public function check(Request $request)
    {
        $token = $request->input('token');
        $userId = auth()->id();
        $today = \Carbon\Carbon::now()->setTime(4, 0);

        $tokenExists = Token::where('token', $token)->exists();

        $hasCheckedInToday = Absen::where('user_id', $userId)
            ->whereDate('tanggal', $today)
            ->whereHas('token', function ($query) {
                $query->where('status', 1);
            })
            ->exists();

        $hasCheckedOutToday = Absen::where('user_id', $userId)
            ->whereDate('tanggal', $today)
            ->whereHas('token', function ($query) {
                $query->where('status', 2);
            })
            ->exists();

        $disableCheckIn = $hasCheckedInToday;
        $disableCheckOut = !$hasCheckedInToday || $hasCheckedOutToday;

        if ($tokenExists) {
            do {
                $newToken = mt_rand(10000, 99999);
                $exists = Token::where('token', $newToken)->exists();
            } while ($exists);

            return response()->json([
                'exists' => true,
                'newToken' => $newToken,
                'disableCheckIn' => $disableCheckIn,
                'disableCheckOut' => $disableCheckOut
            ]);
        } else {
            return response()->json([
                'exists' => false,
                'disableCheckIn' => $disableCheckIn,
                'disableCheckOut' => $disableCheckOut
            ]);
        }
    }
}
