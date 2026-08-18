<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Crea el permiso `enviar_comunicaciones`, que gatea la pantalla de comunicaciones
 * masivas (invitaciones push). Se separa del rol `admin` a propósito para tener un
 * knob dedicado y revocable, desacoplado del sentinel de país (idPaisPermitido),
 * que no es una señal fiable de "admin global".
 *
 * Por defecto se otorga al rol `admin` (para no dejar la feature inaccesible al
 * deploy y ser consistente con el resto del backoffice). Para restringir a un
 * subconjunto de personas sin tocar código: revocar del rol `admin` y asignar el
 * permiso por usuario (Spatie lo soporta), o crear un rol dedicado.
 */
class AddPermisoEnviarComunicaciones extends Migration
{
    public function up()
    {
        // El cache de permisos de Spatie debe limpiarse alrededor de los cambios.
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permiso = Permission::firstOrCreate(['name' => 'enviar_comunicaciones']);

        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->givePermissionTo($permiso);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permiso = Permission::where('name', 'enviar_comunicaciones')->first();
        if ($permiso) {
            $permiso->delete();
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
