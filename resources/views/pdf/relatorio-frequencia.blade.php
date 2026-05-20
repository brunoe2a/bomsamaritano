<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Frequência</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #333; padding: 15px; }
        .header { text-align: center; border-bottom: 3px solid #F5A623; padding-bottom: 10px; margin-bottom: 15px; }
        .header h1 { font-size: 18px; color: #1A1A1A; }
        .header h2 { font-size: 12px; color: #F5A623; margin-top: 2px; }
        .info { margin-bottom: 10px; font-size: 11px; }
        .info span { margin-right: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 4px 6px; border: 1px solid #ccc; text-align: center; font-size: 9px; }
        th { background: #1A1A1A; color: #F5A623; font-weight: bold; }
        .name-col { text-align: left; min-width: 120px; white-space: nowrap; }
        .presente { background: #d4edda; color: #155724; font-weight: bold; }
        .ausente { background: #f8d7da; color: #721c24; font-weight: bold; }
        .footer { text-align: center; margin-top: 15px; font-size: 9px; color: #999; border-top: 1px solid #ddd; padding-top: 8px; }
        .stats { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Projeto Bom Samaritano</h1>
        <h2>Relatório de Frequência</h2>
    </div>

    <div class="info">
        @php
            $meses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
        @endphp
        <span><strong>Turma:</strong> {{ $turma?->nome ?? '-' }}</span>
        <span><strong>Curso:</strong> {{ $turma?->curso?->nome ?? '-' }}</span>
        <span><strong>Período:</strong> {{ $meses[$mes] ?? $mes }}/{{ $ano }}</span>
        <br>
        <span><strong>Professores:</strong> {{ $turma?->professores->pluck('nome')->join(', ') ?: '-' }}</span>
        <span><strong>Voluntários:</strong> {{ $turma?->voluntarios->pluck('nome')->join(', ') ?: '-' }}</span>
    </div>

    @if($chamadas->count())
        @php
            // Extrair todos os alunos únicos
            $todosAlunos = collect();
            foreach ($chamadas as $chamada) {
                foreach ($chamada->presencas as $p) {
                    if ($p->aluno && !$todosAlunos->has($p->aluno_id)) {
                        $todosAlunos->put($p->aluno_id, $p->aluno);
                    }
                }
            }
            $todosAlunos = $todosAlunos->sortBy('nome');
        @endphp

        <table>
            <thead>
                <tr>
                    <th class="name-col">Aluno</th>
                    @foreach($chamadas as $chamada)
                        <th>{{ $chamada->data->format('d') }}</th>
                    @endforeach
                    <th>P</th>
                    <th>F</th>
                    <th>%</th>
                </tr>
            </thead>
            <tbody>
                @foreach($todosAlunos as $aluno)
                    @php
                        $presencas = 0;
                        $faltas = 0;
                    @endphp
                    <tr>
                        <td class="name-col">{{ $aluno->nome }}</td>
                        @foreach($chamadas as $chamada)
                            @php
                                $registro = $chamada->presencas->firstWhere('aluno_id', $aluno->id);
                            @endphp
                            @if($registro)
                                @if($registro->presente)
                                    <td class="presente">P</td>
                                    @php $presencas++; @endphp
                                @else
                                    <td class="ausente">F</td>
                                    @php $faltas++; @endphp
                                @endif
                            @else
                                <td>-</td>
                            @endif
                        @endforeach
                        <td class="stats">{{ $presencas }}</td>
                        <td class="stats">{{ $faltas }}</td>
                        <td class="stats">{{ ($presencas + $faltas) > 0 ? round(($presencas / ($presencas + $faltas)) * 100) : 0 }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="text-align: center; padding: 20px; color: #999;">Nenhuma chamada registrada neste período.</p>
    @endif

    <div class="footer">
        Projeto Bom Samaritano — Emitido em {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
