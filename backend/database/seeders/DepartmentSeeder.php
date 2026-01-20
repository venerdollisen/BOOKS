<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'code' => 'SALES',
                'name' => 'Sales Department',
                'description' => 'Responsible for all sales activities and customer acquisition',
                'manager_name' => 'John Smith',
                'budget' => '500000.00',
                'status' => 'active',
            ],
            [
                'code' => 'OPS',
                'name' => 'Operations Department',
                'description' => 'Manages day-to-day operations and processes',
                'manager_name' => 'Sarah Johnson',
                'budget' => '750000.00',
                'status' => 'active',
            ],
            [
                'code' => 'IT',
                'name' => 'Information Technology',
                'description' => 'IT infrastructure, support, and development',
                'manager_name' => 'Michael Chen',
                'budget' => '300000.00',
                'status' => 'active',
            ],
            [
                'code' => 'HR',
                'name' => 'Human Resources',
                'description' => 'Human resources and employee management',
                'manager_name' => 'Emily Davis',
                'budget' => '200000.00',
                'status' => 'active',
            ],
            [
                'code' => 'MARKETING',
                'name' => 'Marketing Department',
                'description' => 'Brand, marketing, and promotional activities',
                'manager_name' => 'Lisa Anderson',
                'budget' => '400000.00',
                'status' => 'active',
            ],
            [
                'code' => 'FINANCE',
                'name' => 'Finance Department',
                'description' => 'Financial planning, analysis, and accounting',
                'manager_name' => 'Robert Martinez',
                'budget' => '350000.00',
                'status' => 'active',
            ],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}
