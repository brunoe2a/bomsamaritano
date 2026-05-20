<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Convocação - Núcleo de Saúde</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #333; padding: 20px; }
        .header { text-align: center; border-bottom: 3px solid #F5A623; padding-bottom: 12px; margin-bottom: 18px; }
        .header h1 { font-size: 18px; color: #1A1A1A; }
        .header h2 { font-size: 13px; color: #F5A623; margin-top: 4px; }
        .info-box { border: 1px solid #ddd; padding: 12px; margin-bottom: 16px; background: #fafafa; }
        .info-box .row { margin-bottom: 5px; }
        .info-box strong { color: #1A1A1A; display: inline-block; min-width: 110px; }
        .area-badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; background: #F5A623; color: #fff; text-transform: uppercase; }
        h3 { font-size: 13px; margin: 14px 0 8px; color: #1A1A1A; border-left: 4px solid #F5A623; padding-left: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 6px 8px; border: 1px solid #ccc; text-align: left; font-size: 10px; }
        th { background: #1A1A1A; color: #F5A623; font-weight: bold; }
        td.center { text-align: center; }
        td.assinatura { width: 28%; height: 26px; }
        .footer { text-align: center; margin-top: 18px; font-size: 9px; color: #999; border-top: 1px solid #ddd; padding-top: 8px; }
        .obs { margin-top: 14px; font-size: 10px; padding: 8px; border: 1px dashed #ccc; background: #fffdf6; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Projeto Bom Samaritano</h1>
        <h2>Núcleo de Saúde — Convocação / Lista de Presença</h2>
    </div>

    <div class="info-box">
        <div class="row"><strong>Título:</strong> {{ $convocacao->titulo }}</div>
        <div class="row"><strong>Programa:</strong> {{ $convocacao->programa?->nome }}
            <span class="area-badge">{{ $convocacao->programa?->area?->nome }}</span>
        </div>
        <div class="row"><strong>Data:</strong> {{ optional($convocacao->data)->format('d/m/Y') }}
            @if($convocacao->hora) — {{ \Illuminate\Support\Carbon::parse($convocacao->hora)->format('H:i') }} @endif
        </div>
        <div class="row"><strong>Local:</strong> {{ $convocacao->local ?: '—' }}</div>
        <div class="row"><strong>Profissional:</strong> {{ $convocacao->profissional ?: '—' }}</div>
        <div class="row"><strong>Unidade:</strong> {{ $convocacao->unidade?->nome ?: '—' }}</div>
    </div>

    <h3>Alunos Convocados ({{ $convocacao->alunos->count() }})</h3>

    @if ($convocacao->alunos->count())
        <table>
            <thead>
                <tr>
                    <th style="width: 4%;">#</th>
                    <th>Nome do Aluno</th>
                    <th style="width: 22%;">Responsável</th>
                    <th style="width: 18%;">Contato</th>
                    <th style="width: 12%;" class="center">Presença</th>
                    <th style="width: 22%;">Assinatura / Observação</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($convocacao->alunos as $i => $aluno)
                    <tr>
                        <td class="center">{{ $i + 1 }}</td>
                        <td>{{ $aluno->nome }}</td>
                        <td>{{ $aluno->responsavel?->nome ?: '—' }}</td>
                        <td>{{ $aluno->responsavel?->telefone ?: $aluno->responsavel?->whatsapp ?: '—' }}</td>
                        <td class="center">( ) Sim ( ) Não</td>
                        <td class="assinatura"></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="font-style: italic; color: #999;">Nenhum aluno convocado.</p>
    @endif

    @if ($convocacao->observacoes)
        <div class="obs">
            <strong>Observações:</strong><br>
            {{ $convocacao->observacoes }}
        </div>
    @endif

    <div class="footer">
        Emitido em {{ now()->format('d/m/Y H:i') }} — Projeto Bom Samaritano
    </div>
</body>
</html>
