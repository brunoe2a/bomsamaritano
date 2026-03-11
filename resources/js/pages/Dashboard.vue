<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { UsersIcon, AcademicCapIcon, ClipboardDocumentCheckIcon, CurrencyDollarIcon, BookOpenIcon, ChartBarIcon, CakeIcon, CreditCardIcon } from '@heroicons/vue/24/outline';
import AppLayout from '@/layouts/AppLayout.vue';
import StatCard from '@/components/StatCard.vue';
import type {
    DashboardStats,
    Aniversariante,
    MovimentacaoFinanceira,
    FrequenciaMensal,
    BreadcrumbItem,
} from '@/types';
import VueApexCharts from 'vue3-apexcharts';

type FinanceiroMensal = { mes: string; entradas: number; saidas: number };

const props = defineProps<{
    stats: DashboardStats;
    aniversariantes: Aniversariante[];
    ultimasMovimentacoes: MovimentacaoFinanceira[];
    frequenciaMensal: FrequenciaMensal[];
    financeiroMensal: FinanceiroMensal[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
];

function formatCurrency(value: number): string {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
}

// Chart: Alunos por Curso (donut)
const alunosCursoOptions = computed(() => ({
    chart: { type: 'donut' as const, background: 'transparent' },
    labels: props.stats.alunosPorCurso.map(c => c.nome),
    colors: ['#F5A623', '#3B82F6', '#10B981', '#8B5CF6', '#EC4899'],
    legend: { position: 'bottom' as const, labels: { colors: '#888' } },
    dataLabels: { style: { fontSize: '13px' } },
    plotOptions: { pie: { donut: { size: '55%', labels: { show: true, total: { show: true, label: 'Total', color: '#888', fontSize: '14px' } } } } },
    theme: { mode: 'dark' as const },
    stroke: { width: 0 },
}));
const alunosCursoSeries = computed(() => props.stats.alunosPorCurso.map(c => c.total));

// Chart: Frequência Mensal (area)
const frequenciaOptions = computed(() => ({
    chart: { type: 'area' as const, background: 'transparent', toolbar: { show: false }, sparkline: { enabled: false } },
    colors: ['#10B981'],
    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.5, opacityTo: 0.05, stops: [0, 95, 100] } },
    stroke: { curve: 'smooth' as const, width: 3 },
    xaxis: { categories: props.frequenciaMensal.map(f => f.mes), labels: { style: { colors: '#888', fontSize: '11px' } } },
    yaxis: { max: 100, labels: { formatter: (val: number) => val + '%', style: { colors: '#888' } } },
    dataLabels: { enabled: false },
    tooltip: { y: { formatter: (val: number) => val + '%' } },
    grid: { borderColor: '#333', strokeDashArray: 4 },
    theme: { mode: 'dark' as const },
}));
const frequenciaSeries = computed(() => [{ name: 'Frequência', data: props.frequenciaMensal.map(f => f.percentual) }]);

