@php
    $tipo = $tipo ?? null;
    $meses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
    $titulo = match ($tipo) {
        'entrada' => 'Relatório de Entradas',
        'saida' => 'Relatório de Saídas',
        default => 'Relatório Financeiro',
    };
    $totalFiltrado = $tipo === 'entrada' ? $totais['entradas'] : ($tipo === 'saida' ? $totais['saidas'] : null);
    $colunas = $tipo ? 5 : 6;
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #333; padding: 20px; }
        .header { text-align: center; border-bottom: 3px solid #F5A623; padding-bottom: 12px; margin-bottom: 20px; }
        .header h1 { font-size: 20px; color: #1A1A1A; }
        .header h2 { font-size: 13px; color: #F5A623; margin-top: 3px; }
        .resumo { display: table; width: 100%; margin-bottom: 20px; }
        .resumo-item { display: table-cell; text-align: center; padding: 10px; }
        .resumo-item .label { font-size: 10px; color: #888; text-transform: uppercase; }
        .resumo-item .valor { font-size: 18px; font-weight: bold; margin-top: 4px; }
        .entrada { color: #27ae60; }
        .saida { color: #e74c3c; }
        .neutro { color: #1A1A1A; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { padding: 6px 10px; border: 1px solid #ddd; text-align: left; font-size: 11px; }
        th { background: #1A1A1A; color: #F5A623; font-weight: bold; }
        tfoot td { background: #f5f5f5; font-weight: bold; }
        .text-right { text-align: right; }
        .footer { text-align: center; margin-top: 20px; border-top: 1px solid #ddd; padding-top: 10px; font-size: 10px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Projeto Bom Samaritano</h1>
        <h2>{{ $titulo }} — {{ $meses[$mes] }}/{{ $ano }}</h2>
    </div>

    <div class="resumo">
        @if ($tipo)
            <div class="resumo-item" style="width: 50%;">
                <div class="label">Total de {{ $tipo === 'entrada' ? 'Entradas' : 'Saídas' }}</div>
                <div class="valor {{ $tipo === 'entrada' ? 'entrada' : 'saida' }}">R$ {{ number_format($totalFiltrado, 2, ',', '.') }}</div>
            </div>
            <div class="resumo-item" style="width: 50%;">
                <div class="label">Lançamentos</div>
                <div class="valor neutro">{{ $lancamentos->count() }}</div>
            </div>
        @else
            <div class="resumo-item" style="width: 33.33%;">
                <div class="label">Total Entradas</div>
                <div class="valor entrada">R$ {{ number_format($totais['entradas'], 2, ',', '.') }}</div>
            </div>
            <div class="resumo-item" style="width: 33.33%;">
                <div class="label">Total Saídas</div>
                <div class="valor saida">R$ {{ number_format($totais['saidas'], 2, ',', '.') }}</div>
            </div>
            <div class="resumo-item" style="width: 33.33%;">
                <div class="label">Saldo</div>
                <div class="valor {{ $totais['saldo'] >= 0 ? 'entrada' : 'saida' }}">R$ {{ number_format($totais['saldo'], 2, ',', '.') }}</div>
            </div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Data</th>
                @unless ($tipo)
                    <th>Tipo</th>
                @endunless
                <th>Categoria</th>
                <th>Descrição</th>
                <th>Doador</th>
                <th class="text-right">Valor</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lancamentos as $l)
            <tr>
                <td>{{ $l->data->format('d/m/Y') }}</td>
                @unless ($tipo)
                <td>
                    <span class="{{ $l->tipo === 'entrada' ? 'entrada' : 'saida' }}" style="font-weight:bold;">
                        {{ $l->tipo === 'entrada' ? '↑ Entrada' : '↓ Saída' }}
                    </span>
                </td>
                @endunless
                <td>{{ $l->categoria?->nome ?? '-' }}</td>
                <td>{{ $l->descricao }}</td>
                <td>{{ $l->doador?->nome ?? '-' }}</td>
                <td class="text-right {{ $l->tipo === 'entrada' ? 'entrada' : 'saida' }}" style="font-weight:bold;">
                    {{ $l->tipo === 'entrada' ? '+' : '-' }} R$ {{ number_format((float)$l->valor, 2, ',', '.') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ $colunas }}" style="text-align: center; color: #999; padding: 20px;">Nenhum lançamento neste período.</td>
            </tr>
            @endforelse
        </tbody>
        @if ($tipo && $lancamentos->isNotEmpty())
        <tfoot>
            <tr>
                <td colspan="{{ $colunas - 1 }}" class="text-right">Total</td>
                <td class="text-right {{ $tipo === 'entrada' ? 'entrada' : 'saida' }}">R$ {{ number_format($totalFiltrado, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">
        Projeto Bom Samaritano — Emitido em {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
