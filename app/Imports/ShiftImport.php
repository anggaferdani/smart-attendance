<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Shift;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class ShiftImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        $headers = $rows->shift(); 

        foreach ($rows as $row) {
            if (empty($row[0])) continue;

            $tanggal = \Carbon\Carbon::parse($row['tanggal'])->format('Y-m-d');

            foreach ($row as $colIndex => $value) {
                if ($colIndex === 0 || empty($value)) continue;

                $userName = trim($headers[$colIndex]);
                $user = User::whereRaw('LOWER(name) = ?', [strtolower($userName)])->first();

                if (!$user) continue;

                Shift::updateOrCreate(
                    [
                        'tanggal' => $tanggal,
                        'user_id' => $user->id,
                    ],
                    [
                        'shift' => strtoupper(trim($value)),
                    ]
                );
            }
        }
    }
}
