<?php

namespace App\Exports;

use App\Models\FinanceiroLancamento;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class FinanceiroExport implements FromQuery, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    protected ?string $tipo;
    protected ?int $mes;
    protected ?int $ano;

    public function __construct(?string $tipo = null, ?int $mes = null, ?int $ano = null)
    {
        $this->tipo = $tipo;
        $this->mes = $mes;
        $this->ano = $ano ?? (int) date('Y');
    }

    public function query()
    {
        $query = FinanceiroLancamento::with(['categoria', 'doador']);

        if ($this->tipo) {
            $query->where('tipo', $this->tipo);
        }

        if ($this->mes) {
            $query->whereMonth('data', $this->mes);
        }

        $query->whereYear('data', $this->ano);

        return $query->orderBy('data', 'desc');
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
        return 'Financeiro';
    }
}
