<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Crea el permiso `ver_reportes`, que gatea la bandeja de reportes de problemas /
 * sugerencias (y la creación de issues en GitHub desde ahí). Mismo patrón que
 * `enviar_comunicaciones`: un knob dedicado y revocable, desacoplado del rol `admin`.
 *
 * Por defecto se otorga al rol `admin` para no dejar la feature inaccesible tras el
 * deploy. Para restringir a un subconjunto de personas (ej. "solo Agus y Dara"):
 * revocar del rol `admin` y asignar el permiso por usuario. Con tinker:
 *
 *   $p = App\Persona::where('mail','...')->first();
 *   $p->givePermissionTo('ver_reportes');   // asignar
 *   Spatie\Permission\Models\Role::findByName('admin')->revokePermissionTo('ver_reportes'); // sacar del rol
 */
class AddPermisoVerReportes extends Migration
{
    public function up()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permiso = Permission::firstOrCreate(['name' => 'ver_reportes']);

        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->givePermissionTo($permiso);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permiso = Permission::where('name', 'ver_reportes')->first();
        if ($permiso) {
            $permiso->delete();
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
