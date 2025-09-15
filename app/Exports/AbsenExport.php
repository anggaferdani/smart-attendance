<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AbsenExport implements FromView, WithStyles
{
    protected $absen;
    protected $daysInMonth;
    protected $monthYear;

    public function __construct($absen, $daysInMonth, $monthYear)
    {
        $this->absen = $absen;
        $this->daysInMonth = $daysInMonth;
        $this->monthYear = $monthYear;
    }

    public function view(): View
    {
        return view('exports.absen', [
            'absen' => $this->absen,
            'daysInMonth' => $this->daysInMonth,
            'monthYear' => $this->monthYear,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:Z1')->getFont()->setBold(true);

        foreach(range('A','Z') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $rowIndex = 2;
        foreach ($this->absen as $r) {
            for ($d = 1; $d <= $this->daysInMonth; $d++) {
                $day = $r['harian'][$d] ?? ['label' => '', 'color' => ''];
                $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($d + 1);
                $cell = $col . $rowIndex;

                if ($day['color'] === 'yellow') {
                    $sheet->getStyle($cell)->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFFF00');
                } elseif ($day['color'] === 'red') {
                    $sheet->getStyle($cell)->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FF0000');
                } elseif ($day['color'] === 'green') {
                    $sheet->getStyle($cell)->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('00FF00');
                }
            }
            $rowIndex++;
        }

        return [];
    }
}
