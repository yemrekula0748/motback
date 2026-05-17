<?php

namespace Database\Seeders;

use App\Models\Realm;
use Illuminate\Database\Seeder;

class RealmSeeder extends Seeder
{
    public function run(): void
    {
        Realm::query()->updateOrCreate(
            ['slug' => 'gokboru-main'],
            [
                'name' => 'Gokboru Main',
                'host' => env('MOTONLINE_REALM_GOKBORU_HOST', '31.223.124.154'),
                'port' => (int) env('MOTONLINE_REALM_GOKBORU_PORT', 7777),
                'map_path' => env('MOTONLINE_REALM_GOKBORU_MAP', '/Game/Maps/Gokboru'),
                'faction' => 'gokboru',
                'is_active' => (bool) env('MOTONLINE_REALM_GOKBORU_ACTIVE', true),
                'is_default' => true,
                'weight' => 100,
            ]
        );

        Realm::query()->updateOrCreate(
            ['slug' => 'bozkurt-main'],
            [
                'name' => 'Bozkurt Main',
                'host' => env('MOTONLINE_REALM_BOZKURT_HOST', '31.223.124.154'),
                'port' => (int) env('MOTONLINE_REALM_BOZKURT_PORT', 7777),
                'map_path' => env('MOTONLINE_REALM_BOZKURT_MAP', '/Game/Maps/Bozkurt'),
                'faction' => 'bozkurt',
                'is_active' => (bool) env('MOTONLINE_REALM_BOZKURT_ACTIVE', true),
                'is_default' => true,
                'weight' => 100,
            ]
        );
    }
}
