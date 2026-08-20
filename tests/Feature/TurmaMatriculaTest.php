<?php

use App\Models\Aluno;
use App\Models\Curso;
use App\Models\Matricula;
use App\Models\Responsavel;
use App\Models\Turma;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    Permission::findOrCreate('turmas.listar');
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $usuario = User::factory()->create();
    $usuario->givePermissionTo('turmas.listar');
    $this->actingAs($usuario);

    $curso = Curso::create(['nome' => 'Reforço Escolar']);

    $this->turma = Turma::create([
        'nome' => 'NÚMEROS',
        'curso_id' => $curso->id,
        'capacidade_maxima' => 2,
        'ano_letivo' => (int) date('Y'),
        'status' => 'em_andamento',
    ]);

    $responsavel = Responsavel::create(['nome' => 'Maria da Silva']);

    $this->aluno = Aluno::create([
        'nome' => 'João Pedro',
        'data_nascimento' => '2015-01-10',
        'responsavel_id' => $responsavel->id,
    ]);
});

function matricular(Turma $turma, Aluno $aluno)
{
    return test()->post(route('turmas.matricular', $turma), ['aluno_id' => $aluno->id]);
}

function criarMatricula(Turma $turma, Aluno $aluno, string $status): Matricula
{
    return Matricula::create([
        'aluno_id' => $aluno->id,
        'turma_id' => $turma->id,
        'tipo' => 'nova',
        'ano_letivo' => (int) date('Y'),
        'status' => $status,
        'data_matricula' => now()->subMonths(3),
    ]);
}

test('cria matrícula nova quando o aluno nunca participou da turma', function () {
    matricular($this->turma, $this->aluno)->assertSessionHas('success');

    $matricula = Matricula::sole();

    expect($matricula->status)->toBe('ativa')
        ->and($matricula->tipo)->toBe('nova');
});

test('reativa a matrícula trancada em vez de criar uma duplicada', function () {
    $anterior = criarMatricula($this->turma, $this->aluno, 'trancada');

    matricular($this->turma, $this->aluno)
        ->assertSessionHas('success', 'Matrícula de "João Pedro" reativada com sucesso!');

    expect(Matricula::count())->toBe(1);

    $anterior->refresh();

    expect($anterior->status)->toBe('ativa')
        ->and($anterior->tipo)->toBe('rematricula')
        ->and($anterior->data_matricula->isToday())->toBeTrue();
});

test('reativa também matrículas canceladas e concluídas', function (string $status) {
    $anterior = criarMatricula($this->turma, $this->aluno, $status);

    matricular($this->turma, $this->aluno)->assertSessionHas('success');

    expect(Matricula::count())->toBe(1)
        ->and($anterior->refresh()->status)->toBe('ativa');
})->with(['cancelada', 'concluida']);

test('bloqueia quando o aluno já está com matrícula ativa', function () {
    criarMatricula($this->turma, $this->aluno, 'ativa');

    matricular($this->turma, $this->aluno)
        ->assertSessionHas('error', 'Este aluno já está matriculado nesta turma.');

    expect(Matricula::count())->toBe(1);
});

test('não reativa quando a turma já atingiu a capacidade máxima', function () {
    $anterior = criarMatricula($this->turma, $this->aluno, 'trancada');

    // Preenche as 2 vagas da turma com outros alunos
    foreach (['Ana', 'Bruno'] as $nome) {
        $outro = Aluno::create([
            'nome' => $nome,
            'data_nascimento' => '2015-05-05',
            'responsavel_id' => $this->aluno->responsavel_id,
        ]);
        criarMatricula($this->turma, $outro, 'ativa');
    }

    matricular($this->turma, $this->aluno)
        ->assertSessionHas('error', 'A turma está lotada. Capacidade máxima atingida.');

    expect($anterior->refresh()->status)->toBe('trancada');
});

test('matrícula de ano letivo anterior não impede uma matrícula nova neste ano', function () {
    Matricula::create([
        'aluno_id' => $this->aluno->id,
        'turma_id' => $this->turma->id,
        'tipo' => 'nova',
        'ano_letivo' => (int) date('Y') - 1,
        'status' => 'concluida',
        'data_matricula' => now()->subYear(),
    ]);

    matricular($this->turma, $this->aluno)->assertSessionHas('success');

    expect(Matricula::count())->toBe(2)
        ->and(Matricula::where('ano_letivo', (int) date('Y'))->sole()->tipo)->toBe('nova');
});
