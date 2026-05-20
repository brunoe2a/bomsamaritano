<?php

use App\Http\Controllers\AlunoController;
use App\Http\Controllers\ResponsavelController;
use App\Http\Controllers\SaudeController;
use App\Http\Controllers\SaudeProgramaController;
use App\Http\Controllers\SaudeAreaController;
use App\Http\Controllers\SaudeConvocacaoController;
use App\Http\Controllers\SaudeAtendimentoController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpedienteController;
use App\Http\Controllers\ExpedienteRelatorioController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FinanceiroController;
use App\Http\Controllers\ProfessorController;
use App\Http\Controllers\TurmaController;
use App\Http\Controllers\VoluntarioController;
use App\Http\Controllers\AreaAtuacaoController;
use App\Http\Controllers\FinanceiroCategoriaController;
use App\Http\Controllers\UnidadeController;
use App\Http\Controllers\Whatsapp\WhatsappInstanceController;
use App\Http\Controllers\Whatsapp\WhatsappNotificationController;
use App\Http\Controllers\Whatsapp\WhatsappTemplateController;
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

    // Responsáveis
    Route::middleware('permission:responsaveis.listar')->group(function () {
        Route::get('responsaveis/search', [ResponsavelController::class, 'search'])->name('responsaveis.search');
        Route::resource('responsaveis', ResponsavelController::class)->parameters([
            'responsaveis' => 'responsavel'
        ]);
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
        Route::get('turmas/{turma}/ficha-chamada-pdf', [TurmaController::class, 'fichaChamadaPdf'])->name('turmas.ficha-chamada-pdf')->middleware('permission:exportar.pdf');
        Route::get('turmas/{turma}/lista-alunos-pdf', [TurmaController::class, 'listaAlunosPdf'])->name('turmas.lista-alunos-pdf')->middleware('permission:exportar.pdf');
    });

    // Expediente (escala de professores e voluntários)
    Route::middleware('permission:expedientes.listar')->group(function () {
        Route::get('expedientes', [ExpedienteController::class, 'index'])->name('expedientes.index');
        Route::get('expedientes/relatorio', [ExpedienteRelatorioController::class, 'index'])->name('expedientes.relatorio');
        Route::get('expedientes/relatorio/pdf', [ExpedienteRelatorioController::class, 'pdf'])->name('expedientes.relatorio-pdf')->middleware('permission:exportar.pdf');
        Route::get('expedientes/{expediente}', [ExpedienteController::class, 'show'])->name('expedientes.show');
        Route::get('expedientes/{expediente}/escala-pdf', [ExpedienteController::class, 'escalaPdf'])->name('expedientes.escala-pdf');
    });

    Route::middleware('permission:expedientes.criar')->group(function () {
        Route::get('expedientes-novo', [ExpedienteController::class, 'create'])->name('expedientes.create');
        Route::post('expedientes', [ExpedienteController::class, 'store'])->name('expedientes.store');
    });

    Route::middleware('permission:expedientes.editar')->group(function () {
        Route::put('expedientes/{expediente}', [ExpedienteController::class, 'update'])->name('expedientes.update');
        Route::patch('expedientes/{expediente}', [ExpedienteController::class, 'update']);
        Route::delete('expedientes/{expediente}/escalado/{escalado}', [ExpedienteController::class, 'removerEscalado'])->name('expedientes.remover-escalado');
    });

    Route::middleware('permission:expedientes.excluir')->group(function () {
        Route::delete('expedientes/{expediente}', [ExpedienteController::class, 'destroy'])->name('expedientes.destroy');
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
        Route::get('financeiro/dashboard', [FinanceiroController::class, 'dashboard'])->name('financeiro.dashboard');
        Route::get('financeiro/doadores', [FinanceiroController::class, 'doadores'])->name('financeiro.doadores');
        Route::post('financeiro/doadores', [FinanceiroController::class, 'storeDoador'])->name('financeiro.store-doador');
        Route::delete('financeiro/doadores/{doador}', [FinanceiroController::class, 'destroyDoador'])->name('financeiro.destroy-doador');

        Route::resource('financeiro', FinanceiroController::class);
        Route::resource('financeiro-categorias', FinanceiroCategoriaController::class)->except(['create', 'show', 'edit'])->parameters([
            'financeiro-categorias' => 'financeiroCategoria'
        ]);
    });

    // Unidades
    Route::middleware('permission:unidades.listar')->group(function () {
        Route::resource('unidades', UnidadeController::class);
    });

    // Núcleo de Saúde
    Route::middleware('permission:saude.listar')->prefix('saude')->name('saude.')->group(function () {
        Route::get('/', [SaudeController::class, 'index'])->name('index');

        Route::resource('programas', SaudeProgramaController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['programas' => 'programa']);

        Route::resource('areas', SaudeAreaController::class)
            ->only(['store', 'update', 'destroy'])
            ->parameters(['areas' => 'area']);

        Route::resource('convocacoes', SaudeConvocacaoController::class)
            ->parameters(['convocacoes' => 'convocacao']);
        Route::post('convocacoes/{convocacao}/presenca', [SaudeConvocacaoController::class, 'registrarPresenca'])
            ->name('convocacoes.presenca')
            ->middleware('permission:saude.editar');

        Route::resource('atendimentos', SaudeAtendimentoController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['atendimentos' => 'atendimento']);
    });

    // WhatsApp (Evolution API)
    Route::middleware('permission:whatsapp.listar')->prefix('whatsapp')->name('whatsapp.')->group(function () {
        // Instâncias
        Route::get('instancias', [WhatsappInstanceController::class, 'index'])->name('instancias.index');
        Route::post('instancias', [WhatsappInstanceController::class, 'store'])->middleware('permission:whatsapp.criar')->name('instancias.store');
        Route::get('instancias/{instancia}/qrcode', [WhatsappInstanceController::class, 'qrCode'])->name('instancias.qrcode');
        Route::get('instancias/{instancia}/status', [WhatsappInstanceController::class, 'status'])->name('instancias.status');
        Route::post('instancias/{instancia}/desconectar', [WhatsappInstanceController::class, 'disconnect'])->middleware('permission:whatsapp.editar')->name('instancias.disconnect');
        Route::delete('instancias/{instancia}', [WhatsappInstanceController::class, 'destroy'])->middleware('permission:whatsapp.excluir')->name('instancias.destroy');

        // Templates de mensagem
        Route::get('templates', [WhatsappTemplateController::class, 'index'])->name('templates.index');
        Route::post('templates', [WhatsappTemplateController::class, 'store'])->middleware('permission:whatsapp.criar')->name('templates.store');
        Route::put('templates/{template}', [WhatsappTemplateController::class, 'update'])->middleware('permission:whatsapp.editar')->name('templates.update');
        Route::delete('templates/{template}', [WhatsappTemplateController::class, 'destroy'])->middleware('permission:whatsapp.excluir')->name('templates.destroy');

        // Notificações (envio em massa + monitoramento)
        Route::get('notificacoes', [WhatsappNotificationController::class, 'index'])->name('notificacoes.index');
        Route::post('notificacoes/dispatch', [WhatsappNotificationController::class, 'dispatch'])->middleware('permission:whatsapp.enviar')->name('notificacoes.dispatch');
        Route::post('notificacoes/{notificacao}/reenviar', [WhatsappNotificationController::class, 'reenviar'])->middleware('permission:whatsapp.enviar')->name('notificacoes.reenviar');
        Route::delete('notificacoes/{notificacao}', [WhatsappNotificationController::class, 'destroy'])->middleware('permission:whatsapp.excluir')->name('notificacoes.destroy');
        Route::get('alunos-search', [WhatsappNotificationController::class, 'alunos'])->name('alunos.search');
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
        Route::get('saude/convocacoes/{convocacao}/pdf', [ExportController::class, 'saudeConvocacao'])->name('saude.convocacao');
    });
});

require __DIR__.'/settings.php';
