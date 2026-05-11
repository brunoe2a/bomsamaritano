<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Ficha de Chamada</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', sans-serif; font-size: 10px; color: #333; padding: 12px; }
        .header { text-align: center; border-bottom: 3px solid #F5A623; padding-bottom: 8px; margin-bottom: 10px; }
        .header h1 { font-size: 16px; color: #1A1A1A; }
        .header h2 { font-size: 11px; color: #F5A623; margin-top: 2px; }
        .info { margin-bottom: 8px; font-size: 10px; }
        .info span { margin-right: 15px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 4px 5px; border: 1px solid #999; text-align: center; font-size: 9px; height: 22px; }
        th { background: #1A1A1A; color: #F5A623; font-weight: bold; }
        .num-col { width: 24px; }
        .name-col { text-align: left; min-width: 140px; white-space: nowrap; }
        .totais-col { width: 28px; }
        .footer { margin-top: 12px; font-size: 9px; color: #666; display: flex; justify-content: space-between; }
        .assinatura { margin-top: 30px; border-top: 1px solid #333; width: 280px; text-align: center; padding-top: 4px; font-size: 9px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Projeto Bom Samaritano</h1>
        <h2>Ficha de Chamada Mensal</h2>
    </div>

    @php
        $meses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
    @endphp

    <div class="info">
        <span><strong>Unidade:</strong> {{ $turma?->unidade?->nome ?? '-' }}</span>
        <span><strong>Turma:</strong> {{ $turma?->nome ?? '-' }}</span>
        <span><strong>Curso:</strong> {{ $turma?->curso?->nome ?? '-' }}</span>
        <span><strong>Período:</strong> {{ $meses[$mes] ?? $mes }}/{{ $ano }}</span>
        <br>
        <span><strong>Professores:</strong> {{ $turma?->professores->pluck('nome')->join(', ') ?: '-' }}</span>
        <span><strong>Voluntários:</strong> {{ $turma?->voluntarios->pluck('nome')->join(', ') ?: '-' }}</span>
    </div>

    @if(count($datas))
        <table>
            <thead>
                <tr>
                    <th class="num-col">#</th>
                    <th class="name-col">Aluno</th>
                    @foreach($datas as $d)
                        <th>{{ $d->format('d/m') }}</th>
                    @endforeach
                    <th class="totais-col">P</th>
                    <th class="totais-col">F</th>
                    <th class="totais-col">%</th>
                </tr>
            </thead>
            <tbody>
                @foreach($alunos as $i => $aluno)
                    <tr>
                        <td class="num-col">{{ $i + 1 }}</td>
                        <td class="name-col">{{ $aluno->nome }}</td>
                        @foreach($datas as $d)
                            <td></td>
                        @endforeach
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endforeach
                @if(!$alunos->count())
                    <tr><td colspan="{{ count($datas) + 5 }}">Nenhum aluno matriculado.</td></tr>
                @endif
            </tbody>
        </table>
    @else
        <p style="text-align: center; padding: 20px; color: #999;">A turma não possui dias de aula configurados.</p>
    @endif

    <div class="footer">
        <div class="assinatura">Assinatura do Professor</div>
        <div style="text-align: right;">Emitido em {{ now()->format('d/m/Y H:i') }}</div>
    </div>
</body>
</html>
