<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Shift;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class ShiftImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            return;
        }

        $first = $rows->first();
        $firstArray = $first instanceof Collection ? $first->toArray() : (array)$first;
        $firstKeys = array_keys($firstArray);
        $isAssoc = count(array_filter($firstKeys, 'is_string')) > 0;

        if ($isAssoc) {
            foreach ($rows as $row) {
                $rowArray = $row instanceof Collection ? $row->toArray() : (array)$row;
                if (empty($rowArray['tanggal'])) {
                    continue;
                }

                $tanggalValue = $rowArray['tanggal'];

                if (is_numeric($tanggalValue)) {
                    $tanggal = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tanggalValue)->format('Y-m-d');
                } else {
                    $tanggal = Carbon::createFromFormat('d-m-Y', $tanggalValue)->format('Y-m-d');
                }

                foreach ($rowArray as $colName => $value) {
                    if ($colName === 'tanggal' || empty($value)) {
                        continue;
                    }

                    $userName = trim($colName);
                    $user = User::whereRaw('LOWER(name) = ?', [strtolower($userName)])->first();
                    if (!$user) {
                        continue;
                    }

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
        } else {
            $headers = $rows->shift();
            $headerArray = $headers instanceof Collection ? $headers->toArray() : (array)$headers;

            foreach ($rows as $row) {
                $rowArray = $row instanceof Collection ? $row->toArray() : (array)$row;
                if (empty($rowArray[0])) {
                    continue;
                }

                $tanggalValue = $rowArray[0];

                if (is_numeric($tanggalValue)) {
                    $tanggal = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tanggalValue)->format('Y-m-d');
                } else {
                    $tanggal = Carbon::createFromFormat('d-m-Y', $tanggalValue)->format('Y-m-d');
                }

                foreach ($rowArray as $colIndex => $value) {
                    if ($colIndex === 0 || empty($value)) {
                        continue;
                    }

                    $userName = trim($headerArray[$colIndex] ?? '');
                    if ($userName === '') {
                        continue;
                    }

                    $user = User::whereRaw('LOWER(name) = ?', [strtolower($userName)])->first();
                    if (!$user) {
                        continue;
                    }

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
}
