<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { ArrowLeft, ChevronDown, ChevronRight, TrendingDown } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import DatePicker from '@/components/DatePicker.vue';
import type { BreadcrumbItem, Unidade } from '@/types';

interface Detalhe {
    data: string;
    unidade: string | null;
    descricao: string | null;
    presente: boolean;
    justificativa: string | null;
}

interface RankingItem {
    tipo: string;
    tipo_chave: string;
    id: number;
    nome: string;
    total_escalas: number;
    presencas: number;
    faltas: number;
    percentual: number;
    detalhes: Detalhe[];
}

const props = defineProps<{
    ranking: RankingItem[];
    filtros: {
        unidade_id: string | null;
        tipo: string;
        min_escalas: number;
        data_inicio: string;
        data_fim: string;
    };
    unidades: Unidade[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Expediente', href: '/expedientes' },
    { title: 'Relatório', href: '/expedientes/relatorio' },
];

const unidadeId = ref<string | number>(props.filtros.unidade_id || '');
const tipo = ref<string | number>(props.filtros.tipo || 'todos');
const minEscalas = ref(props.filtros.min_escalas || 1);
const dataInicio = ref(props.filtros.data_inicio);
const dataFim = ref(props.filtros.data_fim);

const unidadeOptions = computed(() => props.unidades.map(u => ({ value: u.id, label: u.nome })));
const tipoOptions = [
    { value: 'todos', label: 'Todos' },
    { value: 'professor', label: 'Professores' },
    { value: 'voluntario', label: 'Voluntários' },
];

const expandidos = ref<Set<string>>(new Set());

let debounceTimeout: ReturnType<typeof setTimeout>;
function aplicar() {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        router.get('/expedientes/relatorio', {
            unidade_id: unidadeId.value || undefined,
            tipo: tipo.value,
            min_escalas: minEscalas.value,
            data_inicio: dataInicio.value,
            data_fim: dataFim.value,
        }, { preserveState: true, replace: true });
    }, 300);
}
watch([unidadeId, tipo, minEscalas, dataInicio, dataFim], aplicar);

function toggle(item: RankingItem) {
    const key = `${item.tipo_chave}-${item.id}`;
    if (expandidos.value.has(key)) expandidos.value.delete(key);
    else expandidos.value.add(key);
}

function isAberto(item: RankingItem): boolean {
    return expandidos.value.has(`${item.tipo_chave}-${item.id}`);
}

function corPercentual(p: number): string {
    if (p >= 80) return 'bg-emerald-100 text-emerald-700';
    if (p >= 50) return 'bg-amber-100 text-amber-700';
    return 'bg-red-100 text-red-700';
}

function formatDate(d: string): string {
    if (!d) return '-';
    const [y, m, day] = d.split('-');
    return `${day}/${m}/${y}`;
}
</script>

<template>
    <Head title="Relatório de Assiduidade" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex items-center gap-3">
                <Link href="/expedientes" class="rounded-lg p-2 hover:bg-muted">
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Relatório de Assiduidade</h1>
                    <p class="text-sm text-muted-foreground">Ranking de presença em expedientes — ordenado do menor para o maior %</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-3 rounded-xl border border-border bg-card p-4 md:grid-cols-5">
                <div>
                    <label class="mb-1 block text-xs text-muted-foreground">Unidade</label>
                    <SearchableSelect
                        v-model="unidadeId"
                        :options="unidadeOptions"
                        placeholder="Todas"
                        :allow-empty="true"
                        empty-label="Todas"
                    />
                </div>
                <div>
                    <label class="mb-1 block text-xs text-muted-foreground">Tipo</label>
                    <SearchableSelect v-model="tipo" :options="tipoOptions" placeholder="Todos" />
                </div>
                <div>
                    <label class="mb-1 block text-xs text-muted-foreground">Data Início</label>
                    <DatePicker v-model="dataInicio" />
                </div>
                <div>
                    <label class="mb-1 block text-xs text-muted-foreground">Data Fim</label>
                    <DatePicker v-model="dataFim" />
                </div>
                <div>
                    <label class="mb-1 block text-xs text-muted-foreground">Mín. Escalas</label>
                    <input v-model.number="minEscalas" type="number" min="1" class="h-10 w-full rounded-lg border border-border bg-background px-3 text-sm outline-none transition-colors focus:border-primary focus:ring-1 focus:ring-primary" />
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border bg-muted/50">
                                <th class="w-8"></th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Nome</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Tipo</th>
                                <th class="px-4 py-3 text-center font-medium text-muted-foreground">Escalas</th>
                                <th class="px-4 py-3 text-center font-medium text-muted-foreground">Presenças</th>
                                <th class="px-4 py-3 text-center font-medium text-muted-foreground">Faltas</th>
                                <th class="px-4 py-3 text-center font-medium text-muted-foreground">% Presença</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-for="item in ranking" :key="`${item.tipo_chave}-${item.id}`">
                                <tr @click="toggle(item)" class="cursor-pointer border-b border-border hover:bg-muted/30">
                                    <td class="pl-3">
                                        <ChevronDown v-if="isAberto(item)" class="h-4 w-4 text-muted-foreground" />
                                        <ChevronRight v-else class="h-4 w-4 text-muted-foreground" />
                                    </td>
                                    <td class="px-4 py-3 font-medium">
                                        <div class="flex items-center gap-2">
                                            <TrendingDown v-if="item.percentual < 50" class="h-4 w-4 text-red-600" />
                                            {{ item.nome }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">{{ item.tipo }}</td>
                                    <td class="px-4 py-3 text-center">{{ item.total_escalas }}</td>
                                    <td class="px-4 py-3 text-center text-emerald-600">{{ item.presencas }}</td>
                                    <td class="px-4 py-3 text-center text-red-600">{{ item.faltas }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span :class="corPercentual(item.percentual)" class="rounded-lg px-2 py-1 text-xs font-bold">
                                            {{ item.percentual }}%
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="isAberto(item)" class="border-b border-border bg-muted/20">
                                    <td colspan="7" class="px-4 py-3">
                                        <div class="ml-6">
                                            <h4 class="mb-2 text-xs font-semibold text-muted-foreground">Detalhamento por data</h4>
                                            <div class="grid grid-cols-1 gap-2 md:grid-cols-2 lg:grid-cols-3">
                                                <div v-for="(d, i) in item.detalhes" :key="i" class="flex items-center gap-2 rounded-lg border border-border bg-background p-2 text-xs">
                                                    <span :class="d.presente ? 'bg-emerald-500' : 'bg-red-500'" class="h-2 w-2 rounded-full"></span>
                                                    <span class="font-medium">{{ formatDate(d.data) }}</span>
                                                    <span class="text-muted-foreground">{{ d.unidade }}</span>
                                                    <span v-if="d.justificativa" class="ml-auto italic text-muted-foreground">{{ d.justificativa }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr v-if="!ranking.length">
                                <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">Nenhum dado para os filtros selecionados.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
