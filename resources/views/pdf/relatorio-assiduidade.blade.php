<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Assiduidade</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; padding: 18px; }
        .header { text-align: center; border-bottom: 3px solid #F5A623; padding-bottom: 10px; margin-bottom: 14px; }
        .header h1 { font-size: 18px; color: #1A1A1A; }
        .header h2 { font-size: 12px; color: #F5A623; margin-top: 2px; }
        .info { margin-bottom: 12px; font-size: 11px; }
        .info span { margin-right: 14px; display: inline-block; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { padding: 6px 8px; border: 1px solid #ccc; font-size: 10px; }
        th { background: #1A1A1A; color: #F5A623; text-align: left; }
        td.center, th.center { text-align: center; }
        .pct { font-weight: bold; padding: 2px 6px; border-radius: 4px; display: inline-block; }
        .pct-alto { background: #d1fae5; color: #047857; }
        .pct-medio { background: #fef3c7; color: #b45309; }
        .pct-baixo { background: #fee2e2; color: #b91c1c; }
        .row-detalhe { background: #fafafa; }
        .detalhe-grid { display: block; padding: 4px 0; }
        .det-item { display: inline-block; margin-right: 12px; margin-bottom: 3px; font-size: 9px; }
        .det-presente { color: #047857; }
        .det-falta { color: #b91c1c; }
        .empty { text-align: center; padding: 20px; color: #999; }
        .footer { margin-top: 18px; font-size: 9px; color: #999; text-align: center; border-top: 1px solid #ddd; padding-top: 8px; }
        .totais { margin-top: 14px; padding: 8px 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 4px; font-size: 10px; }
        .totais strong { color: #1A1A1A; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Projeto Bom Samaritano</h1>
        <h2>Relatório de Assiduidade</h2>
    </div>

    @php
        $dt = \Carbon\Carbon::createFromFormat('Y-m-d', $filtros['data_inicio'])->format('d/m/Y');
        $df = \Carbon\Carbon::createFromFormat('Y-m-d', $filtros['data_fim'])->format('d/m/Y');
        $tipoLabel = ['todos' => 'Todos', 'professor' => 'Professores', 'voluntario' => 'Voluntários'][$filtros['tipo']] ?? 'Todos';
    @endphp

    <div class="info">
        <span><strong>Período:</strong> {{ $dt }} a {{ $df }}</span>
        <span><strong>Tipo:</strong> {{ $tipoLabel }}</span>
        <span><strong>Unidade:</strong> {{ $unidadeNome ?? 'Todas' }}</span>
        <span><strong>Mín. Escalas:</strong> {{ $filtros['min_escalas'] }}</span>
    </div>

    @if(count($ranking))
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Tipo</th>
                    <th class="center">Escalas</th>
                    <th class="center">Presenças</th>
                    <th class="center">Faltas</th>
                    <th class="center">% Presença</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ranking as $item)
                    <tr>
                        <td>{{ $item['nome'] }}</td>
                        <td>{{ $item['tipo'] }}</td>
                        <td class="center">{{ $item['total_escalas'] }}</td>
                        <td class="center">{{ $item['presencas'] }}</td>
                        <td class="center">{{ $item['faltas'] }}</td>
                        <td class="center">
                            @php
                                $cls = $item['percentual'] >= 80 ? 'pct-alto' : ($item['percentual'] >= 50 ? 'pct-medio' : 'pct-baixo');
                            @endphp
                            <span class="pct {{ $cls }}">{{ $item['percentual'] }}%</span>
                        </td>
                    </tr>
                    <tr class="row-detalhe">
                        <td colspan="6">
                            <div class="detalhe-grid">
                                @foreach($item['detalhes'] as $d)
                                    @php
                                        $dataBr = \Carbon\Carbon::createFromFormat('Y-m-d', $d['data'])->format('d/m/Y');
                                    @endphp
                                    <span class="det-item {{ $d['presente'] ? 'det-presente' : 'det-falta' }}">
                                        {{ $d['presente'] ? '✓' : '✗' }} {{ $dataBr }}@if($d['unidade']) ({{ $d['unidade'] }})@endif@if(!$d['presente'] && $d['justificativa']) — {{ $d['justificativa'] }}@endif
                                    </span>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @php
            $totalEscalas = array_sum(array_column($ranking, 'total_escalas'));
            $totalPresencas = array_sum(array_column($ranking, 'presencas'));
            $totalFaltas = array_sum(array_column($ranking, 'faltas'));
            $pctGeral = $totalEscalas > 0 ? round(($totalPresencas / $totalEscalas) * 100, 1) : 0;
        @endphp
        <div class="totais">
            <strong>Totais:</strong>
            {{ count($ranking) }} pessoa(s) ·
            {{ $totalEscalas }} escala(s) ·
            {{ $totalPresencas }} presença(s) ·
            {{ $totalFaltas }} falta(s) ·
            {{ $pctGeral }}% de presença geral
        </div>
    @else
        <p class="empty">Nenhum dado para os filtros selecionados.</p>
    @endif

    <div class="footer">
        Projeto Bom Samaritano — Emitido em {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
