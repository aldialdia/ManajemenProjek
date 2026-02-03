<?php

namespace App\Exports;

use App\Models\Project;
use App\Models\User;
use App\Models\Task;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DashboardReportExport implements WithMultipleSheets
{
    protected $stats;
    protected $projectsByType;
    protected $taskDistribution;
    protected $projectsWithIssues;

    public function __construct($stats, $projectsByType, $taskDistribution, $projectsWithIssues)
    {
        $this->stats = $stats;
        $this->projectsByType = $projectsByType;
        $this->taskDistribution = $taskDistribution;
        $this->projectsWithIssues = $projectsWithIssues;
    }

    public function sheets(): array
    {
        return [
            new OverviewSheet($this->stats, $this->projectsByType),
            new ProjectsSheet(),
            new TaskDistributionSheet($this->taskDistribution),
            new ProjectsWithIssuesSheet($this->projectsWithIssues),
        ];
    }
}

// Overview Sheet
class OverviewSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $stats;
    protected $projectsByType;

    public function __construct($stats, $projectsByType)
    {
        $this->stats = $stats;
        $this->projectsByType = $projectsByType;
    }

    public function collection()
    {
        return collect([
            ['Total Projects', $this->stats['total_projects']],
            ['RBB Projects', $this->projectsByType['rbb'] ?? 0],
            ['Non-RBB Projects', $this->projectsByType['non_rbb'] ?? 0],
            [''],
            ['Total Tasks', $this->stats['total_tasks']],
            ['Completed Tasks', $this->stats['completed_tasks']],
            ['Pending Tasks', $this->stats['pending_tasks']],
            [''],
            ['Total Users', $this->stats['total_users']],
            ['Active Users', $this->stats['active_users']],
            [''],
            ['Total Hours This Month', round($this->stats['total_hours_this_month'], 2)],
        ]);
    }

    public function headings(): array
    {
        return [
            'Metric',
            'Value',
        ];
    }

    public function title(): string
    {
        return 'Overview';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}

// Projects Sheet with Year column and AutoFilter
class ProjectsSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles
{
    public function collection()
    {
        return Project::with(['users', 'tasks'])->get();
    }

    public function map($project): array
    {
        return [
            $project->created_at ? $project->created_at->format('Y') : '-',
            $project->name,
            $project->type->value,
            $project->status->label(),
            $project->start_date ? $project->start_date->format('d/m/Y') : '-',
            $project->end_date ? $project->end_date->format('d/m/Y') : '-',
            $project->users->count(),
            $project->tasks->count(),
            $project->tasks->where('status', 'done')->count(),
            $project->progress . '%',
            $project->goals ?? '-',
        ];
    }

    public function headings(): array
    {
        return [
            'Tahun',
            'Project Name',
            'Type',
            'Status',
            'Start Date',
            'End Date',
            'Team Members',
            'Total Tasks',
            'Completed Tasks',
            'Progress',
            'Goals',
        ];
    }

    public function title(): string
    {
        return 'All Projects';
    }

    public function styles(Worksheet $sheet)
    {
        // Get the last row with data
        $lastRow = $sheet->getHighestRow();
        
        // Set AutoFilter on header row (columns A to K)
        $sheet->setAutoFilter('A1:K' . $lastRow);

        // Set column width for better readability
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(12);
        $sheet->getColumnDimension('I')->setWidth(15);
        $sheet->getColumnDimension('J')->setWidth(10);
        $sheet->getColumnDimension('K')->setWidth(25);

        return [
            1 => ['font' => ['bold' => true, 'size' => 11]],
        ];
    }
}

// Task Distribution Sheet with AutoFilter
class TaskDistributionSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $taskDistribution;

    public function __construct($taskDistribution)
    {
        $this->taskDistribution = $taskDistribution;
    }

    public function collection()
    {
        return $this->taskDistribution->map(function ($member) {
            return [
                'name' => $member->name,
                'email' => $member->email,
                'total_tasks' => $member->total_tasks,
                'completed_tasks' => $member->completed_tasks,
                'pending_tasks' => $member->pending_tasks,
                'completion_rate' => $member->total_tasks > 0
                    ? round(($member->completed_tasks / $member->total_tasks) * 100) . '%'
                    : '0%',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Total Tasks',
            'Completed Tasks',
            'Pending Tasks',
            'Completion Rate',
        ];
    }

    public function title(): string
    {
        return 'Task Distribution';
    }

    public function styles(Worksheet $sheet)
    {
        // Get the last row with data
        $lastRow = $sheet->getHighestRow();
        
        // Set AutoFilter on header row (columns A to F)
        $sheet->setAutoFilter('A1:F' . $lastRow);

        return [
            1 => ['font' => ['bold' => true, 'size' => 11]],
        ];
    }
}

// Projects With Issues Sheet with Year column and AutoFilter
class ProjectsWithIssuesSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles
{
    protected $projectsWithIssues;

    public function __construct($projectsWithIssues)
    {
        $this->projectsWithIssues = $projectsWithIssues;
    }

    public function collection()
    {
        return $this->projectsWithIssues;
    }

    public function map($project): array
    {
        $issues = [];

        if ($project->status->value === 'on_hold') {
            $issues[] = 'On Hold';
        }

        if ($project->end_date && $project->end_date->isPast() && $project->status->value !== 'done') {
            $issues[] = 'Overdue (' . $project->end_date->locale('id')->diffForHumans() . ')';
        }

        return [
            $project->created_at ? $project->created_at->format('Y') : '-',
            $project->name,
            $project->type->value,
            $project->status->label(),
            $project->end_date ? $project->end_date->format('d/m/Y') : '-',
            implode(', ', $issues),
        ];
    }

    public function headings(): array
    {
        return [
            'Tahun',
            'Project Name',
            'Type',
            'Status',
            'Deadline',
            'Issues',
        ];
    }

    public function title(): string
    {
        return 'Projects With Issues';
    }

    public function styles(Worksheet $sheet)
    {
        // Get the last row with data
        $lastRow = $sheet->getHighestRow();
        
        // Set AutoFilter on header row (columns A to F)
        $sheet->setAutoFilter('A1:F' . $lastRow);

        return [
            1 => ['font' => ['bold' => true, 'size' => 11]],
        ];
    }
}
