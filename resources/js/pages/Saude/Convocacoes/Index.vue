<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Plus, Eye, Pencil, Trash2, Search, FileText, Users } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import { useSwal } from '@/composables/useSwal';
import { computed } from 'vue';
import type { PaginatedData, BreadcrumbItem } from '@/types';

interface Convocacao {
    id: number;
    titulo: string;
    data: string;
    hora: string | null;
    local: string | null;
    profissional: string | null;
    status: string;
    alunos_count: number;
    atendimentos_count: number;
    programa: { id: number; nome: string; area: string } | null;
}

interface Programa { id: number; nome: string; area: string }

const { confirmDelete: swalDelete } = useSwal();

const props = defineProps<{
    convocacoes: PaginatedData<Convocacao>;
    filtros: Record<string, string>;
    programas: Programa[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Núcleo de Saúde', href: '/saude' },
    { title: 'Convocações', href: '/saude/convocacoes' },
];

const busca = ref(props.filtros.busca || '');
const programaId = ref(props.filtros.programa_id || '');
const status = ref(props.filtros.status || '');
let debounce: ReturnType<typeof setTimeout>;
function applyFilters() {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
        router.get('/saude/convocacoes', {
            busca: busca.value || undefined,
            programa_id: programaId.value || undefined,
            status: status.value || undefined,
        }, { preserveState: true, replace: true });
    }, 300);
}
watch([busca, programaId, status], applyFilters);

function confirmDelete(c: Convocacao) {
    swalDelete(`A convocação "${c.titulo}" será removida permanentemente.`, `/saude/convocacoes/${c.id}`);
}

function formatData(d: string) { return new Date(d).toLocaleDateString('pt-BR'); }

const statusClasses: Record<string, string> = {
    planejada: 'bg-amber-100 text-amber-700',
    realizada: 'bg-emerald-100 text-emerald-700',
    cancelada: 'bg-rose-100 text-rose-700',
};

const programaOptions = computed(() => props.programas.map(p => ({ value: String(p.id), label: p.nome })));
const statusOptions = [
    { value: 'planejada', label: 'Planejada' },
    { value: 'realizada', label: 'Realizada' },
    { value: 'cancelada', label: 'Cancelada' },
];
</script>

<template>
    <Head title="Convocações de Saúde" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Convocações</h1>
                    <p class="text-sm text-muted-foreground">{{ convocacoes.total }} convocação(ões) registrada(s)</p>
                </div>
                <Link href="/saude/convocacoes/create" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90">
                    <Plus class="h-4 w-4" /> Nova Convocação
                </Link>
            </div>

            <div class="rounded-xl border border-border bg-card p-4 shadow-sm">
                <div class="flex flex-wrap gap-3">
                    <div class="relative flex-1 min-w-[240px]">
                        <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <input v-model="busca" type="text" placeholder="Buscar por título..." class="h-10 w-full rounded-lg border border-input bg-background pl-9 pr-3 text-sm outline-none focus:border-primary" />
                    </div>
                    <div class="w-56">
                        <SearchableSelect
                            v-model="programaId"
                            :options="programaOptions"
                            placeholder="Todos os programas"
                            allow-empty
                            empty-label="Todos os programas"
                        />
                    </div>
                    <div class="w-48">
                        <SearchableSelect
                            v-model="status"
                            :options="statusOptions"
                            placeholder="Todos os status"
                            allow-empty
                            empty-label="Todos os status"
                        />
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                <table class="w-full text-sm">
                    <thead class="border-b border-border bg-muted/40 text-xs uppercase tracking-wider text-muted-foreground">
                        <tr>
                            <th class="px-4 py-3 text-left">Título</th>
                            <th class="px-4 py-3 text-left">Programa</th>
                            <th class="px-4 py-3 text-left">Data</th>
                            <th class="px-4 py-3 text-left">Profissional</th>
                            <th class="px-4 py-3 text-center">Alunos</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!convocacoes.data.length"><td colspan="7" class="p-8 text-center text-muted-foreground">Nenhuma convocação encontrada.</td></tr>
                        <tr v-for="c in convocacoes.data" :key="c.id" class="border-b border-border last:border-0 hover:bg-muted/30">
                            <td class="px-4 py-3 font-medium text-foreground">{{ c.titulo }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ c.programa?.nome }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ formatData(c.data) }}<span v-if="c.hora"> · {{ c.hora.slice(0,5) }}</span></td>
                            <td class="px-4 py-3 text-muted-foreground">{{ c.profissional || '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary">
                                    <Users class="h-3 w-3" /> {{ c.alunos_count }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span :class="statusClasses[c.status] || 'bg-muted'" class="rounded-full px-2 py-0.5 text-xs">{{ c.status }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex gap-1">
                                    <a :href="`/export/saude/convocacoes/${c.id}/pdf`" target="_blank" class="rounded-md p-1.5 hover:bg-muted" title="PDF da convocação"><FileText class="h-4 w-4 text-muted-foreground" /></a>
                                    <Link :href="`/saude/convocacoes/${c.id}`" class="rounded-md p-1.5 hover:bg-muted" title="Ver"><Eye class="h-4 w-4 text-muted-foreground" /></Link>
                                    <Link :href="`/saude/convocacoes/${c.id}/edit`" class="rounded-md p-1.5 hover:bg-muted" title="Editar"><Pencil class="h-4 w-4 text-muted-foreground" /></Link>
                                    <button @click="confirmDelete(c)" class="rounded-md p-1.5 hover:bg-red-50 dark:hover:bg-red-950/30" title="Excluir"><Trash2 class="h-4 w-4 text-red-500" /></button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :links="convocacoes.links" />
        </div>
    </AppLayout>
</template>
