<?php

namespace Metafori\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Metafori\Core\Enums\Role as RoleEnum;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate([
            'name' => RoleEnum::Admin,
        ]);
    }
}
