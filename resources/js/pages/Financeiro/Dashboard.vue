<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    TrendingUp, 
    TrendingDown, 
    DollarSign, 
    ArrowLeft,
    Filter,
    Calendar,
    Building2
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import KPICard from '@/components/Financeiro/KPICard.vue';
import BaseChart from '@/components/Financeiro/BaseChart.vue';
import UnitPerformanceChart from '@/components/Financeiro/UnitPerformanceChart.vue';
import type { BreadcrumbItem, Unidade } from '@/types';

const props = defineProps<{
    kpis: {
        total_entradas: number;
        total_saidas: number;
        saldo_total: number;
    };
    grafico_evolucao: {
        labels: string[];
        entradas: number[];
        saidas: number[];
        saldos: number[];
    };
    receitas_categoria: {
        labels: string[];
        series: number[];
    };
    despesas_categoria: {
        labels: string[];
        series: number[];
    };
    balanco_unidades: {
        unidade: string;
        entradas: number;
        saidas: number;
        saldo: number;
    }[];
    unidades: Unidade[];
    filtros: {
        unidade_id: string | number;
        mes: number;
        ano: number;
        periodo_de: string;
        periodo_ate: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Financeiro', href: '/financeiro' },
    { title: 'Dashboard Financeiro', href: '/financeiro/dashboard' },
];

const unidade_id = ref(props.filtros.unidade_id || '');
const periodo_de = ref(props.filtros.periodo_de || '');
const periodo_ate = ref(props.filtros.periodo_ate || '');

function applyFilters() {
    router.get('/financeiro/dashboard', {
        unidade_id: unidade_id.value || undefined,
        periodo_de: periodo_de.value || undefined,
        periodo_ate: periodo_ate.value || undefined,
    }, { preserveState: true, replace: true });
}

const evolutionSeries = [
    { name: 'Entradas', data: props.grafico_evolucao.entradas },
    { name: 'Saídas', data: props.grafico_evolucao.saidas },
    { name: 'Saldo Mensal', data: props.grafico_evolucao.saldos, type: 'line' }
];

const evolutionOptions = {
    colors: ['#10b981', '#ef4444', '#3b82f6'],
    xaxis: {
        categories: props.grafico_evolucao.labels,
        labels: { style: { colors: '#94a3b8' } }
    },
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.45,
            opacityTo: 0.05,
            stops: [20, 100]
        }
    }
};

const pieOptions = (title: string, colors: string[]) => ({
    chart: { type: 'donut' as const },
    labels: [] as string[],
    colors: colors,
    plotOptions: {
        pie: {
            donut: {
                size: '70%',
                labels: {
                    show: true,
                    total: {
                        show: true,
                        label: title,
                        formatter: (w: any) => {
                            const total = w.globals.seriesTotals.reduce((a: number, b: number) => a + b, 0);
                            return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL', maximumFractionDigits: 0 }).format(total);
                        }
                    }
                }
            }
        }
    }
});

const receitasPieOptions = { ...pieOptions('Receitas', ['#10b981', '#34d399', '#6ee7b7', '#a7f3d0']), labels: props.receitas_categoria.labels };
const despesasPieOptions = { ...pieOptions('Despesas', ['#ef4444', '#f87171', '#fca5a5', '#fecaca']), labels: props.despesas_categoria.labels };
</script>

