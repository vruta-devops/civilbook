<?php

namespace Database\Seeders;

use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $checkDepartment = Department::first();

        if (empty($checkDepartment)) {
            $timestamp = Carbon::now();

            $departments = [
                [
                    "id" => 13,
                    "name" => "Workmanship",
                    "status"=>  1,
                    "is_special" => 0,
                    "created_at" => $timestamp,
                    "updated_at" => $timestamp,
                ],
                [
                    "id" => 14,
                    "name" => "Contractors & Builders",
                    "status"=>  1,
                    "is_special" => 0,
                    "created_at" => $timestamp,
                    "updated_at" => $timestamp,
                ],
                [
                    "id" => 15,
                    "name" => "Machineries",
                    "status"=>  1,
                    "is_special" => 1,
                    "created_at" => $timestamp,
                    "updated_at" => $timestamp,
                ],
                [
                    "id" => 18,
                    "name" => "Materials",
                    "status"=>  1,
                    "is_special" => 0,
                    "created_at" => $timestamp,
                    "updated_at" => $timestamp,
                ],
                [
                    "id" => 19,
                    "name" => "Engineers/Govt. Approvals",
                    "status"=>  1,
                    "is_special" => 0,
                    "created_at" => $timestamp,
                    "updated_at" => $timestamp,
                ],
                [
                    "id" => 20,
                    "name" => "Job Seekers",
                    "status"=>  1,
                    "is_special" => 0,
                    "created_at" => $timestamp,
                    "updated_at" => $timestamp,
                ],
                [
                    "id" => 21,
                    "name" => "Plot Sales",
                    "status"=>  1,
                    "is_special" => 0,
                    "created_at" => $timestamp,
                    "updated_at" => $timestamp,
                ],
                [
                    "id" => 22,
                    "name" => "Building Loans",
                    "status"=>  1,
                    "is_special" => 0,
                    "created_at" => $timestamp,
                    "updated_at" => $timestamp,
                ],
            ];

            Department::insert($departments);
        }
    }
}
