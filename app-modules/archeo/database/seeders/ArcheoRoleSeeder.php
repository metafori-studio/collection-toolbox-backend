<?php

namespace Metafori\Archeo\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class ArcheoRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'archeo_admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'archeo_readonly', 'guard_name' => 'web']);
    }
}
