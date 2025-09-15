<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ShiftTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'tanggal',
            'Angga Ferdani',
            'Anisha Athaya',
            'Diana Jacklyn',
            'Rafa Nuzul',
            'Rezvan Ziandra',
        ];
    }

    public function array(): array
    {
        return [
            ['12-09-2025', 'WFO', 'WFO', '', '', 'WFO'],
            ['13-09-2025', '', 'WFH', 'WFH', 'WFO', ''],
        ];
    }
}
