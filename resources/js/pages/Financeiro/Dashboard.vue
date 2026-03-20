<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
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
import VueApexCharts from 'vue3-apexcharts';
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

function formatCurrency(v: number) {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v);
}

const chartOptions = {
    chart: {
        type: 'area',
        toolbar: { show: false },
        zoom: { enabled: false }
    },
    colors: ['#10b981', '#ef4444'],
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 2 },
    xaxis: {
        categories: props.grafico_evolucao.labels,
        labels: { style: { colors: '#94a3b8' } }
    },
    yaxis: {
        labels: { 
            formatter: (v: number) => formatCurrency(v),
            style: { colors: '#94a3b8' } 
        }
    },
    tooltip: {
        y: { formatter: (v: number) => formatCurrency(v) }
    },
    grid: { borderColor: '#f1f5f9' },
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

const series = [
    { name: 'Entradas', data: props.grafico_evolucao.entradas },
    { name: 'Saídas', data: props.grafico_evolucao.saidas }
];
</script>

<template>
    <Head title="Dashboard Financeiro" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <Link href="/financeiro" class="text-muted-foreground hover:text-foreground">
                            <ArrowLeft class="h-5 w-5" />
                        </Link>
                        <h1 class="text-2xl font-bold text-foreground">Dashboard Financeiro</h1>
                    </div>
                    <p class="text-sm text-muted-foreground">Análise de fluxos e saldos por unidade</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="grid gap-4 rounded-xl border border-border bg-card p-4 sm:grid-cols-4">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-muted-foreground">Unidade</label>
                    <div class="relative">
                        <Building2 class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <select v-model="unidade_id" class="h-10 w-full rounded-lg border border-input bg-background pl-10 pr-3 text-sm outline-none focus:border-primary">
                            <option value="">Todas as Unidades</option>
                            <option v-for="u in unidades" :key="u.id" :value="u.id">{{ u.nome }}</option>
                        </select>
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-muted-foreground">De</label>
                    <div class="relative">
                        <Calendar class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <input v-model="periodo_de" type="date" class="h-10 w-full rounded-lg border border-input bg-background pl-10 pr-3 text-sm outline-none focus:border-primary" />
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-muted-foreground">Até</label>
                    <div class="relative">
                        <Calendar class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <input v-model="periodo_ate" type="date" class="h-10 w-full rounded-lg border border-input bg-background pl-10 pr-3 text-sm outline-none focus:border-primary" />
                    </div>
                </div>
                <div class="flex items-end">
                    <button @click="applyFilters" class="flex h-10 w-full items-center justify-center gap-2 rounded-lg bg-primary text-sm font-medium text-primary-foreground hover:bg-primary/90">
                        <Filter class="h-4 w-4" /> Filtrar Resultados
                    </button>
                </div>
            </div>

            <!-- KPIs -->
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground uppercase tracking-wider">Total de Entradas</p>
                            <h3 class="mt-1 text-3xl font-bold text-emerald-600">{{ formatCurrency(kpis.total_entradas) }}</h3>
                        </div>
                        <div class="rounded-full bg-emerald-100 p-3 text-emerald-600 dark:bg-emerald-900/30">
                            <TrendingUp class="h-6 w-6" />
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground uppercase tracking-wider">Total de Saídas</p>
                            <h3 class="mt-1 text-3xl font-bold text-red-600">{{ formatCurrency(kpis.total_saidas) }}</h3>
                        </div>
                        <div class="rounded-full bg-red-100 p-3 text-red-600 dark:bg-red-900/30">
                            <TrendingDown class="h-6 w-6" />
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground uppercase tracking-wider">Saldo Total</p>
                            <h3 class="mt-1 text-3xl font-bold" :class="kpis.saldo_total >= 0 ? 'text-blue-600' : 'text-red-600'">
                                {{ formatCurrency(kpis.saldo_total) }}
                            </h3>
                        </div>
                        <div class="rounded-full p-3" :class="kpis.saldo_total >= 0 ? 'bg-blue-100 text-blue-600 dark:bg-blue-900/30' : 'bg-red-100 text-red-600 dark:bg-red-900/30'">
                            <DollarSign class="h-6 w-6" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Evolution Chart -->
                <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                    <h3 class="mb-6 text-lg font-bold text-foreground">Evolução Mensal</h3>
                    <div class="h-[300px]">
                        <VueApexCharts 
                            type="area" 
                            height="300" 
                            :options="chartOptions" 
                            :series="series" 
                        />
                    </div>
                </div>

                <!-- Units Balance -->
                <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                    <h3 class="mb-6 text-lg font-bold text-foreground">Balanço por Unidade</h3>
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
                                    <td class="py-3 pr-4 text-emerald-600">{{ formatCurrency(item.entradas) }}</td>
                                    <td class="py-3 pr-4 text-red-600">{{ formatCurrency(item.saidas) }}</td>
                                    <td class="py-3 text-right font-bold" :class="item.saldo >= 0 ? 'text-foreground' : 'text-red-600'">
                                        {{ formatCurrency(item.saldo) }}
                                    </td>
                                </tr>
                                <tr v-if="balanco_unidades.length === 0">
                                    <td colspan="4" class="py-8 text-center text-muted-foreground">Sem dados para exibir.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
