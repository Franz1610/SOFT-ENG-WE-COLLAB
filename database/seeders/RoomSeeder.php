<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoomSeeder extends Seeder
{
    /**
     * Seed the rooms table with initial room inventory per category.
     */
    public function run(): void
    {
        $plan = [
            'individual' => 12, // INDIV ROOM: 12
            'master'     => 5,  // MASTER ROOM: 5
            'common'     => 3,  // COMMON ROOM: 3
        ];

        $now = now();
        $rows = [];

        foreach ($plan as $category => $count) {
            for ($i = 1; $i <= $count; $i++) {
                $prefix = match ($category) {
                    'individual' => 'IND',
                    'common'     => 'COM',
                    'master'     => 'MAS',
                    default      => strtoupper(substr($category, 0, 3)),
                };

                $rows[] = [
                    'category'    => $category,
                    'room_number' => sprintf('%s-%02d', $prefix, $i),
                    'status'      => 'available',
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];
            }
        }

        // Upsert to avoid duplicates when re-seeding
        DB::table('rooms')->upsert(
            $rows,
            ['category', 'room_number'],
            ['status', 'updated_at']
        );
    }
}
