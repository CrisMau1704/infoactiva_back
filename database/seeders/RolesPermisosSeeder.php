<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Rol;
use App\Models\Permiso;
use App\Models\User;

class RolesPermisosSeeder extends Seeder
{
    public function run(): void
    {
        $ahora = now();

        // ========================
        // 1. CREAR PERMISOS
        // ========================
        $permisos = [
            // Dashboard
            ['codigo' => 'dashboard.ver', 'descripcion' => 'Ver dashboard', 'categoria' => 'Dashboard'],

            // Usuarios
            ['codigo' => 'usuarios.ver', 'descripcion' => 'Ver usuarios', 'categoria' => 'Usuarios'],
            ['codigo' => 'usuarios.crear', 'descripcion' => 'Crear usuarios', 'categoria' => 'Usuarios'],
            ['codigo' => 'usuarios.editar', 'descripcion' => 'Editar usuarios', 'categoria' => 'Usuarios'],
            ['codigo' => 'usuarios.eliminar', 'descripcion' => 'Eliminar usuarios', 'categoria' => 'Usuarios'],

            // Roles
            ['codigo' => 'roles.ver', 'descripcion' => 'Ver roles', 'categoria' => 'Roles'],
            ['codigo' => 'roles.crear', 'descripcion' => 'Crear roles', 'categoria' => 'Roles'],
            ['codigo' => 'roles.editar', 'descripcion' => 'Editar roles', 'categoria' => 'Roles'],
            ['codigo' => 'roles.eliminar', 'descripcion' => 'Eliminar roles', 'categoria' => 'Roles'],

            // Permisos
            ['codigo' => 'permisos.ver', 'descripcion' => 'Ver permisos', 'categoria' => 'Permisos'],
            ['codigo' => 'permisos.crear', 'descripcion' => 'Crear permisos', 'categoria' => 'Permisos'],
            ['codigo' => 'permisos.editar', 'descripcion' => 'Editar permisos', 'categoria' => 'Permisos'],
            ['codigo' => 'permisos.eliminar', 'descripcion' => 'Eliminar permisos', 'categoria' => 'Permisos'],

            // Regionales
            ['codigo' => 'regionales.ver', 'descripcion' => 'Ver regionales', 'categoria' => 'Regionales'],
            ['codigo' => 'regionales.crear', 'descripcion' => 'Crear regionales', 'categoria' => 'Regionales'],
            ['codigo' => 'regionales.editar', 'descripcion' => 'Editar regionales', 'categoria' => 'Regionales'],
            ['codigo' => 'regionales.eliminar', 'descripcion' => 'Eliminar regionales', 'categoria' => 'Regionales'],

            // Almacenes
            ['codigo' => 'almacenes.ver', 'descripcion' => 'Ver almacenes', 'categoria' => 'Almacenes'],
            ['codigo' => 'almacenes.crear', 'descripcion' => 'Crear almacenes', 'categoria' => 'Almacenes'],
            ['codigo' => 'almacenes.editar', 'descripcion' => 'Editar almacenes', 'categoria' => 'Almacenes'],
            ['codigo' => 'almacenes.eliminar', 'descripcion' => 'Eliminar almacenes', 'categoria' => 'Almacenes'],

            // Clientes
            ['codigo' => 'clientes.ver', 'descripcion' => 'Ver clientes', 'categoria' => 'Clientes'],
            ['codigo' => 'clientes.crear', 'descripcion' => 'Crear clientes', 'categoria' => 'Clientes'],
            ['codigo' => 'clientes.editar', 'descripcion' => 'Editar clientes', 'categoria' => 'Clientes'],
            ['codigo' => 'clientes.eliminar', 'descripcion' => 'Eliminar clientes', 'categoria' => 'Clientes'],

            // Áreas de cliente
            ['codigo' => 'areas.ver', 'descripcion' => 'Ver áreas', 'categoria' => 'Áreas'],
            ['codigo' => 'areas.crear', 'descripcion' => 'Crear áreas', 'categoria' => 'Áreas'],
            ['codigo' => 'areas.editar', 'descripcion' => 'Editar áreas', 'categoria' => 'Áreas'],
            ['codigo' => 'areas.eliminar', 'descripcion' => 'Eliminar áreas', 'categoria' => 'Áreas'],

            // Cajas
            ['codigo' => 'cajas.ver', 'descripcion' => 'Ver cajas', 'categoria' => 'Cajas'],
            ['codigo' => 'cajas.crear', 'descripcion' => 'Crear cajas', 'categoria' => 'Cajas'],
            ['codigo' => 'cajas.editar', 'descripcion' => 'Editar cajas', 'categoria' => 'Cajas'],
            ['codigo' => 'cajas.eliminar', 'descripcion' => 'Eliminar cajas', 'categoria' => 'Cajas'],

            // Guías de salida
            ['codigo' => 'guias_salida.ver', 'descripcion' => 'Ver guías de salida', 'categoria' => 'Guías de Salida'],
            ['codigo' => 'guias_salida.crear', 'descripcion' => 'Crear guías de salida', 'categoria' => 'Guías de Salida'],

            // Guías de ingreso
            ['codigo' => 'guias_ingreso.ver', 'descripcion' => 'Ver guías de ingreso', 'categoria' => 'Guías de Ingreso'],
            ['codigo' => 'guias_ingreso.crear', 'descripcion' => 'Crear guías de ingreso', 'categoria' => 'Guías de Ingreso'],

            // Ubicaciones
            ['codigo' => 'ubicaciones.ver', 'descripcion' => 'Ver ubicaciones', 'categoria' => 'Ubicaciones'],
            ['codigo' => 'ubicaciones.crear', 'descripcion' => 'Crear ubicaciones', 'categoria' => 'Ubicaciones'],
            ['codigo' => 'ubicaciones.editar', 'descripcion' => 'Editar ubicaciones', 'categoria' => 'Ubicaciones'],
            ['codigo' => 'ubicaciones.eliminar', 'descripcion' => 'Eliminar ubicaciones', 'categoria' => 'Ubicaciones'],

            // Stock y Reportes
            ['codigo' => 'stock.ver', 'descripcion' => 'Ver stock', 'categoria' => 'Stock'],
            ['codigo' => 'reportes.ver', 'descripcion' => 'Ver reportes', 'categoria' => 'Reportes'],
            ['codigo' => 'reportes.exportar', 'descripcion' => 'Exportar reportes', 'categoria' => 'Reportes'],
        ];

        foreach ($permisos as $permiso) {
            Permiso::updateOrCreate(
                ['codigo' => $permiso['codigo']],
                array_merge($permiso, [
                    'fecha_creacion' => $ahora,
                    'fecha_actualizacion' => $ahora,
                ])
            );
        }

        $this->command->info('✅ Permisos creados: ' . count($permisos));

        // ========================
        // 2. CREAR ROLES
        // ========================
        $roles = [
            ['nombre' => 'Administrador', 'descripcion' => 'Acceso total al sistema', 'nivel' => 1],
            ['nombre' => 'Jefe de Almacén', 'descripcion' => 'Gestiona operaciones del almacén', 'nivel' => 2],
            ['nombre' => 'Auxiliar de Almacén', 'descripcion' => 'Registra ingresos y consulta ubicaciones', 'nivel' => 3],
            ['nombre' => 'Jefe de Administración', 'descripcion' => 'Consulta stock y genera reportes', 'nivel' => 2],
        ];

        $rolesCreados = [];
        foreach ($roles as $rol) {
            $rolesCreados[$rol['nombre']] = Rol::updateOrCreate(
                ['nombre' => $rol['nombre']],
                array_merge($rol, [
                    'fecha_creacion' => $ahora,
                    'fecha_actualizacion' => $ahora,
                ])
            );
        }

        $this->command->info('✅ Roles creados: ' . count($roles));

        // ========================
        // 3. ASIGNAR PERMISOS A ROLES
        // ========================
        $pivotData = [
            'fecha_creacion' => $ahora,
            'fecha_actualizacion' => $ahora,
        ];

        // ADMINISTRADOR: todos los permisos
        $todosLosPermisos = Permiso::pluck('id_permiso')->toArray();
        $rolesCreados['Administrador']->permisos()->syncWithPivotValues($todosLosPermisos, $pivotData);

        // JEFE DE ALMACÉN
        $permisosJefeAlmacen = Permiso::whereIn('codigo', [
            'dashboard.ver',
            'regionales.ver', 'regionales.crear', 'regionales.editar',
            'almacenes.ver', 'almacenes.crear', 'almacenes.editar',
            'clientes.ver', 'clientes.crear', 'clientes.editar', 'clientes.eliminar',
            'areas.ver', 'areas.crear', 'areas.editar', 'areas.eliminar',
            'cajas.ver', 'cajas.crear', 'cajas.editar', 'cajas.eliminar',
            'guias_salida.ver', 'guias_salida.crear',
            'guias_ingreso.ver', 'guias_ingreso.crear',
            'ubicaciones.ver', 'ubicaciones.crear', 'ubicaciones.editar',
            'stock.ver',
            'reportes.ver', 'reportes.exportar',
        ])->pluck('id_permiso')->toArray();
        $rolesCreados['Jefe de Almacén']->permisos()->syncWithPivotValues($permisosJefeAlmacen, $pivotData);

        // AUXILIAR DE ALMACÉN
        $permisosAuxiliar = Permiso::whereIn('codigo', [
            'dashboard.ver',
            'guias_ingreso.ver', 'guias_ingreso.crear',
            'ubicaciones.ver',
            'regionales.ver',
            'almacenes.ver',
        ])->pluck('id_permiso')->toArray();
        $rolesCreados['Auxiliar de Almacén']->permisos()->syncWithPivotValues($permisosAuxiliar, $pivotData);

        // JEFE DE ADMINISTRACIÓN
        $permisosJefeAdmin = Permiso::whereIn('codigo', [
            'dashboard.ver',
            'stock.ver',
            'reportes.ver', 'reportes.exportar',
            'regionales.ver',
            'almacenes.ver',
            'clientes.ver',
        ])->pluck('id_permiso')->toArray();
        $rolesCreados['Jefe de Administración']->permisos()->syncWithPivotValues($permisosJefeAdmin, $pivotData);

        $this->command->info('✅ Permisos asignados a roles');

        // ========================
        // 4. CREAR USUARIO ADMINISTRADOR
        // ========================
        User::updateOrCreate(
            ['email' => 'admin@infoactiva.com'],
            [
                'nombre' => 'Administrador del Sistema',
                'password' => 'admin123',  // ← El cast 'hashed' del modelo lo hashea
                'id_rol' => $rolesCreados['Administrador']->id_rol,
                'id_regional' => null,
                'id_almacen' => null,
                'estado' => 'activo',
                'fecha_creacion' => $ahora,
                'fecha_actualizacion' => $ahora,
            ]
        );

        $this->command->info('✅ Usuario administrador creado:');
        $this->command->info('   Email: admin@infoactiva.com');
        $this->command->info('   Password: admin123');
    }
}