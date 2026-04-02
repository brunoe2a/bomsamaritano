<script setup lang="ts">
import BaseChart from './BaseChart.vue';
import { computed, ref } from 'vue';

interface Props {
    data: {
        unidade: string;
        entradas: number;
        saidas: number;
        saldo: number;
    }[];
}

const props = defineProps<Props>();

const series = computed(() => [
    { name: 'Entradas', data: props.data.map(d => d.entradas) },
    { name: 'Saídas', data: props.data.map(d => d.saidas) }
]);

const options = computed(() => ({
    chart: { type: 'bar' as const },
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '85%',
            borderRadius: 4,
            dataLabels: { position: 'top' }
        }
    },
    colors: ['#10b981', '#ef4444'],
    xaxis: {
        categories: props.data.map(d => d.unidade),
    },
    legend: { position: 'top' as const }
}));
const chartRef = ref<any>(null);
const dataURI = async () => await chartRef.value?.dataURI();
defineExpose({ dataURI });
</script>

<template>
    <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
        <BaseChart 
            ref="chartRef"
            type="bar" 
            title="Performance por Unidade" 
            :series="series" 
            :options="options" 
            :height="450" 
        />
    </div>
</template>
