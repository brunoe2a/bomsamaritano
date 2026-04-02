<script setup lang="ts">
import { ref } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

interface Props {
    type: 'area' | 'bar' | 'line' | 'pie' | 'donut' | 'radialBar' | 'treemap';
    height?: number | string;
    series: any[];
    options?: any;
    title?: string;
}

const props = withDefaults(defineProps<Props>(), {
    height: 350,
    options: () => ({})
});

const chartRef = ref<any>(null);

const dataURI = async () => {
    if (!chartRef.value) return null;
    return await chartRef.value.dataURI();
};

defineExpose({
    dataURI
});

const formatCurrency = (v: any) => {
    if (typeof v !== 'number' || isNaN(v)) return v;
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL', maximumFractionDigits: 0 }).format(v);
};

const defaultOptions = {
    chart: {
        toolbar: { show: false },
        parentHeightOffset: 0,
        fontFamily: 'inherit'
    },
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth' as const, width: 2 },
    tooltip: {
        theme: 'light',
        y: {
            formatter: (v: number) => formatCurrency(v)
        }
    },
    yaxis: {
        labels: {
            formatter: (v: number) => formatCurrency(v),
            style: { colors: '#94a3b8' }
        }
    },
    grid: {
        borderColor: '#f1f5f9',
        padding: { top: 0, bottom: 0 }
    },
    legend: {
        position: 'bottom' as const,
        horizontalAlign: 'center' as const,
        fontSize: '14px',
        markers: { radius: 12 }
    }
};

const chartOptions = {
    ...defaultOptions,
    ...props.options,
    chart: { ...defaultOptions.chart, ...props.options?.chart, type: props.type },
    tooltip: { ...defaultOptions.tooltip, ...props.options?.tooltip },
    legend: { ...defaultOptions.legend, ...props.options?.legend },
    grid: { ...defaultOptions.grid, ...props.options?.grid },
    yaxis: { ...defaultOptions.yaxis, ...props.options?.yaxis, labels: { ...defaultOptions.yaxis.labels, ...props.options?.yaxis?.labels } },
    xaxis: { ...props.options?.xaxis }
};
</script>

<template>
    <div class="w-full">
        <div v-if="title" class="mb-4 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-foreground uppercase tracking-wider">{{ title }}</h3>
        </div>
    <VueApexCharts 
        ref="chartRef"
        :type="type" 
        :height="height" 
        :options="chartOptions" 
        :series="series" 
    />
    </div>
</template>
