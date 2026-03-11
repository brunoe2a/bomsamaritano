<?php

use App\Http\Controllers\AlunoController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FinanceiroController;
use App\Http\Controllers\ProfessorController;
use App\Http\Controllers\TurmaController;
use App\Http\Controllers\VoluntarioController;
use App\Http\Controllers\AreaAtuacaoController;
use App\Http\Controllers\FinanceiroCategoriaController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::redirect('/', '/dashboard')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard — todos os perfis
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Alunos
    Route::middleware('permission:alunos.listar')->group(function () {
        Route::resource('alunos', AlunoController::class);
    });

    // Cursos
    Route::middleware('permission:cursos.listar')->group(function () {
        Route::resource('cursos', CursoController::class)->except(['show']);
    });

    // Turmas
    Route::middleware('permission:turmas.listar')->group(function () {
        Route::resource('turmas', TurmaController::class);
        Route::get('turmas/{turma}/chamada', [TurmaController::class, 'chamada'])->name('turmas.chamada')->middleware('permission:chamada.registrar');
        Route::post('turmas/{turma}/chamada', [TurmaController::class, 'registrarChamada'])->name('turmas.registrar-chamada')->middleware('permission:chamada.registrar');
        Route::post('turmas/{turma}/matricular', [TurmaController::class, 'matricular'])->name('turmas.matricular');
        Route::delete('turmas/{turma}/desmatricular/{matricula}', [TurmaController::class, 'desmatricular'])->name('turmas.desmatricular');
    });

    // Professores
    Route::middleware('permission:professores.listar')->group(function () {
        Route::resource('professores', ProfessorController::class)->parameters([
            'professores' => 'professor'
        ]);
    });

    // Voluntários e Áreas de Atuação
    Route::middleware('permission:voluntarios.listar')->group(function () {
        Route::resource('voluntarios', VoluntarioController::class);
        Route::resource('area-atuacao', AreaAtuacaoController::class)->except(['create', 'show', 'edit']);
    });

    // Financeiro
    Route::middleware('permission:financeiro.listar')->group(function () {
        Route::resource('financeiro', FinanceiroController::class)->except(['show']);
        Route::resource('financeiro-categorias', FinanceiroCategoriaController::class)->except(['create', 'show', 'edit'])->parameters([
            'financeiro-categorias' => 'financeiroCategoria'
        ]);
        Route::get('financeiro/doadores', [FinanceiroController::class, 'doadores'])->name('financeiro.doadores');
        Route::post('financeiro/doadores', [FinanceiroController::class, 'storeDoador'])->name('financeiro.store-doador');
        Route::delete('financeiro/doadores/{doador}', [FinanceiroController::class, 'destroyDoador'])->name('financeiro.destroy-doador');
    });

    // Usuários (Somente Admins)
    Route::middleware('role:admin')->group(function () {
        Route::resource('usuarios', \App\Http\Controllers\UserController::class);
    });

    // Exportações
    Route::prefix('export')->name('export.')->middleware('permission:exportar.pdf|exportar.excel')->group(function () {
        Route::get('alunos/excel', [ExportController::class, 'alunosExcel'])->name('alunos.excel');
        Route::get('financeiro/excel', [ExportController::class, 'financeiroExcel'])->name('financeiro.excel');
        Route::get('alunos/{aluno}/ficha', [ExportController::class, 'fichaAluno'])->name('alunos.ficha');
        Route::get('frequencia', [ExportController::class, 'relatorioFrequencia'])->name('frequencia');
        Route::get('financeiro/pdf', [ExportController::class, 'relatorioFinanceiro'])->name('financeiro.pdf');
    });
});

require __DIR__.'/settings.php';
