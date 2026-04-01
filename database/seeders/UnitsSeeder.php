<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\UnitGroup;
use Illuminate\Database\Seeder;

class UnitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = [
            [
                'name' => 'Indoor',
                // 'color' => '#3B82F6',
                'is_active' => true,
                'position' => 1,
                'units' => [
                    [
                        'name' => 'T1',
                        'capacity' => 2,
                        'type' => 'table',
                        // 'color' => '#60A5FA',
                        // 'properties' => ['x' => 80, 'y' => 120, 'label' => 'Window'],
                        'is_active' => true,
                        'is_available' => true,
                        'position' => 1,
                    ],
                    [
                        'name' => 'T2',
                        'capacity' => 4,
                        'type' => 'table',
                        // 'color' => '#2563EB',
                        // 'properties' => ['x' => 240, 'y' => 120, 'label' => 'Center'],
                        'is_active' => true,
                        'is_available' => true,
                        'position' => 2,
                    ],
                    [
                        'name' => 'T3',
                        'capacity' => 6,
                        'type' => 'table',
                        // 'color' => '#1D4ED8',
                        // 'properties' => ['x' => 420, 'y' => 120, 'label' => 'Family'],
                        'is_active' => true,
                        'is_available' => true,
                        'position' => 3,
                    ],
                ],
            ],
            [
                'name' => 'Outdoor',
                // 'color' => '#10B981',
                'is_active' => true,
                'position' => 2,
                'units' => [
                    [
                        'name' => 'G1',
                        'capacity' => 2,
                        'type' => 'game',
                        // 'color' => '#34D399',
                        // 'properties' => ['x' => 100, 'y' => 320, 'label' => 'Garden'],
                        'is_active' => true,
                        'is_available' => true,
                        'position' => 1,
                    ],
                    [
                        'name' => 'G2',
                        'capacity' => 4,
                        'type' => 'room',
                        // 'color' => '#059669',
                        // 'properties' => ['x' => 280, 'y' => 320, 'label' => 'Terrace'],
                        'is_active' => true,
                        'is_available' => true,
                        'position' => 2,
                    ],
                ],
            ],
        ];

        foreach ($groups as $groupData) {
            $units = $groupData['units'];
            unset($groupData['units']);

            $group = UnitGroup::updateOrCreate(
                ['name' => $groupData['name']],
                $groupData
            );

            foreach ($units as $unitData) {
                Unit::updateOrCreate(
                    [
                        'unit_group_id' => $group->id,
                        'name' => $unitData['name'],
                    ],
                    array_merge($unitData, ['unit_group_id' => $group->id])
                );
            }
        }
    }
}
