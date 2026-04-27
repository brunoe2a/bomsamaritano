<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Search, Trash2 } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import DatePicker from '@/components/DatePicker.vue';
import { useSwal } from '@/composables/useSwal';
import { computed } from 'vue';
import type { PaginatedData, BreadcrumbItem } from '@/types';

interface Atendimento {
    id: number;
    data_atendimento: string;
    profissional: string | null;
    observacoes: string | null;
    aluno: { id: number; nome: string };
    programa: { id: number; nome: string; area: { id: number; nome: string } | null };
}

const { confirmDelete: swalDelete } = useSwal();

const props = defineProps<{
    atendimentos: PaginatedData<Atendimento>;
    filtros: Record<string, string>;
    programas: { id: number; nome: string; area: { id: number; nome: string } | null }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Núcleo de Saúde', href: '/saude' },
    { title: 'Atendimentos', href: '/saude/atendimentos' },
];

const busca = ref(props.filtros.busca || '');
const programaId = ref(props.filtros.programa_id || '');
const dataInicio = ref(props.filtros.data_inicio || '');
const dataFim = ref(props.filtros.data_fim || '');
let debounce: ReturnType<typeof setTimeout>;

function applyFilters() {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
        router.get('/saude/atendimentos', {
            busca: busca.value || undefined,
            programa_id: programaId.value || undefined,
            data_inicio: dataInicio.value || undefined,
            data_fim: dataFim.value || undefined,
        }, { preserveState: true, replace: true });
    }, 300);
}
watch([busca, programaId, dataInicio, dataFim], applyFilters);

function confirmDelete(a: Atendimento) {
    swalDelete(`O atendimento de "${a.aluno.nome}" será removido.`, `/saude/atendimentos/${a.id}`);
}

function formatData(d: string) { return new Date(d).toLocaleDateString('pt-BR'); }

const programaOptions = computed(() => props.programas.map(p => ({ value: String(p.id), label: p.nome, hint: p.area?.nome ?? '' })));
</script>

<template>
    <Head title="Atendimentos de Saúde" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div>
                <h1 class="text-2xl font-bold text-foreground">Atendimentos de Saúde</h1>
                <p class="text-sm text-muted-foreground">{{ atendimentos.total }} atendimento(s) registrado(s)</p>
            </div>

            <div class="rounded-xl border border-border bg-card p-4 shadow-sm">
                <div class="grid gap-3 sm:grid-cols-4">
                    <div class="relative sm:col-span-2">
                        <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <input v-model="busca" type="text" placeholder="Buscar aluno..." class="h-10 w-full rounded-lg border border-input bg-background pl-9 pr-3 text-sm outline-none focus:border-primary" />
                    </div>
                    <SearchableSelect v-model="programaId" :options="programaOptions" placeholder="Todos os programas" allow-empty empty-label="Todos os programas" />
                    <div class="flex gap-2">
                        <DatePicker v-model="dataInicio" placeholder="Data início" />
                        <DatePicker v-model="dataFim" placeholder="Data fim" />
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                <table class="w-full text-sm">
                    <thead class="border-b border-border bg-muted/40 text-xs uppercase text-muted-foreground">
                        <tr>
                            <th class="px-4 py-3 text-left">Data</th>
                            <th class="px-4 py-3 text-left">Aluno</th>
                            <th class="px-4 py-3 text-left">Programa</th>
                            <th class="px-4 py-3 text-left">Profissional</th>
                            <th class="px-4 py-3 text-left">Observações</th>
                            <th class="px-4 py-3 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!atendimentos.data.length"><td colspan="6" class="p-8 text-center text-muted-foreground">Nenhum atendimento encontrado.</td></tr>
                        <tr v-for="a in atendimentos.data" :key="a.id" class="border-b border-border last:border-0 hover:bg-muted/30">
                            <td class="px-4 py-3 text-muted-foreground">{{ formatData(a.data_atendimento) }}</td>
                            <td class="px-4 py-3"><Link :href="`/alunos/${a.aluno.id}`" class="font-medium text-foreground hover:underline">{{ a.aluno.nome }}</Link></td>
                            <td class="px-4 py-3 text-muted-foreground">{{ a.programa.nome }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ a.profissional || '—' }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ a.observacoes ? a.observacoes.slice(0, 60) : '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <button @click="confirmDelete(a)" class="rounded-md p-1.5 hover:bg-red-50 dark:hover:bg-red-950/30" title="Excluir"><Trash2 class="h-4 w-4 text-red-500" /></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :links="atendimentos.links" />
        </div>
    </AppLayout>
</template>
