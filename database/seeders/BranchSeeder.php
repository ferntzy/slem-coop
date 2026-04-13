<?php

namespace Database\Seeders;


use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [
            [
                'code' => '001',
                'name' => 'Hilongos',
                'address' => 'Western, Hilongos, Leyte',
                'contact_no' => '09123531552',
                'is_active' => true,
            ],
            [
                'code' => '002',
                'name' => 'Baybay',
                'address' => 'Baybay City, Leyte',
                'contact_no' => '09171234567',
                'is_active' => true,
            ],
            [
                'code' => '003',
                'name' => 'Ormoc',
                'address' => 'Ormoc City, Leyte',
                'contact_no' => '09179876543',
                'is_active' => true,
            ],
        ];

        foreach ($branches as $branch) {
            Branch::updateOrCreate(
                ['code' => $branch['code']],
                $branch
            );
        }
    }
}
