<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AktivitasTerbaruSheet implements FromArray, WithTitle, WithHeadings, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function title(): string
    {
        return 'Aktivitas Terbaru';
    }

    public function headings(): array
    {
        return ['Aktivitas', 'Ditugaskan', 'Tanggal', 'Status'];
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->data['recentActivities'] as $activity) {
            $rows[] = [
                $activity['activity'],
                $activity['user'],
                $activity['date'],
                $activity['status']
            ];
        }
        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 11]],
        ];
    }
}
