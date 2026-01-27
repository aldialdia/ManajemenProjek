<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProduktivitasTimSheet implements FromArray, WithTitle, WithHeadings, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function title(): string
    {
        return 'Produktivitas Tim';
    }

    public function headings(): array
    {
        return ['Nama', 'Tugas Selesai', 'Total Tugas', 'Persentase Penyelesaian', 'Jam Kerja'];
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->data['tasksByUser'] as $user) {
            $rows[] = [
                $user->name,
                $user->completed_count ?? 0,
                $user->total_tasks_count ?? 0,
                ($user->completion_percentage ?? 0) . '%',
                ($user->work_hours ?? 0) . ' jam'
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