<template>
    <Head title="Dashboard Financeiro" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-3">
                        <Link href="/financeiro" class="flex h-9 w-9 items-center justify-center rounded-lg border border-border hover:bg-muted">
                            <ArrowLeft class="h-5 w-5" />
                        </Link>
                        <h1 class="text-2xl font-bold text-foreground">Dashboard Financeiro</h1>
                    </div>
                </div>
            </div>

            <!-- Dashboard Filter Bar -->
            <div class="grid gap-4 rounded-xl border border-border bg-card p-4 sm:grid-cols-4 shadow-sm">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-bold text-muted-foreground uppercase">Unidade</label>
                    <div class="relative">
                        <Building2 class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <select v-model="unidade_id" class="h-10 w-full rounded-lg border border-input bg-background pl-10 pr-3 text-sm outline-none focus:ring-2 focus:ring-primary/20">
                            <option value="">Todas as Unidades</option>
                            <option v-for="u in unidades" :key="u.id" :value="u.id">{{ u.nome }}</option>
                        </select>
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-bold text-muted-foreground uppercase">De</label>
                    <div class="relative">
                        <Calendar class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <input v-model="periodo_de" type="date" class="h-10 w-full rounded-lg border border-input bg-background pl-10 pr-3 text-sm outline-none focus:ring-2 focus:ring-primary/20" />
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-bold text-muted-foreground uppercase">Até</label>
                    <div class="relative">
                        <Calendar class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <input v-model="periodo_ate" type="date" class="h-10 w-full rounded-lg border border-input bg-background pl-10 pr-3 text-sm outline-none focus:ring-2 focus:ring-primary/20" />
                    </div>
                </div>
                <div class="flex items-end">
                    <button @click="applyFilters" class="flex h-10 w-full items-center justify-center gap-2 rounded-lg bg-primary text-sm font-semibold text-primary-foreground transition-all hover:bg-primary/90 hover:shadow-lg">
                        <Filter class="h-4 w-4" /> Atualizar Dashboard
                    </button>
                </div>
            </div>

            <!-- Strategic KPIs -->
            <div class="grid gap-6 sm:grid-cols-3">
                <KPICard title="Receitas Totais" :value="kpis.total_entradas" :icon="TrendingUp" variant="emerald" />
                <KPICard title="Despesas Totais" :value="kpis.total_saidas" :icon="TrendingDown" variant="red" />
                <KPICard title="Saldo Líquido" :value="kpis.saldo_total" :icon="DollarSign" :variant="kpis.saldo_total >= 0 ? 'blue' : 'red'" />
            </div>

            <!-- Main Charts Row -->
            <div class="grid gap-6">
                <!-- Evolution Full Width -->
                <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                    <BaseChart 
                        type="area" 
                        title="Evolução Financeira Mensal" 
                        :series="evolutionSeries" 
                        :options="evolutionOptions" 
                        :height="350" 
                    />
                </div>

                <!-- Secondary Charts Grid -->
                <div class="grid gap-6 lg:grid-cols-2">
                    <UnitPerformanceChart :data="balanco_unidades" />
                    
                    <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                        <h3 class="mb-6 text-sm font-semibold text-foreground uppercase tracking-wider">Composição por Categoria</h3>
                        <div class="space-y-8">
                            <div v-if="receitas_categoria.series.length > 0">
                                <BaseChart type="donut" title="Receitas" :series="receitas_categoria.series" :options="receitasPieOptions" :height="250" />
                            </div>
                            <div v-if="despesas_categoria.series.length > 0">
                                <BaseChart type="donut" title="Despesas" :series="despesas_categoria.series" :options="despesasPieOptions" :height="250" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Units Details Table -->
                <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                    <h3 class="mb-6 text-sm font-semibold text-foreground uppercase tracking-wider">Tabela de Balanço por Unidade</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-border text-left font-medium text-muted-foreground">
                                    <th class="pb-3 pr-4">Unidade</th>
                                    <th class="pb-3 pr-4 text-emerald-600">Entradas</th>
                                    <th class="pb-3 pr-4 text-red-600">Saídas</th>
                                    <th class="pb-3 text-right">Saldo</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="item in balanco_unidades" :key="item.unidade" class="hover:bg-muted/50 transition-colors">
                                    <td class="py-3 pr-4 font-medium">{{ item.unidade }}</td>
                                    <td class="py-3 pr-4 text-emerald-600">{{ new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(item.entradas) }}</td>
                                    <td class="py-3 pr-4 text-red-600">{{ new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(item.saidas) }}</td>
                                    <td class="py-3 text-right font-bold" :class="item.saldo >= 0 ? 'text-foreground' : 'text-red-600'">
                                        {{ new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(item.saldo) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
