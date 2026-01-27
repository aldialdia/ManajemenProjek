<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProjectReportExport implements WithMultipleSheets
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

    public function sheets(): array
    {
        return [
            'Ringkasan' => new Sheets\RingkasanSheet($this->project, $this->data, $this->period),
            'Status Tugas' => new Sheets\StatusTugasSheet($this->data),
            'Produktivitas Tim' => new Sheets\ProduktivitasTimSheet($this->data),
            'Aktivitas Terbaru' => new Sheets\AktivitasTerbaruSheet($this->data),
        ];
    }
}
