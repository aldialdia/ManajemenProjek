<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StatusTugasSheet implements FromArray, WithTitle, WithHeadings, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function title(): string
    {
        return 'Status Tugas';
    }

    public function headings(): array
    {
        return ['Status', 'Jumlah Tugas', 'Persentase'];
    }

    public function array(): array
    {
        $tasksByStatus = $this->data['tasksByStatus'];
        $total = array_sum($tasksByStatus) ?: 1;

        return [
            ['Done (Selesai)', $tasksByStatus['done'] ?? 0, round(($tasksByStatus['done'] ?? 0) / $total * 100, 1) . '%'],
            ['In Progress (Sedang Dikerjakan)', $tasksByStatus['in_progress'] ?? 0, round(($tasksByStatus['in_progress'] ?? 0) / $total * 100, 1) . '%'],
            ['Review', $tasksByStatus['review'] ?? 0, round(($tasksByStatus['review'] ?? 0) / $total * 100, 1) . '%'],
            ['To Do (Belum Dikerjakan)', $tasksByStatus['todo'] ?? 0, round(($tasksByStatus['todo'] ?? 0) / $total * 100, 1) . '%'],
            ['', '', ''],
            ['Total', $total, '100%'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 11]],
            6 => ['font' => ['bold' => true]],
        ];
    }
}
