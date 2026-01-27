<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RingkasanSheet implements FromArray, WithTitle, WithHeadings, WithStyles
{
    protected $project;
    protected $data;
    protected $period;

    public function __construct($project, $data, $period)
    {
        $this->project = $project;
        $this->data = $data;
        $this->period = $period;
    }

    public function title(): string
    {
        return 'Ringkasan';
    }

    public function headings(): array
    {
        return ['Metrik', 'Nilai'];
    }

    public function array(): array
    {
        $periodLabel = match($this->period) {
            'today' => 'Hari Ini',
            '7' => '7 Hari Terakhir',
            '30' => '30 Hari Terakhir',
            'month' => 'Bulan Ini',
            'year' => 'Tahun Ini',
            default => '30 Hari Terakhir'
        };

        return [
            ['Nama Proyek', $this->project->name],
            ['Periode', $periodLabel],
            ['Tanggal Export', now()->format('d M Y H:i')],
            ['', ''],
            ['Total Tugas', $this->data['totalTasks']],
            ['Tugas Selesai', $this->data['completedTasks']],
            ['Total Jam Kerja', $this->data['totalHours'] . ' jam'],
            ['Anggota Tim', $this->data['totalMembers']],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
            'A1:A8' => ['font' => ['bold' => true]],
        ];
    }
}
