<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Ficha de Matrícula — {{ $aluno->nome }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; padding: 20px; }
        .header { text-align: center; border-bottom: 3px solid #F5A623; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { font-size: 22px; color: #1A1A1A; }
        .header h2 { font-size: 14px; color: #F5A623; margin-top: 4px; }
        .header p { font-size: 10px; color: #888; margin-top: 4px; }
        .section { margin-bottom: 18px; }
        .section-title { background: #1A1A1A; color: #F5A623; padding: 6px 12px; font-size: 13px; font-weight: bold; border-radius: 4px; margin-bottom: 8px; }
        .section-title-alt { background: #F5A623; color: #1A1A1A; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table th, table td { padding: 6px 10px; border: 1px solid #ddd; text-align: left; font-size: 11px; }
        table th { background: #f5f5f5; font-weight: 600; color: #555; width: 30%; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: bold; }
        .badge-ativo { background: #d4edda; color: #155724; }
        .badge-inativo { background: #f8d7da; color: #721c24; }
        .footer { text-align: center; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 10px; font-size: 10px; color: #999; }
        .two-col { display: table; width: 100%; }
        .two-col .col { display: table-cell; width: 50%; vertical-align: top; padding-right: 10px; }
        .tags { margin-top: 5px; }
        .tag { display: inline-block; padding: 2px 6px; margin: 2px; border-radius: 8px; font-size: 9px; background: #e3f2fd; color: #1565c0; }
        .tag-warn { background: #fff3e0; color: #e65100; }
        .tag-ok { background: #e8f5e9; color: #2e7d32; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Projeto Bom Samaritano</h1>
        <h2>Ficha de Matrícula</h2>
        <p>Emitido em {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <!-- Dados do Aluno -->
    <div class="section">
        <div class="section-title">Dados do Aluno</div>
        <table>
            <tr><th>Nome</th><td>{{ $aluno->nome }}</td></tr>
            <tr><th>Data de Nascimento</th><td>{{ $aluno->data_nascimento?->format('d/m/Y') }} ({{ $aluno->idade }} anos)</td></tr>
            <tr><th>Ano Escolar</th><td>{{ $aluno->ano_escolar ?? '-' }}</td></tr>
            <tr><th>Status</th><td><span class="badge badge-{{ $aluno->status }}">{{ ucfirst($aluno->status) }}</span></td></tr>
        </table>
    </div>

    <!-- Responsável -->
    @if($aluno->responsavel)
    <div class="section">
        <div class="section-title section-title-alt">👤 Responsável</div>
        <table>
            <tr><th>Nome</th><td>{{ $aluno->responsavel->nome }}</td></tr>
            <tr><th>CPF</th><td>{{ $aluno->responsavel->cpf ?? '-' }}</td></tr>
            <tr><th>Telefone</th><td>{{ $aluno->responsavel->telefone ?? '-' }}</td></tr>
            <tr><th>WhatsApp</th><td>{{ $aluno->responsavel->whatsapp ?? '-' }}</td></tr>
            <tr><th>Endereço</th><td>
                {{ $aluno->responsavel->endereco_rua ?? '' }}
                {{ $aluno->responsavel->endereco_numero ? ', ' . $aluno->responsavel->endereco_numero : '' }}
                {{ $aluno->responsavel->endereco_complemento ? ' - ' . $aluno->responsavel->endereco_complemento : '' }}
                <br>
                {{ $aluno->responsavel->endereco_bairro ?? '' }}
                {{ $aluno->responsavel->endereco_cidade ? ' - ' . $aluno->responsavel->endereco_cidade : '' }}
                {{ $aluno->responsavel->endereco_estado ? '/' . $aluno->responsavel->endereco_estado : '' }}
                {{ $aluno->responsavel->endereco_cep ? ' CEP: ' . $aluno->responsavel->endereco_cep : '' }}
            </td></tr>
            <tr><th>Renda Familiar</th><td>
                @php
                    $rendas = ['menos_1_salario' => 'Menos de 1 salário', 'ate_2_salarios' => 'Até 2 salários', 'acima_3_salarios' => 'Acima de 3 salários'];
                @endphp
                {{ $rendas[$aluno->responsavel->renda_familiar] ?? '-' }}
            </td></tr>
        </table>
        <div class="tags">
            @if($aluno->responsavel->veiculo_proprio)<span class="tag">Veículo próprio</span>@endif
            @if($aluno->responsavel->casa_propria)<span class="tag tag-ok">Casa própria</span>@endif
            @if($aluno->responsavel->cadastro_cras)<span class="tag">Cadastro CRAs</span>@endif
            @if($aluno->responsavel->auxilio_governo)<span class="tag tag-warn">Auxílio governo</span>@endif
            @if($aluno->responsavel->desempregado)<span class="tag tag-warn">Desempregado</span>@endif
            @if($aluno->responsavel->autorizacao_sozinho)<span class="tag tag-ok">Vai sozinho</span>@endif
            @if($aluno->responsavel->autorizacao_imagem)<span class="tag tag-ok">Uso de imagem</span>@endif
        </div>
    </div>
    @endif

    <!-- Matrículas -->
    @if($aluno->matriculas && $aluno->matriculas->count())
    <div class="section">
        <div class="section-title">Matrículas</div>
        <table>
            <thead>
                <tr>
                    <th style="width:auto">Curso</th>
                    <th style="width:auto">Turma</th>
                    <th style="width:auto">Professor</th>
                    <th style="width:auto">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($aluno->matriculas as $m)
                <tr>
                    <td>{{ $m->turma?->curso?->nome ?? '-' }}</td>
                    <td>{{ $m->turma?->nome ?? '-' }}</td>
                    <td>{{ $m->turma?->professores->pluck('nome')->join(', ') ?: '-' }}</td>
                    <td><span class="badge badge-{{ $m->status }}">{{ ucfirst($m->status) }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Assinatura -->
    <div style="margin-top: 50px;">
        <table style="border: none;">
            <tr>
                <td style="border: none; width: 50%; text-align: center; padding-top: 40px; border-top: 1px solid #333;">
                    <strong>Responsável</strong>
                </td>
                <td style="border: none; width: 50%; text-align: center; padding-top: 40px; border-top: 1px solid #333;">
                    <strong>Coordenação</strong>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Projeto Bom Samaritano — Gestão Educacional | {{ now()->format('d/m/Y') }}
    </div>
</body>
</html>
