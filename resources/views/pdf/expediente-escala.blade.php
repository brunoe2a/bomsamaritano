<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Escala do Expediente</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; padding: 18px; }
        .header { text-align: center; border-bottom: 3px solid #F5A623; padding-bottom: 10px; margin-bottom: 14px; }
        .header h1 { font-size: 18px; color: #1A1A1A; }
        .header h2 { font-size: 12px; color: #F5A623; margin-top: 2px; }
        .info { margin-bottom: 12px; font-size: 11px; }
        .info span { margin-right: 18px; }
        h3.secao { font-size: 12px; color: #F5A623; margin: 14px 0 6px 0; border-bottom: 1px solid #ddd; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 6px 8px; border: 1px solid #ccc; font-size: 10px; }
        th { background: #1A1A1A; color: #F5A623; text-align: left; }
        .check-col { width: 60px; text-align: center; }
        .obs-col { min-width: 150px; }
        .footer { margin-top: 18px; font-size: 9px; color: #999; text-align: center; border-top: 1px solid #ddd; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Projeto Bom Samaritano</h1>
        <h2>Escala de Expediente</h2>
    </div>

    <div class="info">
        <span><strong>Unidade:</strong> {{ $expediente->unidade?->nome ?? '-' }}</span>
        <span><strong>Data:</strong> {{ $expediente->data->format('d/m/Y') }}</span>
        @if($expediente->descricao)
            <span><strong>Descrição:</strong> {{ $expediente->descricao }}</span>
        @endif
    </div>

    @php
        $professores = $expediente->escalados->where('escalavel_type', \App\Models\Professor::class)->sortBy(fn ($e) => $e->escalavel?->nome);
        $voluntarios = $expediente->escalados->where('escalavel_type', \App\Models\Voluntario::class)->sortBy(fn ($e) => $e->escalavel?->nome);
    @endphp

    @if($professores->count())
        <h3 class="secao">Professores</h3>
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th class="check-col">Presente</th>
                    <th class="obs-col">Justificativa</th>
                </tr>
            </thead>
            <tbody>
                @foreach($professores as $e)
                    <tr>
                        <td>{{ $e->escalavel?->nome ?? '(removido)' }}</td>
                        <td class="check-col">{{ $e->presente ? '✓' : 'F' }}</td>
                        <td class="obs-col">{{ $e->justificativa }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($voluntarios->count())
        <h3 class="secao">Voluntários</h3>
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th class="check-col">Presente</th>
                    <th class="obs-col">Justificativa</th>
                </tr>
            </thead>
            <tbody>
                @foreach($voluntarios as $e)
                    <tr>
                        <td>{{ $e->escalavel?->nome ?? '(removido)' }}</td>
                        <td class="check-col">{{ $e->presente ? '✓' : 'F' }}</td>
                        <td class="obs-col">{{ $e->justificativa }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if(!$professores->count() && !$voluntarios->count())
        <p style="text-align: center; padding: 20px; color: #999;">Nenhum escalado registrado.</p>
    @endif

    @if($expediente->observacoes)
        <h3 class="secao">Observações</h3>
        <p style="font-size: 10px; padding: 4px 0;">{{ $expediente->observacoes }}</p>
    @endif

    <div class="footer">
        Projeto Bom Samaritano — Emitido em {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
