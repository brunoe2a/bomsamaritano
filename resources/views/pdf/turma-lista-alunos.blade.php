<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Lista de Alunos — {{ $turma->nome }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; padding: 20px; }
        .header { text-align: center; border-bottom: 3px solid #F5A623; padding-bottom: 12px; margin-bottom: 16px; }
        .header h1 { font-size: 20px; color: #1A1A1A; }
        .header h2 { font-size: 13px; color: #F5A623; margin-top: 4px; }
        .info-box { background: #fafafa; border: 1px solid #e5e7eb; border-radius: 4px; padding: 10px 14px; margin-bottom: 14px; font-size: 11px; }
        .info-box .linha { margin-bottom: 4px; }
        .info-box strong { color: #1A1A1A; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { padding: 6px 8px; border: 1px solid #ccc; font-size: 10px; vertical-align: middle; }
        th { background: #1A1A1A; color: #F5A623; text-align: left; }
        td.center, th.center { text-align: center; }
        td.idx { width: 28px; text-align: center; color: #666; }
        .empty { text-align: center; padding: 24px; color: #999; }
        .assinatura { margin-top: 40px; text-align: center; font-size: 11px; }
        .assinatura .linha-assinatura { border-top: 1px solid #333; margin: 30px auto 6px auto; width: 60%; }
        .footer { margin-top: 18px; font-size: 9px; color: #999; text-align: center; border-top: 1px solid #ddd; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Projeto Bom Samaritano</h1>
        <h2>Lista de Alunos Matriculados</h2>
    </div>

    @php
        $periodoLabels = ['segunda_sexta' => 'Segunda a Sexta', 'sabados' => 'Aos Sábados'];
        $diasLabel = collect($turma->dias_semana ?? [])->map(fn ($d) => ucfirst($d))->join(', ');
        $professoresLabel = $turma->professores->pluck('nome')->join(', ');
        $voluntariosLabel = $turma->voluntarios->pluck('nome')->join(', ');
    @endphp

    <div class="info-box">
        <div class="linha"><strong>Turma:</strong> {{ $turma->nome }}</div>
        <div class="linha"><strong>Curso:</strong> {{ $turma->curso?->nome ?? '-' }}</div>
        <div class="linha"><strong>Unidade:</strong> {{ $turma->unidade?->nome ?? '-' }}</div>
        <div class="linha">
            <strong>Período:</strong> {{ $periodoLabels[$turma->periodo] ?? $turma->periodo }}
            @if($diasLabel) · <strong>Dias:</strong> {{ $diasLabel }} @endif
            @if($turma->horario_inicio || $turma->horario_fim)
                · <strong>Horário:</strong> {{ $turma->horario_inicio }} - {{ $turma->horario_fim }}
            @endif
        </div>
        <div class="linha"><strong>Ano Letivo:</strong> {{ $turma->ano_letivo }}</div>
        @if($professoresLabel)
            <div class="linha"><strong>Professor(es):</strong> {{ $professoresLabel }}</div>
        @endif
        @if($voluntariosLabel)
            <div class="linha"><strong>Voluntário(s):</strong> {{ $voluntariosLabel }}</div>
        @endif
        <div class="linha"><strong>Total de Alunos:</strong> {{ $alunos->count() }} / {{ $turma->capacidade_maxima }}</div>
    </div>

    @if($alunos->count())
        <table>
            <thead>
                <tr>
                    <th class="center">#</th>
                    <th>Nome do Aluno</th>
                    <th>Ano Escolar</th>
                    <th class="center">Idade</th>
                    <th>Responsável</th>
                    <th>Contato</th>
                </tr>
            </thead>
            <tbody>
                @foreach($alunos as $i => $aluno)
                    @php
                        $idade = $aluno->data_nascimento ? \Carbon\Carbon::parse($aluno->data_nascimento)->age : null;
                        $contato = $aluno->responsavel?->whatsapp ?: $aluno->responsavel?->telefone;
                    @endphp
                    <tr>
                        <td class="idx">{{ $i + 1 }}</td>
                        <td>{{ $aluno->nome }}</td>
                        <td>{{ $aluno->ano_escolar ?? '-' }}</td>
                        <td class="center">{{ $idade !== null ? $idade : '-' }}</td>
                        <td>{{ $aluno->responsavel?->nome ?? '-' }}</td>
                        <td>{{ $contato ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="assinatura">
            <div class="linha-assinatura"></div>
            {{ $professoresLabel ?: 'Professor(a) Responsável' }}
        </div>
    @else
        <p class="empty">Nenhum aluno matriculado nesta turma.</p>
    @endif

    <div class="footer">
        Projeto Bom Samaritano — Emitido em {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
