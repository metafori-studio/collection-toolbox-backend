<?php

namespace Metafori\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Metafori\Core\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $enumClass = Permission::enumClass();

        if ($enumClass === null) {
            return;
        }

        foreach ($enumClass::cases() as $permission) {
            Permission::firstOrCreate([
                'name' => $permission->value,
            ]);
        }
    }
}
