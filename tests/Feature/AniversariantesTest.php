<?php

use App\Models\Aluno;
use App\Models\Responsavel;
use Illuminate\Support\Carbon;

function aluno(string $nome, string $nascimento): Aluno
{
    return Aluno::create([
        'nome' => $nome,
        'data_nascimento' => $nascimento,
        'responsavel_id' => Responsavel::firstOrCreate(['nome' => 'Responsável'])->id,
    ]);
}

test('aniversariantes da semana ignora o ano de nascimento', function () {
    Carbon::setTestNow('2026-08-19'); // quarta-feira: semana de 17/08 a 23/08

    aluno('Dentro da semana', '2014-08-20');
    aluno('Fora da semana', '2014-09-20');

    $nomes = Aluno::aniversariantesSemana()->pluck('nome');

    expect($nomes)->toContain('Dentro da semana')
        ->and($nomes)->not->toContain('Fora da semana');
});

test('aniversariantes da semana funciona na virada do ano', function () {
    Carbon::setTestNow('2026-12-31'); // semana de 28/12 a 03/01

    aluno('Fim de dezembro', '2013-12-30');
    aluno('Início de janeiro', '2013-01-02');
    aluno('Meio de janeiro', '2013-01-20');

    $nomes = Aluno::aniversariantesSemana()->pluck('nome');

    expect($nomes)->toContain('Fim de dezembro')
        ->and($nomes)->toContain('Início de janeiro')
        ->and($nomes)->not->toContain('Meio de janeiro');
});

test('aniversariantes do mês filtra pelo mês informado', function () {
    aluno('Aniversário em março', '2014-03-05');
    aluno('Aniversário em julho', '2014-07-05');

    $nomes = Aluno::aniversariantesMes(3)->pluck('nome');

    expect($nomes)->toContain('Aniversário em março')
        ->and($nomes)->not->toContain('Aniversário em julho');
});
