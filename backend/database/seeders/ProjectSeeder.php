<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = [
            [
                'code' => 'PROJ-001',
                'name' => 'Website Redesign',
                'description' => 'Complete redesign of company website',
                'department_id' => 3, // IT
                'project_manager' => 'Michael Chen',
                'start_date' => '2026-01-15',
                'end_date' => '2026-06-30',
                'budget' => '50000.00',
                'status' => 'active',
            ],
            [
                'code' => 'PROJ-002',
                'name' => 'Q1 Marketing Campaign',
                'description' => 'Q1 2026 marketing and promotion campaign',
                'department_id' => 5, // Marketing
                'project_manager' => 'Lisa Anderson',
                'start_date' => '2026-01-01',
                'end_date' => '2026-03-31',
                'budget' => '75000.00',
                'status' => 'active',
            ],
            [
                'code' => 'PROJ-003',
                'name' => 'Sales Expansion Europe',
                'description' => 'Expansion into European market',
                'department_id' => 1, // Sales
                'project_manager' => 'John Smith',
                'start_date' => '2026-02-01',
                'end_date' => '2026-12-31',
                'budget' => '150000.00',
                'status' => 'planning',
            ],
            [
                'code' => 'PROJ-004',
                'name' => 'ERP Implementation',
                'description' => 'Enterprise resource planning system implementation',
                'department_id' => 6, // Finance
                'project_manager' => 'Robert Martinez',
                'start_date' => '2026-03-01',
                'end_date' => '2026-09-30',
                'budget' => '100000.00',
                'status' => 'planning',
            ],
            [
                'code' => 'PROJ-005',
                'name' => 'Employee Training Program',
                'description' => 'Annual employee skill development and training',
                'department_id' => 4, // HR
                'project_manager' => 'Emily Davis',
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'budget' => '60000.00',
                'status' => 'active',
            ],
        ];

        foreach ($projects as $project) {
            Project::create($project);
        }
    }
}
