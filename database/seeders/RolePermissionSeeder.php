<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions
        $permissions = [
            // Alunos
            'alunos.listar', 'alunos.ver', 'alunos.criar', 'alunos.editar', 'alunos.excluir',
            // Cursos
            'cursos.listar', 'cursos.criar', 'cursos.editar', 'cursos.excluir',
            // Turmas
            'turmas.listar', 'turmas.ver', 'turmas.criar', 'turmas.editar', 'turmas.excluir',
            // Chamada
            'chamada.registrar',
            // Professores
            'professores.listar', 'professores.ver', 'professores.criar', 'professores.editar', 'professores.excluir',
            // Voluntários
            'voluntarios.listar', 'voluntarios.ver', 'voluntarios.criar', 'voluntarios.editar', 'voluntarios.excluir',
            // Financeiro
            'financeiro.listar', 'financeiro.criar', 'financeiro.editar', 'financeiro.excluir',
            'doadores.listar', 'doadores.criar', 'doadores.excluir',
            // Exportações
            'exportar.pdf', 'exportar.excel',
            // Dashboard
            'dashboard.ver',
            // Unidades
            'unidades.listar', 'unidades.ver', 'unidades.criar', 'unidades.editar', 'unidades.excluir',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Roles
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        $coordenador = Role::firstOrCreate(['name' => 'coordenador']);
        $coordenador->givePermissionTo([
            'dashboard.ver',
            'alunos.listar', 'alunos.ver', 'alunos.criar', 'alunos.editar', 'alunos.excluir',
            'cursos.listar', 'cursos.criar', 'cursos.editar', 'cursos.excluir',
            'turmas.listar', 'turmas.ver', 'turmas.criar', 'turmas.editar', 'turmas.excluir',
            'chamada.registrar',
            'professores.listar', 'professores.ver', 'professores.criar', 'professores.editar',
            'voluntarios.listar', 'voluntarios.ver', 'voluntarios.criar', 'voluntarios.editar',
            'unidades.listar', 'unidades.ver', 'unidades.criar', 'unidades.editar', 'unidades.excluir',
            'exportar.pdf', 'exportar.excel',
        ]);

        $professor = Role::firstOrCreate(['name' => 'professor']);
        $professor->givePermissionTo([
            'dashboard.ver',
            'alunos.listar', 'alunos.ver',
            'turmas.listar', 'turmas.ver',
            'chamada.registrar',
            'exportar.pdf',
        ]);

        $financeiro = Role::firstOrCreate(['name' => 'financeiro']);
        $financeiro->givePermissionTo([
            'dashboard.ver',
            'financeiro.listar', 'financeiro.criar', 'financeiro.editar', 'financeiro.excluir',
            'doadores.listar', 'doadores.criar', 'doadores.excluir',
            'exportar.pdf', 'exportar.excel',
        ]);
    }
}
