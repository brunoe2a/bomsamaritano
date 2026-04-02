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
    Building2,
    FileText
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { jsPDF } from 'jspdf';
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

const isGeneratingPDF = ref(false);
const unidade_id = ref(props.filtros.unidade_id || '');
const periodo_de = ref(props.filtros.periodo_de || '');
const periodo_ate = ref(props.filtros.periodo_ate || '');

const evolutionChart = ref(null);
const unitChart = ref(null);
const receitasChart = ref(null);
const despesasChart = ref(null);

async function exportPDF() {
    isGeneratingPDF.value = true;

    try {
        // Coletar as imagens dos gráficos diretamente (mais estável que processar o DOM)
        const evImg = await evolutionChart.value?.dataURI();
        const unitImg = await unitChart.value?.dataURI();
        const recImg = await receitasChart.value?.dataURI();
        const desImg = await despesasChart.value?.dataURI();

        const pdf = new jsPDF('p', 'mm', 'a4'); // Retrato para relatório
        const pageWidth = pdf.internal.pageSize.getWidth();
        const margin = 15;
        const colWidth = (pageWidth - (margin * 2));

        // Cabeçalho
        pdf.setFontSize(22);
        pdf.setTextColor(26, 26, 26);
        pdf.text('Relatório Executivo - Dashboard Financeiro', margin, 25);
        
        pdf.setFontSize(10);
        pdf.setTextColor(100, 100, 100);
        pdf.text(`Gerado em: ${new Date().toLocaleString('pt-BR')}`, margin, 32);
        if (props.filtros.periodo_de) {
            pdf.text(`Período: ${props.filtros.periodo_de} até ${props.filtros.periodo_ate || 'hoje'}`, margin, 37);
        }

        // Seção KPIs
        pdf.setDrawColor(240, 240, 240);
        pdf.setFillColor(250, 250, 250);
        pdf.roundedRect(margin, 45, colWidth, 30, 3, 3, 'F');
        
        const formatBRL = (v: number) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v);
        
        pdf.setFontSize(9);
        pdf.setTextColor(120, 120, 120);
        pdf.text('RECEITAS TOTAIS', margin + 5, 53);
        pdf.text('DESPESAS TOTAIS', margin + (colWidth/3) + 5, 53);
        pdf.text('SALDO LÍQUIDO', margin + (2*colWidth/3) + 5, 53);

        pdf.setFontSize(14);
        pdf.setTextColor(16, 185, 129); // Emerald
        pdf.text(formatBRL(props.kpis.total_entradas), margin + 5, 65);
        pdf.setTextColor(239, 68, 68); // Red
        pdf.text(formatBRL(props.kpis.total_saidas), margin + (colWidth/3) + 5, 65);
        pdf.setTextColor(59, 130, 246); // Blue
        pdf.text(formatBRL(props.kpis.saldo_total), margin + (2*colWidth/3) + 5, 65);

        // Gráfico 1: Evolução
        if (evImg?.img) {
            pdf.setFontSize(12);
            pdf.setTextColor(40, 40, 40);
            pdf.text('Evolução Financeira Mensal', margin, 90);
            pdf.addImage(evImg.img, 'PNG', margin, 95, colWidth, 80);
        }

        // Gráfico 2: Unidade
        if (unitImg?.img) {
            pdf.addPage();
            pdf.setFontSize(12);
            pdf.text('Performance por Unidade', margin, 25);
            pdf.addImage(unitImg.img, 'PNG', margin, 30, colWidth, 100);
        }

        // Gráfico de Pizza (Categorias)
        if (recImg?.img || desImg?.img) {
            const pizzaSize = (colWidth / 2) - 5;
            let currentY = unitImg?.img ? 140 : 25;
            
            if (recImg?.img) {
                pdf.setFontSize(11);
                pdf.text('Composição de Receitas', margin, currentY);
                pdf.addImage(recImg.img, 'PNG', margin, currentY + 5, pizzaSize, pizzaSize);
            }
            if (desImg?.img) {
                pdf.setFontSize(11);
                pdf.text('Composição de Despesas', margin + pizzaSize + 10, currentY);
                pdf.addImage(desImg.img, 'PNG', margin + pizzaSize + 10, currentY + 5, pizzaSize, pizzaSize);
            }
        }

        // No ambiente local HTTP, blobs podem ser bloqueados. Usar Data URI como alternativa.
        const fileName = `dashboard_financeiro_${new Date().toISOString().split('T')[0]}.pdf`;
        const pdfData = pdf.output('datauristring');
        const link = document.createElement('a');
        link.href = pdfData;
        link.download = fileName;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } catch (e) {
        console.error('Erro ao coletar dados para PDF:', e);
        alert('Houve um erro técnico na geração. Tentando modo de impressão padrão.');
        window.print();
    } finally {
        isGeneratingPDF.value = false;
    }
}

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
                <div class="flex items-center justify-between w-full">
                    <div class="flex items-center gap-3">
                        <Link href="/financeiro" class="flex h-9 w-9 items-center justify-center rounded-lg border border-border hover:bg-muted">
                            <ArrowLeft class="h-5 w-5" />
                        </Link>
                        <h1 class="text-2xl font-bold text-foreground">Dashboard Financeiro</h1>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <button 
                            @click="exportPDF" 
                            :disabled="isGeneratingPDF"
                            class="inline-flex items-center gap-2 rounded-lg border border-border bg-card px-4 py-2 text-sm font-medium hover:bg-muted shadow-sm transition-all active:scale-95 disabled:opacity-50"
                        >
                            <template v-if="!isGeneratingPDF">
                                <FileText class="h-4 w-4" />
                                <span>Exportar PDF</span>
                            </template>
                            <template v-else>
                                <span class="h-4 w-4 animate-spin rounded-full border-2 border-primary border-t-transparent"></span>
                                <span>Gerando...</span>
                            </template>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Dashboard Content Container (Used for PDF capture) -->
            <div id="dashboard-content" class="flex flex-col gap-6">
                <!-- Dashboard Filter Bar (Hidden in PDF usually, but we can keep it or hide it) -->
                <div class="grid gap-4 rounded-xl border border-border bg-card p-4 sm:grid-cols-4 shadow-sm" data-html2canvas-ignore="true">
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
                        ref="evolutionChart"
                        type="area" 
                        title="Evolução Financeira Mensal" 
                        :series="evolutionSeries" 
                        :options="evolutionOptions" 
                        :height="350" 
                    />
                </div>

                <!-- Secondary Charts Grid -->
                <div class="grid gap-6 lg:grid-cols-2">
                    <UnitPerformanceChart ref="unitChart" :data="balanco_unidades" />
                    
                    <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                        <h3 class="mb-6 text-sm font-semibold text-foreground uppercase tracking-wider">Composição por Categoria</h3>
                        <div class="space-y-8">
                            <div v-if="receitas_categoria.series.length > 0">
                                <BaseChart ref="receitasChart" type="donut" title="Receitas" :series="receitas_categoria.series" :options="receitasPieOptions" :height="250" />
                            </div>
                            <div v-if="despesas_categoria.series.length > 0">
                                <BaseChart ref="despesasChart" type="donut" title="Despesas" :series="despesas_categoria.series" :options="despesasPieOptions" :height="250" />
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
    </div>
    </AppLayout>
</template>

<style scoped>
@media print {
    :deep(nav), 
    :deep(header), 
    :deep(aside),
    [data-html2canvas-ignore="true"] { 
        display: none !important; 
    }
    
    #dashboard-content {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        padding: 0;
        margin: 0;
        visibility: visible;
    }

    body {
        background: white !important;
    }
}
</style>
