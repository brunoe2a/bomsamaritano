<?php

namespace App\Exports;

use App\Models\Aluno;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AlunosExport implements FromQuery, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    protected ?string $status;
    protected ?int $cursoId;

    public function __construct(?string $status = null, ?int $cursoId = null)
    {
        $this->status = $status;
        $this->cursoId = $cursoId;
    }

    public function query()
    {
        $query = Aluno::with(['responsavel', 'matriculas.turma.curso']);

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->cursoId) {
            $query->whereHas('matriculas.turma.curso', function ($q) {
                $q->where('cursos.id', $this->cursoId);
            });
        }

        return $query->orderBy('nome');
    }

    public function headings(): array
    {
        return [
            'ID', 'Nome', 'Data Nasc.', 'Idade', 'Ano Escolar', 'Status',
            'Responsável', 'Telefone', 'WhatsApp', 'CPF Resp.',
            'Renda Familiar', 'Cursos Matriculados',
        ];
    }

    public function map($aluno): array
    {
        $cursos = $aluno->matriculas
            ->where('status', 'ativa')
            ->map(fn($m) => $m->turma?->curso?->nome)
            ->filter()
            ->unique()
            ->implode(', ');

        $rendaLabels = [
            'menos_1_salario' => 'Menos de 1 salário',
            'ate_2_salarios' => 'Até 2 salários',
            'acima_3_salarios' => 'Acima de 3 salários',
        ];

        return [
            $aluno->id,
            $aluno->nome,
            $aluno->data_nascimento?->format('d/m/Y'),
            $aluno->idade,
            $aluno->ano_escolar,
            ucfirst($aluno->status),
            $aluno->responsavel?->nome,
            $aluno->responsavel?->telefone,
            $aluno->responsavel?->whatsapp,
            $aluno->responsavel?->cpf,
            $rendaLabels[$aluno->responsavel?->renda_familiar] ?? '-',
            $cursos ?: '-',
        ];
    }

    public function title(): string
    {
        return 'Alunos';
    }
}
