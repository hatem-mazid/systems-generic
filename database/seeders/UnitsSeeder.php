<?php

namespace Database\Seeders;

use App\Enums\UnitType;
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
                        'type' => UnitType::Table,
                        // 'color' => '#60A5FA',
                        // 'properties' => ['x' => 80, 'y' => 120, 'label' => 'Window'],
                        'active' => true,
                        'status' => 'available',
                        'position' => 1,
                        'fee_per_hour' => 10.00,
                    ],
                    [
                        'name' => 'T2',
                        'capacity' => 4,
                        'type' => UnitType::Table,
                        // 'color' => '#2563EB',
                        // 'properties' => ['x' => 240, 'y' => 120, 'label' => 'Center'],
                        'active' => true,
                        'status' => 'reserved',
                        'reserved_at' => now()->addMinutes(30),
                        'reserved_by' => 'Walk-in customer',
                        'position' => 2,
                        'fee_per_hour' => 12.50,
                    ],
                    [
                        'name' => 'T3',
                        'capacity' => 6,
                        'type' => UnitType::Table,
                        // 'color' => '#1D4ED8',
                        // 'properties' => ['x' => 420, 'y' => 120, 'label' => 'Family'],
                        'active' => true,
                        'status' => 'occupied',
                        'position' => 3,
                        'fee_per_hour' => 15.00,
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
                        'type' => UnitType::Room,
                        // 'color' => '#34D399',
                        // 'properties' => ['x' => 100, 'y' => 320, 'label' => 'Garden'],
                        'active' => false,
                        'status' => 'inactive',
                        'position' => 1,
                        'fee_per_hour' => 20.00,
                    ],
                    [
                        'name' => 'G2',
                        'capacity' => 4,
                        'type' => UnitType::Room,
                        // 'color' => '#059669',
                        // 'properties' => ['x' => 280, 'y' => 320, 'label' => 'Terrace'],
                        'active' => true,
                        'status' => 'available',
                        'position' => 2,
                        'fee_per_hour' => 25.00,
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
