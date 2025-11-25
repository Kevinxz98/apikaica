<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    
    public function run(): void
    {       
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'ver usuarios',
            'crear usuarios',
            'editar usuarios',
            'eliminar usuarios',
            'gestionar roles',
        ];

        $servicePermissions = [
            'view services',
            'create services',
            'update services',
            'delete services',
        ];

         foreach (array_merge($permissions, $servicePermissions) as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $client = Role::firstOrCreate(['name' => 'client']);

        $admin->givePermissionTo(Permission::all());

        $user = User::where('email', 'pereakevin001@gmail.com')->first(); // o busca por email, por ejemplo
        if ($user) {
            $user->assignRole('admin');
        }

        // El rol client no recibe permisos adicionales

    }
}
