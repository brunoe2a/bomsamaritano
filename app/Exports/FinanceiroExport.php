<?php

namespace App\Exports;

use App\Models\FinanceiroLancamento;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class FinanceiroExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithTitle
{
    /**
     * @param  array{tipo?: ?string, categoria_id?: ?int, unidade_id?: ?int, busca?: ?string, mes?: ?int, ano?: ?int}  $filtros
     */
    public function __construct(protected array $filtros = []) {}

    public function query()
    {
        return FinanceiroLancamento::with(['categoria', 'doador'])
            ->whereYear('data', $this->filtros['ano'] ?? (int) date('Y'))
            ->when($this->filtros['mes'] ?? null, fn ($q, $mes) => $q->whereMonth('data', $mes))
            ->when($this->filtros['tipo'] ?? null, fn ($q, $tipo) => $q->where('tipo', $tipo))
            ->when($this->filtros['categoria_id'] ?? null, fn ($q, $id) => $q->where('categoria_id', $id))
            ->when($this->filtros['unidade_id'] ?? null, fn ($q, $id) => $q->where('unidade_id', $id))
            ->when($this->filtros['busca'] ?? null, fn ($q, $busca) => $q->where('descricao', 'like', "%{$busca}%"))
            ->orderBy('data', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID', 'Data', 'Tipo', 'Categoria', 'Descrição', 'Valor (R$)', 'Doador',
        ];
    }

    public function map($lancamento): array
    {
        return [
            $lancamento->id,
            $lancamento->data?->format('d/m/Y'),
            $lancamento->tipo === 'entrada' ? 'Entrada' : 'Saída',
            $lancamento->categoria?->nome ?? '-',
            $lancamento->descricao,
            number_format((float) $lancamento->valor, 2, ',', '.'),
            $lancamento->doador?->nome ?? '-',
        ];
    }

    public function title(): string
    {
        return match ($this->filtros['tipo'] ?? null) {
            'entrada' => 'Entradas',
            'saida' => 'Saídas',
            default => 'Financeiro',
        };
    }
}
