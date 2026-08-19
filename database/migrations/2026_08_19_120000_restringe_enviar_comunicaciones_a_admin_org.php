<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Persona;

/**
 * Acota `enviar_comunicaciones` a usuarios puntuales.
 *
 * Antes el permiso estaba en el rol `admin` (que además tiene TODOS los permisos),
 * así que cualquier admin veía/usaba la solapa Comunicaciones. Se decidió que solo
 * la usen las cuentas administradoras por país (mails `@admin.org`, 1 por país).
 *
 * Por eso:
 *  1) se REVOCA del rol `admin` (para que no lo tengan todos los admins);
 *  2) se ASIGNA por usuario a cada Persona con mail `@admin.org`.
 *
 * El gate de la feature (solapa + rutas) ya es `permission:enviar_comunicaciones`,
 * no se toca: acá solo cambia QUIÉN tiene el permiso.
 *
 * NOTA: es un snapshot de las cuentas `@admin.org` existentes al migrar. Si más
 * adelante se crean nuevas cuentas `@admin.org`, hay que reasignarles el permiso
 * (o montar un observer/command que lo haga automáticamente). El seeder
 * RolePermissionsSeeder ya fue ajustado para NO volver a dárselo al rol `admin`.
 */
class RestringeEnviarComunicacionesAAdminOrg extends Migration
{
    private const DOMINIO = '%@admin.org';

    public function up()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permiso = Permission::firstOrCreate(['name' => 'enviar_comunicaciones']);

        // (1) Sacarlo del rol admin: deja de ser un permiso de "todos los admins".
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->revokePermissionTo($permiso);
        }

        // (2) Asignarlo por usuario a cada cuenta @admin.org (una por país).
        $asignados = 0;
        Persona::where('mail', 'like', self::DOMINIO)->get()->each(function (Persona $persona) use ($permiso, &$asignados) {
            $persona->givePermissionTo($permiso);
            $asignados++;
        });

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        echo "  enviar_comunicaciones: revocado del rol admin y asignado a {$asignados} cuenta(s) @admin.org.\n";
    }

    public function down()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permiso = Permission::where('name', 'enviar_comunicaciones')->first();
        if ($permiso) {
            // Revertir: quitar de los usuarios @admin.org y devolver al rol admin.
            Persona::where('mail', 'like', self::DOMINIO)->get()->each(function (Persona $persona) use ($permiso) {
                $persona->revokePermissionTo($permiso);
            });

            $admin = Role::where('name', 'admin')->first();
            if ($admin) {
                $admin->givePermissionTo($permiso);
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
