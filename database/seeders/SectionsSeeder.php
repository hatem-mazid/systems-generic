<?php

namespace Database\Seeders;

use App\Models\Section;
use Illuminate\Database\Seeder;

class SectionsSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'name' => 'Kitchen',
                'code' => 'kitchen',
                'is_active' => true,
                'position' => 1,
                'printer_name' => null,
            ],
            [
                'name' => 'Bar',
                'code' => 'bar',
                'is_active' => true,
                'position' => 2,
                'printer_name' => null,
            ],
        ];

        foreach ($rows as $row) {
            Section::updateOrCreate(
                ['code' => $row['code']],
                $row
            );
        }
    }
}
