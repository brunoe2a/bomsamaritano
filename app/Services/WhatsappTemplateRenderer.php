<?php

namespace App\Services;

use App\Models\Aluno;
use App\Models\Responsavel;
use Illuminate\Support\Carbon;

class WhatsappTemplateRenderer
{
    public function render(string $conteudo, ?Aluno $aluno = null, ?Responsavel $responsavel = null, array $extra = []): string
    {
        $turma = null;
        $curso = null;
        if ($aluno) {
            $matricula = $aluno->matriculas()->with('turma.curso')->latest()->first();
            $turma = $matricula?->turma?->nome;
            $curso = $matricula?->turma?->curso?->nome;
        }

        $vars = array_merge([
            'nome_aluno' => $aluno?->nome ?? '',
            'nome_responsavel' => $responsavel?->nome ?? $aluno?->responsavel?->nome ?? '',
            'turma' => $turma ?? '',
            'curso' => $curso ?? '',
            'data' => Carbon::now()->format('d/m/Y'),
            'hora' => Carbon::now()->format('H:i'),
            'unidade' => '',
        ], $extra);

        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', function ($m) use ($vars) {
            return $vars[$m[1]] ?? $m[0];
        }, $conteudo);
    }
}