// Chart: Financeiro Mensal (bar)
const financeiroOptions = computed(() => ({
    chart: { type: 'bar' as const, background: 'transparent', toolbar: { show: false }, stacked: false },
    colors: ['#10B981', '#EF4444'],
    plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
    xaxis: { categories: props.financeiroMensal.map(f => f.mes), labels: { style: { colors: '#888', fontSize: '11px' } } },
    yaxis: { labels: { formatter: (val: number) => 'R$ ' + (val / 1000).toFixed(1) + 'k', style: { colors: '#888' } } },
    dataLabels: { enabled: false },
    tooltip: { y: { formatter: (val: number) => formatCurrency(val) } },
    grid: { borderColor: '#333', strokeDashArray: 4 },
    legend: { labels: { colors: '#888' } },
    theme: { mode: 'dark' as const },
}));
const financeiroSeries = computed(() => [
    { name: 'Entradas', data: props.financeiroMensal.map(f => f.entradas) },
    { name: 'Saídas', data: props.financeiroMensal.map(f => f.saidas) },
]);
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Stats Cards -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard
                    title="Alunos Ativos"
                    :value="stats.totalAlunosAtivos"
                    :icon="UsersIcon"
                    color="primary"
                    :description="`${stats.novosCadastrosMes} novos este mês`"
                />
                <StatCard
                    title="Turmas Hoje"
                    :value="stats.turmasHoje"
                    :icon="AcademicCapIcon"
                    color="info"
                />
                <StatCard
                    title="Chamadas Pendentes"
                    :value="stats.chamadasPendentes"
                    :icon="ClipboardDocumentCheckIcon"
                    :color="stats.chamadasPendentes > 0 ? 'warning' : 'success'"
                />
                <StatCard
                    title="Saldo Atual"
                    :value="formatCurrency(stats.saldo.atual)"
                    :icon="CurrencyDollarIcon"
                    :color="stats.saldo.atual >= 0 ? 'success' : 'danger'"
                    :description="`Entradas: ${formatCurrency(stats.saldo.entradas)}`"
                />
            </div>

            <!-- Charts Row 1 -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Alunos por Curso (Donut) -->
                <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                    <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-foreground"><BookOpenIcon class="size-5" /> Alunos por Curso</h3>
                    <VueApexCharts
                        v-if="alunosCursoSeries.length"
                        type="donut"
                        :options="alunosCursoOptions"
                        :series="alunosCursoSeries"
                        height="280"
                    />
                    <p v-else class="py-8 text-center text-sm text-muted-foreground">Sem dados disponíveis</p>
                </div>

                <!-- Frequência Mensal (Area) -->
                <div class="rounded-xl border border-border bg-card p-6 shadow-sm lg:col-span-2">
                    <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-foreground"><ChartBarIcon class="size-5" /> Frequência Geral (últimos 6 meses)</h3>
                    <VueApexCharts
                        type="area"
                        :options="frequenciaOptions"
                        :series="frequenciaSeries"
                        height="280"
                    />
                </div>
            </div>

            <!-- Charts Row 2 -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Financeiro Mensal (Bar) -->
                <div class="rounded-xl border border-border bg-card p-6 shadow-sm lg:col-span-2">
                    <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-foreground"><CurrencyDollarIcon class="size-5" /> Financeiro Mensal </h3>
                    <VueApexCharts
                        type="bar"
                        :options="financeiroOptions"
                        :series="financeiroSeries"
                        height="280"
                    />
                </div>

                <!-- Aniversariantes da Semana -->
                <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                    <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-foreground"><CakeIcon class="size-5" /> Aniversariantes</h3>
                    <div v-if="aniversariantes.length" class="space-y-3">
                        <div
                            v-for="a in aniversariantes"
                            :key="a.id"
                            class="flex items-center gap-3 rounded-lg bg-muted/50 p-2"
                        >
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/20 text-sm font-bold text-primary"
                            >
                                {{ a.nome.charAt(0) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-foreground">{{ a.nome }}</p>
                                <p class="text-xs text-muted-foreground">
                                    {{ a.data_nascimento }} · {{ a.idade }} anos
                                </p>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-sm text-muted-foreground">Nenhum aniversariante esta semana.</p>
                </div>
            </div>

            <!-- Últimas Movimentações -->
            <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-foreground"><CreditCardIcon class="size-5" /> Últimas Movimentações</h3>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                    <div
                        v-for="m in ultimasMovimentacoes"
                        :key="m.id"
                        class="flex flex-col items-center rounded-xl border border-border bg-muted/20 p-4 text-center"
                    >
                        <span
                            class="mb-2 flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold"
                            :class="m.tipo === 'entrada' ? 'bg-emerald-500/15 text-emerald-600' : 'bg-red-500/15 text-red-600'"
                        >
                            {{ m.tipo === 'entrada' ? '↑' : '↓' }}
                        </span>
                        <p class="mb-1 text-sm font-medium text-foreground truncate w-full">{{ m.descricao }}</p>
                        <p class="text-xs text-muted-foreground">{{ m.data }} · {{ m.categoria }}</p>
                        <span
                            class="mt-2 text-sm font-bold"
                            :class="m.tipo === 'entrada' ? 'text-emerald-600' : 'text-red-600'"
                        >
                            {{ m.tipo === 'entrada' ? '+' : '-' }}{{ formatCurrency(Number(m.valor)) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
